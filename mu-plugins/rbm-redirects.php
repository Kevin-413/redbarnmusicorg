<?php
add_action("template_redirect", function() {
    if (isset($_SERVER["REQUEST_URI"]) && strpos($_SERVER["REQUEST_URI"], "/registration/") === 0) {
        wp_redirect(home_url("/lessons-inquiry/"), 301);
        exit;
    }
});