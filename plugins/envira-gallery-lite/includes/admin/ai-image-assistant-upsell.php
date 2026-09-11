<?php
/**
 * AI Image Assistant — Lite upsell.
 *
 * Lite shows the same per-image purple sparkle trigger and the "Bulk Image
 * Assistant" button that Pro renders on the gallery editor, but every
 * interaction opens an upgrade modal instead of running the AI feature. This is
 * a pure upsell — no REST calls, no settings, no credits.
 *
 * @package Envira_Gallery_Lite
 * @since 1.12.7
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Envira_AI_Image_Assistant_Upsell.
 */
class Envira_AI_Image_Assistant_Upsell {

	/**
	 * Holds the base singleton.
	 *
	 * @var object
	 */
	public $base = null;

	/**
	 * Primary class constructor.
	 */
	public function __construct() {
		$this->base = \Envira_Gallery_Lite::get_instance();
	}

	/**
	 * Setup hooks.
	 */
	public function hooks() {
		// Bulk toolbar button — rendered just above the gallery image list.
		add_action( 'envira_gallery_do_default_display', [ $this, 'render_bulk_toolbar' ] );
		// Assets — only on the gallery editor screen.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
	}

	/**
	 * True only on the Envira gallery editor screen.
	 *
	 * @return bool
	 */
	private function is_gallery_editor() {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return false;
		}
		$screen = get_current_screen();
		if ( ! $screen ) {
			return false;
		}
		return ( 'envira' === $screen->post_type )
			|| ( false !== strpos( (string) $screen->id, 'envira-gallery' ) );
	}

	/**
	 * Render the "Bulk Image Assistant" button above the gallery image list.
	 * Per-image sparkle triggers are injected by JS onto each gallery <li>.
	 *
	 * @param WP_Post|null $post Current gallery post (unused — the button only opens the upsell).
	 */
	public function render_bulk_toolbar( $post = null ) {
		?>
		<div class="envira-ia envira-ia-toolbar-lite">
			<button type="button" class="envira-ia-bulk envira-ia-upsell-trigger">
				<span class="envira-ia-sparkle"></span>
				<?php esc_html_e( 'Bulk Image Assistant', 'envira-gallery-lite' ); ?>
			</button>
		</div>
		<?php
	}

	/**
	 * Cache-busting version for an asset — its file mtime so edits always
	 * reload, falling back to the plugin version if the file is missing.
	 *
	 * @param string $relative Path relative to the plugin root.
	 * @return string
	 */
	private function asset_version( $relative ) {
		$path = ENVIRA_LITE_DIR . $relative;
		return file_exists( $path ) ? (string) filemtime( $path ) : ENVIRA_LITE_VERSION;
	}

	/**
	 * Register, localize and enqueue the upsell assets on the gallery editor.
	 */
	public function enqueue() {
		if ( ! $this->is_gallery_editor() ) {
			return;
		}

		wp_enqueue_style(
			'envira-ai-image-assistant-upsell',
			ENVIRA_LITE_URL . 'assets/css/ai-image-assistant-upsell.css',
			[],
			$this->asset_version( 'assets/css/ai-image-assistant-upsell.css' )
		);

		wp_register_script(
			'envira-ai-image-assistant-upsell',
			ENVIRA_LITE_URL . 'assets/js/ai-image-assistant-upsell.js',
			[],
			$this->asset_version( 'assets/js/ai-image-assistant-upsell.js' ),
			true
		);

		$upgrade_url = \Envira_Gallery_Common_Admin::get_instance()->get_upgrade_link(
			'https://enviragallery.com/lite/',
			'ai-image-assistant',
			'aiimageassistantupsell'
		);

		wp_localize_script(
			'envira-ai-image-assistant-upsell',
			'enviraAIUpsell',
			[
				'upgradeUrl' => esc_url_raw( $upgrade_url ),
				'i18n'       => [
					'title'    => __( 'AI Image Assistant is a Pro Feature', 'envira-gallery-lite' ),
					'desc'     => __( 'Automatically generate captions, alt text, and tags for every image you upload - automatically powered by AI, so your galleries are SEO-ready and accessible without the manual work.', 'envira-gallery-lite' ),
					'upgrade'  => __( 'Upgrade to Pro', 'envira-gallery-lite' ),
					'close'    => __( 'Close', 'envira-gallery-lite' ),
					'trigger'  => __( 'Image Assistant', 'envira-gallery-lite' ),
					// The current plugin version is substituted into the copy
					// server-side, and "get 50% off" is emphasised. Rendered via
					// innerHTML — the only variable is the trusted version constant.
					'discount' => sprintf(
						/* translators: %1$s: edition label ("Lite"). %2$s/%3$s: opening/closing <strong> tags. */
						__( 'Envira Gallery %1$s users %2$sget 50%% off%3$s the regular price, automatically applied at checkout.', 'envira-gallery-lite' ),
						'Lite',
						'<strong>',
						'</strong>'
					),
				],
			]
		);

		wp_enqueue_script( 'envira-ai-image-assistant-upsell' );
	}
}
