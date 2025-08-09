<?php
/**
 * Plugin Name: Tutor AI
 * Plugin URI: https://your-website.com/tutor-ai
 * Description: Asistente IA que recomienda cursos y resuelve dudas en Tutor LMS.
 * Version: 1.0.0
 * Author: FABIAN
 * Text Domain: tutor-ai
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace TutorAI;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('TUTOR_AI_VERSION', '1.0.0');
define('TUTOR_AI_PATH', plugin_dir_path(__FILE__));
define('TUTOR_AI_URL', plugin_dir_url(__FILE__));
define('TUTOR_AI_BASENAME', plugin_basename(__FILE__));

// Load Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Main plugin initialization
 */
function tutor_ai_init() {
    // Check if Tutor LMS is active
    if (!class_exists('TUTOR\Tutor')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            echo __('Tutor AI requires Tutor LMS to be installed and activated.', 'tutor-ai');
            echo '</p></div>';
        });
        return;
    }

    // Initialize the plugin
    Plugin::instance();
}
add_action('plugins_loaded', __NAMESPACE__ . '\tutor_ai_init');

/**
 * Plugin activation hook
 */
function tutor_ai_activate() {
    Install::activate();
}
register_activation_hook(__FILE__, __NAMESPACE__ . '\tutor_ai_activate');

/**
 * Plugin deactivation hook
 */
function tutor_ai_deactivate() {
    Install::deactivate();
}
register_deactivation_hook(__FILE__, __NAMESPACE__ . '\tutor_ai_deactivate');

/**
 * Load plugin textdomain
 */
function tutor_ai_load_textdomain() {
    load_plugin_textdomain(
        'tutor-ai',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
}
add_action('init', __NAMESPACE__ . '\tutor_ai_load_textdomain');
