<?php
/**
 * Admin-only Recommended Tools reference page.
 * Simple plain list of recommended plugins/tools for the site; not public.
 * Registered under Tools, gated by manage_options.
 */

add_action('admin_menu', function () {
    add_management_page(
        'Recommended Tools',
        'Recommended Tools',
        'manage_options',
        'rbm-recommended-tools',
        'rbm_render_recommended_tools_page'
    );
});

function rbm_render_recommended_tools_page() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.'));
    }
    ?>
    <div class="wrap">
        <h1>Recommended Tools</h1>
        <p>Plugins and tools recommended for use on this site.</p>

        <ul style="list-style: disc; margin-left: 1.5em;">
            <li>
                <strong>Avada</strong> — Theme and page builder used to build and maintain all site pages.
                <a href="https://theme-fusion.com/avada/" target="_blank" rel="noopener noreferrer">Official site</a>
            </li>
            <li>
                <strong>Forminator</strong> — Form builder plugin used for contact, registration, and inquiry forms.
                <a href="https://wordpress.org/plugins/forminator/" target="_blank" rel="noopener noreferrer">Plugin page</a>
            </li>
            <li>
                <strong>Redirection</strong> — Manages URL redirects and tracks 404 errors.
                <a href="https://wordpress.org/plugins/redirection/" target="_blank" rel="noopener noreferrer">Plugin page</a>
            </li>
            <li>
                <strong>WP Media Folder</strong> — Media Library organization and image-management support, including the workflow for duplicating a teacher portrait, manually cropping the duplicate square, and assigning it as the Square Card Photo.
                <a href="https://wordpress.org/plugins/wp-media-folder-light/" target="_blank" rel="noopener noreferrer">Plugin page</a>
            </li>
        </ul>

        <h2>Forminator Form Templates</h2>
        <p>Exported Forminator form definitions (JSON). Import via Forminator &rarr; Import/Export to recreate a form.</p>
        <ul style="list-style: disc; margin-left: 1.5em;">
            <li>Lessons Inquiry Form — <a href="<?php echo esc_url(wp_get_attachment_url(20485)); ?>">Download</a></li>
            <li>Registration Form — <a href="<?php echo esc_url(wp_get_attachment_url(20484)); ?>">Download</a></li>
            <li>Contact Form — <a href="<?php echo esc_url(wp_get_attachment_url(20483)); ?>">Download</a></li>
        </ul>
    </div>
    <?php
}
