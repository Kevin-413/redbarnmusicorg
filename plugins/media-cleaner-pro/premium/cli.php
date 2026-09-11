<?php

class MeowPro_WPMC_CLI extends WP_CLI_Command {

	public function __construct() {
	}

	/**
	 * Memory management helper - forces garbage collection and logs memory usage
	 */
	private function manage_memory( $processed_count = 0, $debug = false ) {
		if ( function_exists( 'gc_collect_cycles' ) ) {
			gc_collect_cycles();
		}
		
		if ( $debug && $processed_count > 0 ) {
			$memory_usage = memory_get_usage( true );
			$memory_mb = round( $memory_usage / 1024 / 1024, 2 );
			$memory_peak = memory_get_peak_usage( true );
			$memory_peak_mb = round( $memory_peak / 1024 / 1024, 2 );
			WP_CLI::line( sprintf( __( '%1$s: Current: %2$sMB, Peak: %3$sMB (after %4$d items)', 'media-cleaner' ), str_pad( __( '> Memory Status', 'media-cleaner' ), 20 ), $memory_mb, $memory_peak_mb, $processed_count ) );
		}
	}

	/**
	 * Check if we're approaching memory limits
	 */
	private function check_memory_limit() {
		$memory_limit = ini_get( 'memory_limit' );
		if ( $memory_limit !== '-1' ) {
			$memory_limit_bytes = $this->parse_memory_limit( $memory_limit );
			$current_usage = memory_get_usage( true );
			$usage_percentage = ( $current_usage / $memory_limit_bytes ) * 100;
			
			if ( $usage_percentage > 80 ) {
				WP_CLI::warning( sprintf( __( 'Memory usage is at %1$s%% of limit (%2$s). Consider increasing memory_limit.', 'media-cleaner' ), $usage_percentage, $memory_limit ) );
			}
		}
	}

	/**
	 * Parse memory limit string to bytes
	 */
	private function parse_memory_limit( $memory_limit ) {
		$memory_limit = trim( $memory_limit );
		$last_char = strtolower( substr( $memory_limit, -1 ) );
		$value = (int) substr( $memory_limit, 0, -1 );
		
		switch ( $last_char ) {
			case 'g':
				$value *= 1024;
			case 'm':
				$value *= 1024;
			case 'k':
				$value *= 1024;
		}
		
		return $value;
	}

	private function core() {
		global $wpmc;
		return $wpmc;
	}

	private function printable_path( $path ) {
		$clean = preg_replace( '/[\x00-\x1F\x7F]/u', '?', (string) $path );
		return is_string( $clean ) ? $clean : __( '[invalid path encoding]', 'media-cleaner' );
	}

	private function list_results( $deleted ) {
		global $wpdb;
		$core = $this->core();
		$table = $wpdb->prefix . 'mclean_scan';
		$run_id = $core->get_run_id();
		$total = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM $table WHERE run_id = %d AND ignored = 0 AND deleted = %d",
			$run_id,
			$deleted ? 1 : 0
		) );
		if ( $total === 0 ) {
			WP_CLI::line( $deleted ? __( 'No deleted items found.', 'media-cleaner' ) : __( 'No issues found.', 'media-cleaner' ) );
			return;
		}
		WP_CLI::line( sprintf( __( 'Found %d item(s):', 'media-cleaner' ), $total ) );
		$cursor = 0;
		do {
			$items = $wpdb->get_results( $wpdb->prepare(
				"SELECT id, postId, path, issue FROM $table
				WHERE run_id = %d AND ignored = 0 AND deleted = %d AND id > %d
				ORDER BY id ASC LIMIT 200",
				$run_id,
				$deleted ? 1 : 0,
				$cursor
			) );
			foreach ( $items as $item ) {
				$cursor = (int) $item->id;
				WP_CLI::line( sprintf( __( '- [#%1$d] Media %2$d: %3$s (%4$s)', 'media-cleaner' ), $cursor, (int) $item->postId, $this->printable_path( $item->path ), $item->issue ) );
			}
		} while ( count( $items ) === 200 );
	}

	private function operate_item( $id, $operation, $request_key ) {
		$core = $this->core();
		$journal = $core->runs->begin_operation( $id, $operation, $request_key );
		if ( is_wp_error( $journal ) ) return $journal;
		if ( $journal->state === 'complete' ) return true;
		$manifest = json_decode( (string) $journal->manifest, true );
		$manifest = is_array( $manifest ) ? $manifest : array();
		if ( $journal->state === 'pending' ) {
			$issue = $core->get_issue( $id );
			if ( !$issue ) return new WP_Error( 'wpmc_issue_missing', __( 'The Media Cleaner result no longer exists.', 'media-cleaner' ) );
			$manifest = array(
				'initial_deleted' => (bool) $issue->deleted,
				'initial_ignored' => (bool) $issue->ignored,
				'initial_type' => (int) $issue->type,
			);
		}
		if ( empty( $manifest['identity_validated'] ) ) {
			$issue = $core->get_issue( $id );
			$identity = $issue ? $core->validate_issue_manifest( $issue ) : new WP_Error( 'wpmc_issue_missing', __( 'The Media Cleaner result no longer exists.', 'media-cleaner' ) );
			if ( is_wp_error( $identity ) ) {
				$core->runs->update_operation( $journal->id, 'failed', $manifest, $identity );
				return $identity;
			}
			$manifest['identity_validated'] = true;
		}
		if ( !$core->runs->update_operation( $journal->id, 'running', $manifest ) ) {
			return new WP_Error( 'wpmc_operation_journal_failed', __( 'The cleanup journal could not be updated.', 'media-cleaner' ) );
		}
		$result = $operation === 'delete' ? $core->delete( $id, $manifest ) : $core->recover( $id, $manifest );
		if ( is_wp_error( $result ) || $result !== true ) {
			$error = is_wp_error( $result ) ? $result : new WP_Error( 'wpmc_operation_failed', __( 'The cleanup operation failed.', 'media-cleaner' ) );
			$core->runs->update_operation( $journal->id, 'failed', $manifest, $error );
			return $error;
		}
		if ( !$core->runs->update_operation( $journal->id, 'complete', $manifest ) ) {
			return new WP_Error( 'wpmc_operation_journal_failed', __( 'The cleanup completed, but its journal could not be finalized.', 'media-cleaner' ) );
		}
		return true;
	}

	// No gate here: delete() refuses on its own when the results are not current, and
	// recovering is never refused. Gating the whole command would lock the trash away
	// from the very people who need it back.
	private function operate_results( $operation, $deleted, $ids = array() ) {
		global $wpdb;
		$core = $this->core();
		$request_key = wp_generate_uuid4();
		$table = $wpdb->prefix . 'mclean_scan';
		$explicit = !empty( $ids );
		$cursor = 0;
		do {
			$batch = $explicit ? array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) ) : $wpdb->get_col( $wpdb->prepare(
				"SELECT id FROM $table WHERE run_id = %d AND ignored = 0 AND deleted = %d AND id > %d ORDER BY id ASC LIMIT 100",
				$core->get_run_id(),
				$deleted ? 1 : 0,
				$cursor
			) );
			foreach ( $batch as $id ) {
				$id = (int) $id;
				$cursor = max( $cursor, $id );
				$result = $this->operate_item( $id, $operation, $request_key );
					if ( is_wp_error( $result ) ) WP_CLI::warning( sprintf( __( '#%1$d: %2$s', 'media-cleaner' ), $id, $result->get_error_message() ) );
					else WP_CLI::line( sprintf( __( '- Updated Media Cleaner result #%d', 'media-cleaner' ), $id ) );
			}
			if ( $explicit ) break;
		} while ( count( $batch ) === 100 );
	}

	public function issues() {
		$this->list_results( false );
	}

	public function trash() {
		$this->list_results( true );
	}

	public function delete( $args ) {
		$this->operate_results( 'delete', false, $args );
	}

	public function recover( $args ) {
		$this->operate_results( 'recover', true, $args );
	}

	public function empty() {
		$this->operate_results( 'delete', true );
	}

	public function scan( $args ) {
		$args = (array) $args;
		$core = $this->core();
		$options = $core->get_all_options();
		$method = $options['method'];
		if ( in_array( 'filesystem', $args, true ) ) $method = 'files';
		if ( in_array( 'media', $args, true ) ) $method = 'media';
		if ( !in_array( $method, array( 'media', 'files' ), true ) ) {
				WP_CLI::error( __( 'WP-CLI scanning currently supports only media and filesystem methods.', 'media-cleaner' ) );
		}

		$config = $core->sanitize_scan_config( $options );
		if ( in_array( 'check-content', $args, true ) ) {
			$config[ $method === 'media' ? 'content' : 'filesystem_content' ] = true;
		}
		if ( in_array( 'uncheck-content', $args, true ) ) {
			$config[ $method === 'media' ? 'content' : 'filesystem_content' ] = false;
		}
		if ( $method === 'files' && in_array( 'check-media', $args, true ) ) $config['media_library'] = true;
		if ( in_array( 'uncheck-media', $args, true ) ) $config['media_library'] = false;
		$config['posts_buffer'] = min( 20, max( 1, (int) $config['posts_buffer'] ) );
		$config['medias_buffer'] = min( 100, max( 1, (int) $config['medias_buffer'] ) );
		$config['uploads_file_buffer'] = min( 500, max( 10, (int) $config['uploads_file_buffer'] ) );

		$storage = $core->prepare_private_storage();
		if ( is_wp_error( $storage ) ) WP_CLI::error( $storage->get_error_message() );
		$needs_dom = ( $method === 'media' && $config['content'] ) || ( $method === 'files' && $config['filesystem_content'] );
		if ( $needs_dom && !class_exists( 'DOMDocument' ) ) WP_CLI::error( __( 'The PHP DOM extension is required for content analysis.', 'media-cleaner' ) );

		$run = $core->runs->start( $method, $config, 'wp-cli-' . wp_generate_uuid4() );
		if ( is_wp_error( $run ) ) WP_CLI::error( $run->get_error_message() );
		$context = $core->set_run_context( $run->id );
		if ( is_wp_error( $context ) ) WP_CLI::error( $context->get_error_message() );
		$debug = in_array( 'debug', $args, true );

		try {
			WP_CLI::line( sprintf( __( 'Started scan run #%1$d (%2$s).', 'media-cleaner' ), $run->id, $method ) );
			$core->reset_issues();
			$core->reset_references();
			$core->save_progress( 'resetIssuesAndReferences' );

			$check_content = $method === 'media' ? $config['content'] : $config['filesystem_content'];
			if ( $check_content ) {
				$total = $core->engine->count_posts_to_check();
				$progress = \WP_CLI\Utils\make_progress_bar( __( 'Analyze content', 'media-cleaner' ), $total );
					$offset = 0;
					do {
						$message = '';
						$processed = 0;
						$finished = $core->engine->extractRefsFromContent( $offset, $config['posts_buffer'], $message, null, $processed );
						if ( !$finished && $processed < 1 ) throw new RuntimeException( __( 'The content scan could not advance its cursor.', 'media-cleaner' ) );
						for ( $i = 0; $i < $processed; $i++ ) $progress->tick();
						$offset += $processed;
					if ( $debug ) $this->manage_memory( $offset, true );
				} while ( !$finished );
				$progress->finish();
			}

			if ( $method === 'files' && $config['media_library'] ) {
				$total = $core->engine->count_media_entries();
				$progress = \WP_CLI\Utils\make_progress_bar( __( 'Analyze media library', 'media-cleaner' ), $total );
					$offset = 0;
					do {
						$message = '';
						$processed = 0;
						$finished = $core->engine->extractRefsFromLibrary( $offset, $config['posts_buffer'], $message, null, $processed );
						if ( !$finished && $processed < 1 ) throw new RuntimeException( __( 'The media-library reference scan could not advance its cursor.', 'media-cleaner' ) );
						for ( $i = 0; $i < $processed; $i++ ) $progress->tick();
						$offset += $processed;
				} while ( !$finished );
				$progress->finish();
			}

			if ( $method === 'files' ) {
				$rest = new Meow_WPMC_Rest( $core, $core->admin );
				$initialize = true;
				$last_checkpoint = null;
				$no_progress_yields = 0;
				do {
					$request = new WP_REST_Request( 'POST', '/media-cleaner/v1/retrieve_files' );
					$request->set_header( 'content-type', 'application/json' );
					$request->set_body( wp_json_encode( array( 'runId' => (int) $run->id, 'initialize' => $initialize, 'root' => '' ) ) );
					$response = $rest->rest_retrieve_files( $request );
					$data = $response->get_data();
					if ( empty( $data['success'] ) ) throw new RuntimeException( isset( $data['message'] ) ? $data['message'] : __( 'Filesystem batch failed.', 'media-cleaner' ) );
					$initialize = false;
					$finished = !empty( $data['data']['finished'] );
					if ( !$finished && !empty( $data['data']['yielded'] ) ) {
						$checkpoint = isset( $data['data']['work_id'], $data['data']['cursor'] )
							? (int) $data['data']['work_id'] . ':' . (int) $data['data']['cursor']
							: null;
						$no_progress_yields = $checkpoint !== null && $checkpoint === $last_checkpoint
							? $no_progress_yields + 1
							: 0;
						$last_checkpoint = $checkpoint;
						if ( $no_progress_yields >= 3 ) {
							throw new RuntimeException( __( 'The filesystem scan repeatedly yielded without advancing and was stopped to protect the server.', 'media-cleaner' ) );
						}
					}
					else {
						$no_progress_yields = 0;
					}
					if ( $debug ) $this->manage_memory( (int) $data['data']['checked'], true );
				} while ( !$finished );
			}
			else {
				$total = $core->engine->count_media_entries( $config['attach_is_use'] );
				$progress = \WP_CLI\Utils\make_progress_bar( __( 'Check media usage', 'media-cleaner' ), $total );
				$offset = 0;
				do {
					$ids = $core->engine->get_media_entries( $offset, $config['medias_buffer'], $config['attach_is_use'] );
					$core->timeout_check_start( count( $ids ) );
					foreach ( $ids as $media_id ) {
						$core->timeout_check();
						$core->engine->check_media( $media_id );
						$core->timeout_check_additem();
						$progress->tick();
					}
					$finished = count( $ids ) < $config['medias_buffer'];
					$core->save_progress( $finished ? 'retrieveMedia_finished' : 'retrieveMedia', array( 'limit' => $offset, 'limitSize' => $config['medias_buffer'] ) );
					$offset += $config['medias_buffer'];
				} while ( !$finished );
				$progress->finish();
			}

			$completed = $core->runs->complete( $run->id );
			if ( is_wp_error( $completed ) ) throw new RuntimeException( $completed->get_error_message() );
			$core->clear_run_context();
			WP_CLI::success( sprintf( __( 'Scan run #%d completed and published.', 'media-cleaner' ), $run->id ) );
			$this->issues();
		}
		catch ( Throwable $e ) {
			$core->runs->fail( $run->id, 'cli_scan_failed', $e->getMessage() );
			$core->clear_run_context();
			WP_CLI::error( sprintf( __( 'The scan was not published: %s', 'media-cleaner' ), $e->getMessage() ) );
		}

	}

}

?>
