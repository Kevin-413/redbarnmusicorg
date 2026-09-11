<?php
add_action("template_redirect", function() {
    if (isset($_SERVER["REQUEST_URI"]) && strpos($_SERVER["REQUEST_URI"], "/registration/") === 0) {
        wp_redirect(home_url("/lessons-inquiry/"), 301);
        exit;
    }
    // Legacy instrument-group page retirement (docs/0911-0914): Piano.
    if (isset($_SERVER["REQUEST_URI"]) && strpos($_SERVER["REQUEST_URI"], "/piano-at-the-red-barn/") === 0) {
        wp_redirect(home_url("/lessons/?category=piano"), 301);
        exit;
    }
    // Legacy instrument-group page retirement (docs/0911-0916): remaining 7 pages.
    if (isset($_SERVER["REQUEST_URI"]) && strpos($_SERVER["REQUEST_URI"], "/guitar-lessons/") === 0) {
        wp_redirect(home_url("/lessons/?category=guitar"), 301);
        exit;
    }
    if (isset($_SERVER["REQUEST_URI"]) && strpos($_SERVER["REQUEST_URI"], "/voice-lessons/") === 0) {
        wp_redirect(home_url("/lessons/?category=voice"), 301);
        exit;
    }
    if (isset($_SERVER["REQUEST_URI"]) && strpos($_SERVER["REQUEST_URI"], "/woodwinds-lessons/") === 0) {
        wp_redirect(home_url("/lessons/?category=woodwinds"), 301);
        exit;
    }
    if (isset($_SERVER["REQUEST_URI"]) && strpos($_SERVER["REQUEST_URI"], "/drum-lessons/") === 0) {
        wp_redirect(home_url("/lessons/?category=drums"), 301);
        exit;
    }
    if (isset($_SERVER["REQUEST_URI"]) && strpos($_SERVER["REQUEST_URI"], "/strings/") === 0) {
        wp_redirect(home_url("/lessons/?category=strings"), 301);
        exit;
    }
    if (isset($_SERVER["REQUEST_URI"]) && strpos($_SERVER["REQUEST_URI"], "/lessons-2/") === 0) {
        wp_redirect(home_url("/lessons/?category=composition"), 301);
        exit;
    }
    if (isset($_SERVER["REQUEST_URI"]) && strpos($_SERVER["REQUEST_URI"], "/brass-lessons/") === 0) {
        wp_redirect(home_url("/lessons/?category=brass"), 301);
        exit;
    }
});