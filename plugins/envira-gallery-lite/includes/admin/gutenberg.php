<?php
/**
 * Gutenberg class.
 *
 * @since 1.8.5
 *
 * @package Envira_Gallery
 * @author  Envira Gallery Team <support@enviragallery.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gutenberg class.
 *
 * @since 1.8.5
 */
class Envira_Gutenberg {

	/**
	 * Holds Singleton
	 *
	 * @var object
	 */
	public static $instance = null;

	/**
	 * Base Class.
	 *
	 * @var object
	 */
	public $base = null;

	/**
	 * Common Class
	 *
	 * @var object
	 */
	public $common = null;

	/**
	 * Flag to determine if media modal is loaded.
	 *
	 * @since 1.8.5
	 *
	 * @var object
	 */
	public $loaded = false;

	/**
	 * Helper to store galleries.
	 *
	 * @since 1.8.5
	 *
	 * @var array
	 */
	public $galleries = [];

	/**
	 * Primary class constructor.
	 *
	 * @since 1.8.5
	 */
	public function __construct() {

		$this->base   = Envira_Gallery_Lite::get_instance();
		$this->common = Envira_Gallery_Common::get_instance();
		add_action( 'init', [ $this, 'register_block' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'editor_assets' ], 10 );
		add_action( 'current_screen', [ $this, 'get_galleries' ] );
	}

	/**
	 * Registers the block's metadata (attributes, apiVersion, editorStyle) from block.json.
	 *
	 * The block's `edit`/`save` are still registered client-side in `envira-gutenberg.js`;
	 * this server-side registration is what makes WordPress enqueue and load `editorStyle`
	 * into the iframed block editor canvas.
	 *
	 * `register_block_type()` only accepts a block.json path since WP 5.8, and
	 * `WP_Block_Type::$editor_style_handles` only exists since WP 6.1, so this is skipped
	 * entirely below that version rather than registering something WordPress can't use.
	 *
	 * @since 2.0.0
	 */
	public function register_block() {

		global $wp_version;

		if ( version_compare( $wp_version, '6.1', '<' ) ) {
			return;
		}

		// `assets/block.json` is a build copy of `_gutenberg/block.json`; the release zip
		// strips `_gutenberg/*`, so registration must point at the path that survives packaging.
		$registered = register_block_type( plugin_dir_path( $this->base->file ) . 'assets/block.json' );

		// Check the stylesheet file itself rather than $registered->editor_style_handles,
		// which is populated by register_block_style_handle() even when the file is missing.
		$stylesheet_exists = file_exists( plugin_dir_path( $this->base->file ) . 'assets/css/envira-gutenberg-editor.css' );

		if ( false === $registered || ! $stylesheet_exists ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- intentional, visible without WP_DEBUG.
			error_log( 'Envira Gallery: failed to register the envira/envira-gallery block.json - block editor styles will not load correctly in the iframed canvas.' );
			_doing_it_wrong( __METHOD__, 'Failed to register the envira/envira-gallery block.json - block editor styles will not load correctly in the iframed canvas.', '2.0.0' );
		}
	}

	/**
	 * Helper Method to get the galleries
	 *
	 * @since 1.0.0
	 */
	public function get_galleries() {
		$current_screen = get_current_screen();

		global $wp_version;

		if ( ! is_null( $current_screen ) && method_exists( $current_screen, 'is_block_editor' ) && ( $current_screen->is_block_editor() || ( version_compare( $wp_version, '5.8.0', '>' ) && 'widgets' === $current_screen->base || 'customize' === $current_screen->base ) ) ) {
			$request = new \WP_REST_Request( 'GET', '/wp/v2/envira-gallery' ); // request.
			$request->set_query_params( [ 'per_page' => 10 ] );
			$response        = rest_do_request( $request );
			$server          = rest_get_server();
			$this->galleries = $server->response_to_data( $response, false );
		}
	}

	/**
	 * Enqueue Gutenberg block assets for backend editor.
	 *
	 * `wp-blocks`: includes block type registration and related functions.
	 * `wp-element`: includes the WordPress Element abstraction for describing the structure of your blocks.
	 * `wp-i18n`: To internationalize the block's text.
	 *
	 * @since 1.0.0
	 */
	public function editor_assets() {

		$current_screen = get_current_screen();

		$dependencies = [ 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-data', 'wp-api-fetch', 'wp-hooks', 'wp-block-editor', 'wp-compose' ];
		if ( isset( $current_screen->id ) && 'widgets' !== $current_screen->id ) {
			$dependencies[] = 'wp-editor';
		}

		wp_enqueue_script(
			'envira_gutenberg-block-js',
			plugins_url( 'assets/js/envira-gutenberg.js', $this->base->file ), // Block.build.js: we register the block here and built with Webpack.
			$dependencies, // dependencies, defined above.
			$this->base->version,
			true // Enqueue the script in the footer.
		);

		$current_post_id = get_the_ID();

		if ( ! $current_post_id ) {
			$current_post_id = 0;
		}

		// Localize data to pass to the block script.
		$localized_data = [
			'postId'              => $current_post_id,
			'panelTitle'          => __( 'Envira Gallery', 'envira-gallery-lite' ),
			'contentHeading'      => __( 'Convert this gallery to an Envira Gallery', 'envira-gallery-lite' ),
			'contentDescription'  => __( 'Make your gallery shine with awesome customizations, drag and drop management, and much more.', 'envira-gallery-lite' ),
			'contentButtonText'   => __( 'Convert to Envira Gallery', 'envira-gallery-lite' ),
			'convertLoading'      => __( 'Converting...', 'envira-gallery-lite' ),
			'convertRestNonce'    => wp_create_nonce( 'wp_rest' ),
			'convertApiUrl'       => '/envira-convert/v1/convert-gallery',
			'errorMessage'        => __( 'Failed to convert the gallery. Please try again.', 'envira-gallery-lite' ),
			'confirmationMessage' => __( 'Are you sure you want to convert this gallery to an Envira Gallery?', 'envira-gallery-lite' ),
			'invalidBlockMessage' => __( 'This block is not a WordPress Gallery block.', 'envira-gallery-lite' ),
			'noImagesMessage'     => __( 'This gallery has no images.', 'envira-gallery-lite' ),
		];

		wp_localize_script( 'envira_gutenberg-block-js', 'enviraBlockData', $localized_data );

		$columns = $this->common->get_columns();

		$new_columns = [];
		foreach ( $columns as $options ) {
			$new_columns[] = [
				'label' => $options['name'],
				'value' => $options['value'],
			];
		}
		$lightbox_options = $this->common->get_lightbox_themes();

		$new_lightbox = [];
		foreach ( $lightbox_options as $options ) {
			$new_lightbox[] = [
				'label' => $options['name'],
				'value' => $options['value'],
			];
		}

		$image_option = $this->common->get_image_sizes();

		$new_sizes = [];
		foreach ( $image_option as $options ) {
			$new_sizes[] = [
				'label' => $options['name'],
				'value' => $options['value'],
			];
		}
		$options = [
			'columns'         => $new_columns,
			'lightbox_themes' => $new_lightbox,
			'image_sizes'     => $new_sizes,
			'sorting_options' => $this->common->get_sorting_options(),
			'galleries'       => is_array( $this->galleries ) ? $this->galleries : [],
		];

		$args_array = [
			'options'   => $options,
			'isLite'    => true,
			'admin_url' => admin_url(),
		];
		wp_localize_script(
			'envira_gutenberg-block-js',
			'envira_args',
			$args_array
		);
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return object The Envira_Gallery_License object.
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Envira_Gutenberg ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

$envira_gutenberg = Envira_Gutenberg::get_instance();
