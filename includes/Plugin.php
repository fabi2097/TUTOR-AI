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
        add_action('wp_footer', [$this, 'render_chat_widget']);
    }

    /**
     * Initialize plugin components
     */
    private function init_components() {
        // Initialize REST API
        RestController::instance();
        
        // Initialize AI Service (unified for all providers)
        // AIService will be instantiated when needed
        
        // Initialize Recommender
        Recommender::instance();
        
        // Initialize admin settings if in admin
        if (is_admin()) {
            AdminSettings::instance();
        }
        
        // Add admin notice hook
        add_action('admin_notices', [$this, 'display_admin_notices']);
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

    /**
     * Display admin notices
     */
    public function display_admin_notices() {
        // Check if API key is configured
        $api_key = get_option('tutor_ai_openai_api_key', '');
        
        if (empty($api_key) && current_user_can('manage_options')) {
            $settings_url = admin_url('options-general.php?page=tutor-ai-settings');
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p><strong>Tutor AI:</strong> ' . sprintf(
                __('Por favor configura tu clave API de OpenAI en la <a href="%s">página de configuración</a> para activar las funciones de IA.', 'tutor-ai'),
                esc_url($settings_url)
            ) . '</p>';
            echo '</div>';
        }
        
        // Check if Tutor LMS is active
        if (!class_exists('TUTOR\Tutor')) {
            echo '<div class="notice notice-error">';
            echo '<p><strong>Tutor AI:</strong> ' . __('Este plugin requiere Tutor LMS para funcionar correctamente.', 'tutor-ai') . '</p>';
            echo '</div>';
        }
    }

    /**
     * Render chat widget in frontend
     */
    public function render_chat_widget() {
        // Solo mostrar en el frontend, no en admin
        if (is_admin()) {
            return;
        }

        // Verificar si el chat está habilitado
        $settings = get_option('tutor_ai_settings', []);
        $chat_enabled = $settings['chat_enabled'] ?? false;
        
        if (!$chat_enabled) {
            return;
        }

        // Incluir el template del widget
        $widget_path = TUTOR_AI_PATH . 'public/views/chat-widget.php';
        if (file_exists($widget_path)) {
            include $widget_path;
        }
    }
}