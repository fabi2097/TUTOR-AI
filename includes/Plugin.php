<?php
/**
 * Main Plugin class
 *
 * @package TutorAI
 */

namespace TutorAI;

use TutorAI\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Plugin class
 */
class Plugin {
    use Singleton;

    /**
     * Plugin version
     */
    const VERSION = '1.0.0';

    /**
     * Initialize the plugin
     */
    protected function init() {
        $this->define_constants();
        $this->init_hooks();
        $this->init_components();
    }

    /**
     * Define plugin constants
     */
    private function define_constants() {
        if (!defined('TUTOR_AI_VERSION')) {
            define('TUTOR_AI_VERSION', self::VERSION);
        }
        if (!defined('TUTOR_AI_PATH')) {
            define('TUTOR_AI_PATH', plugin_dir_path(dirname(__FILE__)));
        }
        if (!defined('TUTOR_AI_URL')) {
            define('TUTOR_AI_URL', plugin_dir_url(dirname(__FILE__)));
        }
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('init', [$this, 'load_textdomain']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_public_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    /**
     * Initialize plugin components
     */
    private function init_components() {
        // Initialize REST API
        RestController::instance();
        
        // Initialize OpenAI Service
        OpenAIService::instance();
        
        // Initialize Recommender
        Recommender::instance();
        
        // Initialize admin settings if in admin
        if (is_admin()) {
            AdminSettings::instance();
        }
    }

    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'tutor-ai',
            false,
            dirname(plugin_basename(dirname(__FILE__))) . '/languages'
        );
    }

    /**
     * Enqueue public assets
     */
    public function enqueue_public_assets() {
        wp_enqueue_style(
            'tutor-ai-public',
            TUTOR_AI_URL . 'public/css/tutor-ai-public.css',
            [],
            TUTOR_AI_VERSION
        );

        wp_enqueue_script(
            'tutor-ai-public',
            TUTOR_AI_URL . 'public/js/tutor-ai-public.js',
            ['jquery'],
            TUTOR_AI_VERSION,
            true
        );

        wp_localize_script('tutor-ai-public', 'tutorAI', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'rest_url' => rest_url('tutor-ai/v1/'),
            'nonce' => wp_create_nonce('tutor_ai_nonce'),
        ]);
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets() {
        wp_enqueue_style(
            'tutor-ai-admin',
            TUTOR_AI_URL . 'admin/css/tutor-ai-admin.css',
            [],
            TUTOR_AI_VERSION
        );

        wp_enqueue_script(
            'tutor-ai-admin',
            TUTOR_AI_URL . 'admin/js/tutor-ai-admin.js',
            ['jquery'],
            TUTOR_AI_VERSION,
            true
        );
    }
}