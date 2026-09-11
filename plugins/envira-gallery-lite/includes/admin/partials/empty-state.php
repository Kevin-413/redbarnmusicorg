<?php
/**
 * Empty State Template for Envira Gallery List
 *
 * @since 1.8.15
 *
 * @package Envira_Gallery
 * @author  Envira Gallery Team <support@enviragallery.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="envira-empty-state">
	<div class="envira-empty-state-content">
		<div class="envira-empty-state-left">
			<div class="envira-empty-state-left-content">
				<p class="envira-empty-state-greeting"><?php esc_html_e( '👋 Hello there!', 'envira-gallery-lite' ); ?></p>

				<h2 class="envira-empty-state-title"><?php esc_html_e( "It looks like you haven't created any galleries yet.", 'envira-gallery-lite' ); ?></h2>

				<p class="envira-empty-state-description">
					<?php esc_html_e( 'You can use Envira Gallery to build beautiful and fast galleries with just a few clicks.', 'envira-gallery-lite' ); ?>
				</p>
			</div>
			<div class="envira-empty-state-actions">
				<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=envira' ) ); ?>" class="button button-primary button-hero envira-empty-state-button">
					<?php esc_html_e( 'Create your Gallery', 'envira-gallery-lite' ); ?>
					<img src="<?php echo esc_url( plugins_url( 'assets/images/arrow-right.png', ENVIRA_LITE_FILE ) ); ?>" alt="<?php esc_attr_e( 'Arrow Right', 'envira-gallery-lite' ); ?>" class="envira-button-arrow" />
				</a>
			</div>

			<p class="envira-empty-state-help">
				<?php
				printf(
					/* translators: %s: Documentation link */
					esc_html__( 'Need some help? Check out our %s', 'envira-gallery-lite' ),
					'<a href="https://enviragallery.com/docs/" target="_blank" rel="noopener">' . esc_html__( 'documentation', 'envira-gallery-lite' ) . '</a>.'
				);
				?>
			</p>
		</div>

		<div class="envira-empty-state-right">
			<img src="<?php echo esc_url( plugins_url( 'assets/images/gallery-demo.png', ENVIRA_LITE_FILE ) ); ?>" alt="<?php esc_attr_e( 'Gallery Demo', 'envira-gallery-lite' ); ?>" class="envira-empty-state-image" />
		</div>
	</div>
</div>

