<?php
/**
 * Admin-only Website Maintenance guide.
 * Summarizes the approved Add-a-Lesson / Add-a-Teacher procedures for staff reference.
 * Not public: registered under Tools, gated by manage_options.
 */

add_action('admin_menu', function () {
    add_management_page(
        'Website Maintenance Guide',
        'Website Maintenance',
        'manage_options',
        'rbm-maintenance-guide',
        'rbm_render_maintenance_guide_page'
    );
});

function rbm_render_maintenance_guide_page() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.'));
    }
    ?>
    <div class="wrap">
        <h1>Website Maintenance Guide</h1>
        <p>Concise summary only. Full procedures/standards live in the project's <code>docs/</code> folder (see Full Documentation below) — this page does not duplicate them in full.</p>

        <h2>1. Add a Lesson</h2>
        <ol>
            <li>Confirm the real offering (teacher, photo, bio, instrument/category) exists — never invent one.</li>
            <li>Create/duplicate the approved lesson page structure from an existing similar destination page.</li>
            <li>Replace the title, slug, teacher content, and images — never carry over stale content from the source page.</li>
            <li>Verify the new page (publishes, resolves, renders correctly on desktop and mobile).</li>
            <li>Add one Lesson Card to the Lessons landing page grid, linking to the new page.</li>
            <li>Verify the Lessons landing page (card count, links, no overflow).</li>
        </ol>

        <h2>2. Add a Teacher</h2>
        <ol>
            <li>Confirm the teacher/source content is real (name, instrument, photo, bio).</li>
            <li>Choose the correct lesson/category page for their instrument.</li>
            <li>Add their Teacher/Bio section using the approved standard.</li>
            <li>Create a stable, unique anchor/id for their entry.</li>
            <li>Add a Teacher Card linking to that anchor.</li>
            <li>Verify the roster and card destination (no dead links, no stale entries).</li>
        </ol>

        <h2>3. Teacher Routing</h2>
        <p>Current category examples:</p>
        <table class="widefat striped" style="max-width:480px;">
            <thead><tr><th>Instrument</th><th>Category Page</th></tr></thead>
            <tbody>
                <tr><td>Piano</td><td>Piano &amp; Keyboards</td></tr>
                <tr><td>Guitar</td><td>Guitar Lessons</td></tr>
                <tr><td>Voice</td><td>Voice Lessons</td></tr>
                <tr><td>Strings</td><td>Strings</td></tr>
                <tr><td>Woodwinds</td><td>Woodwinds Lessons</td></tr>
                <tr><td>Drums</td><td>Drum Lessons</td></tr>
                <tr><td>Brass</td><td>Brass Lessons</td></tr>
            </tbody>
        </table>
        <p><strong>If the category doesn't exist yet:</strong> create the lesson destination first (see Add a Lesson), then add the teacher and Teacher Card. Never create a Teacher Card with nowhere to link.</p>
        <p><strong>Multi-category teachers:</strong> Teacher Cards may appear in more than one relevant category roster. Prefer one canonical Teacher/Bio destination where practical, to avoid duplicating long bio content unnecessarily.</p>

        <h2>4. Approved Components</h2>
        <ul style="list-style: disc; margin-left: 1.5em;">
            <li>Image + Text</li>
            <li>Text-Only</li>
            <li>Teacher/Bio</li>
            <li>Lesson Card</li>
            <li>Teacher Card</li>
            <li>Standard Page Header</li>
            <li>Call-to-Action</li>
            <li>Teacher Testimonial Section (only if/when separately approved)</li>
        </ul>

        <h2>5. Full Documentation</h2>
        <p>Full procedures and standards (in the project repository, not on this server):</p>
        <ul style="list-style: disc; margin-left: 1.5em;">
            <li><code>docs/0906-1649-STANDARD-Home-Page-Container-Formats.txt</code> — approved component standards</li>
            <li><code>docs/0906-1947-PROCEDURE-New-Lesson-Page-Template-And-Add-A-Lesson.txt</code> — full Add-a-Lesson procedure</li>
            <li><code>docs/0906-2042-PROCEDURE-New-Teacher-Page-And-Add-A-Teacher.txt</code> — full Add-a-Teacher procedure, including teacher category routing</li>
        </ul>
    </div>
    <?php
}
