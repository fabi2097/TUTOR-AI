<?php
/**
 * Admin Settings class
 *
 * @package TutorAI
 */

namespace TutorAI;

use TutorAI\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Settings class
 */
class AdminSettings {
    use Singleton;

    /**
     * Settings page slug
     */
    const PAGE_SLUG = 'tutor-ai-settings';

    /**
     * Initialize admin settings
     */
    protected function init() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'init_settings']);
        add_action('wp_ajax_tutor_ai_test_connection', [$this, 'ajax_test_connection']);
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            __('Tutor AI Settings', 'tutor-ai'),
            __('Tutor AI', 'tutor-ai'),
            'manage_options',
            self::PAGE_SLUG,
            [$this, 'render_settings_page']
        );
    }

    /**
     * Initialize settings
     */
    public function init_settings() {
        register_setting('tutor_ai_settings', 'tutor_ai_openai_api_key');
        register_setting('tutor_ai_settings', 'tutor_ai_chat_enabled');
        register_setting('tutor_ai_settings', 'tutor_ai_recommendations_enabled');

        add_settings_section(
            'tutor_ai_general',
            __('General Settings', 'tutor-ai'),
            [$this, 'render_section_description'],
            self::PAGE_SLUG
        );

        add_settings_field(
            'tutor_ai_openai_api_key',
            __('OpenAI API Key', 'tutor-ai'),
            [$this, 'render_api_key_field'],
            self::PAGE_SLUG,
            'tutor_ai_general'
        );

        add_settings_field(
            'tutor_ai_chat_enabled',
            __('Enable AI Chat', 'tutor-ai'),
            [$this, 'render_chat_enabled_field'],
            self::PAGE_SLUG,
            'tutor_ai_general'
        );

        add_settings_field(
            'tutor_ai_recommendations_enabled',
            __('Enable Course Recommendations', 'tutor-ai'),
            [$this, 'render_recommendations_enabled_field'],
            self::PAGE_SLUG,
            'tutor_ai_general'
        );
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Handle form submission
        if (isset($_POST['submit']) && wp_verify_nonce($_POST['_wpnonce'], 'tutor_ai_settings')) {
            $this->save_settings();
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Configuración guardada exitosamente!', 'tutor-ai') . '</p></div>';
        }

        include TUTOR_AI_PATH . 'admin/views/settings-page.php';
    }

    /**
     * Save settings from form submission
     */
    public function save_settings($data = null) {
        // Si no se pasa data, usar $_POST
        if ($data === null) {
            $data = $_POST;
        }
        
        // General settings
        update_option('tutor_ai_openai_api_key', sanitize_text_field($data['tutor_ai_openai_api_key'] ?? ''));
        update_option('tutor_ai_anthropic_api_key', sanitize_text_field($data['tutor_ai_anthropic_api_key'] ?? ''));
        update_option('tutor_ai_google_api_key', sanitize_text_field($data['tutor_ai_google_api_key'] ?? ''));
        update_option('tutor_ai_provider', sanitize_text_field($data['ai_provider'] ?? 'openai'));
        update_option('tutor_ai_model', sanitize_text_field($data['ai_model'] ?? 'gpt-5-mini'));
        update_option('tutor_ai_temperature', floatval($data['temperature'] ?? 0.7));
        update_option('tutor_ai_max_tokens', intval($data['max_tokens'] ?? 800));
        
        // Behavior settings
        update_option('tutor_ai_bot_name', sanitize_text_field($data['bot_name'] ?? 'Asistente de Cursos'));
        update_option('tutor_ai_bot_language', sanitize_text_field($data['bot_language'] ?? 'es'));
        update_option('tutor_ai_welcome_message', sanitize_textarea_field($data['welcome_message'] ?? ''));
        update_option('tutor_ai_system_prompt', sanitize_textarea_field($data['system_prompt'] ?? ''));
        
        // Knowledge settings
        update_option('tutor_ai_rag_enabled', isset($data['rag_enabled']) ? 1 : 0);
        update_option('tutor_ai_rag_sources', $data['rag_sources'] ?? []);
        
        // Integration settings
        update_option('tutor_ai_lms_platform', sanitize_text_field($data['lms_platform'] ?? 'tutor'));
        update_option('tutor_ai_enable_course_recommendations', isset($data['enable_course_recommendations']) ? 1 : 0);
        update_option('tutor_ai_enable_progress_tracking', isset($data['enable_progress_tracking']) ? 1 : 0);
        update_option('tutor_ai_enable_enrollment_help', isset($data['enable_enrollment_help']) ? 1 : 0);
        
        // Appearance settings  
        update_option('tutor_ai_chat_enabled', isset($data['tutor_ai_chat_enabled']) ? 1 : 0);
        update_option('tutor_ai_chat_position', sanitize_text_field($data['chat_position'] ?? 'bottom-right'));
        update_option('tutor_ai_chat_color', sanitize_hex_color($data['chat_color'] ?? '#2563eb'));
        
        // Advanced settings
        update_option('tutor_ai_daily_message_limit', intval($data['daily_message_limit'] ?? 50));
        update_option('tutor_ai_response_timeout', intval($data['response_timeout'] ?? 30));
        update_option('tutor_ai_enable_logging', isset($data['enable_logging']) ? 1 : 0);
        update_option('tutor_ai_enable_debug', isset($data['enable_debug']) ? 1 : 0);
        
        // Also save unified settings array
        $this->save_unified_settings();
    }

    /**
     * Save all settings in a unified array for easier access
     */
    private function save_unified_settings() {
        $unified_settings = [
            // API Settings
            'ai_provider' => get_option('tutor_ai_provider', 'openai'),
            'ai_model' => get_option('tutor_ai_model', 'gpt-5-mini'),
            'openai_api_key' => get_option('tutor_ai_openai_api_key', ''),
            'anthropic_api_key' => get_option('tutor_ai_anthropic_api_key', ''),
            'google_api_key' => get_option('tutor_ai_google_api_key', ''),
            
            // Bot Configuration
            'bot_name' => get_option('tutor_ai_bot_name', 'Asistente de Cursos'),
            'welcome_message' => get_option('tutor_ai_welcome_message', '¡Hola! Soy tu asistente de cursos. ¿En qué puedo ayudarte hoy?'),
            'system_message' => get_option('tutor_ai_system_prompt', 'You are a helpful AI assistant for an online learning platform.'),
            
            // Chat Widget
            'chat_enabled' => get_option('tutor_ai_chat_enabled', 1),
            'widget_position' => get_option('tutor_ai_chat_position', 'bottom-right'),
            'widget_color' => get_option('tutor_ai_chat_color', '#2563eb'),
            
            // Integration
            'lms_platform' => get_option('tutor_ai_lms_platform', 'tutor'),
            'course_recommendations' => get_option('tutor_ai_enable_course_recommendations', 1),
            'progress_tracking' => get_option('tutor_ai_enable_progress_tracking', 1),
            'enrollment_help' => get_option('tutor_ai_enable_enrollment_help', 1),
            
            // Advanced
            'daily_message_limit' => get_option('tutor_ai_daily_message_limit', 50),
            'response_timeout' => get_option('tutor_ai_response_timeout', 30),
            'logging_enabled' => get_option('tutor_ai_enable_logging', 0),
            'debug_enabled' => get_option('tutor_ai_enable_debug', 0),
        ];
        
        update_option('tutor_ai_settings', $unified_settings);
    }

    /**
     * AJAX handler for testing AI connection
     */
    public function ajax_test_connection() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'tutor_ai_test_connection')) {
            wp_die(__('Security check failed', 'tutor-ai'));
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'tutor-ai'));
        }

        $api_key = sanitize_text_field($_POST['api_key'] ?? '');
        $provider = sanitize_text_field($_POST['provider'] ?? 'openai');

        if (empty($api_key)) {
            wp_send_json_error(__('API key is required', 'tutor-ai'));
        }

        try {
            // Test the connection using AI service
            $ai_service = new AIService();
            $test_result = $ai_service->test_connection($api_key, $provider);

            if ($test_result['success']) {
                wp_send_json_success($test_result['message']);
            } else {
                wp_send_json_error($test_result['message']);
            }
        } catch (Exception $e) {
            wp_send_json_error(sprintf(__('Error: %s', 'tutor-ai'), $e->getMessage()));
        }
    }

    /**
     * Render section description
     */
    public function render_section_description() {
        echo '<p>' . __('Configure your Tutor AI plugin settings below.', 'tutor-ai') . '</p>';
    }

    /**
     * Render API key field
     */
    public function render_api_key_field() {
        $api_key = get_option('tutor_ai_openai_api_key', '');
        echo '<input type="password" id="tutor_ai_openai_api_key" name="tutor_ai_openai_api_key" value="' . esc_attr($api_key) . '" class="regular-text" />';
        echo '<p class="description">' . __('Enter your OpenAI API key to enable AI features.', 'tutor-ai') . '</p>';
    }

    /**
     * Render chat enabled field
     */
    public function render_chat_enabled_field() {
        $enabled = get_option('tutor_ai_chat_enabled', 1);
        echo '<input type="checkbox" id="tutor_ai_chat_enabled" name="tutor_ai_chat_enabled" value="1" ' . checked(1, $enabled, false) . ' />';
        echo '<label for="tutor_ai_chat_enabled">' . __('Enable AI-powered chat widget', 'tutor-ai') . '</label>';
    }

    /**
     * Render recommendations enabled field
     */
    public function render_recommendations_enabled_field() {
        $enabled = get_option('tutor_ai_recommendations_enabled', 1);
        echo '<input type="checkbox" id="tutor_ai_recommendations_enabled" name="tutor_ai_recommendations_enabled" value="1" ' . checked(1, $enabled, false) . ' />';
        echo '<label for="tutor_ai_recommendations_enabled">' . __('Enable AI course recommendations', 'tutor-ai') . '</label>';
    }

    /**
     * Get all plugin settings
     *
     * @return array
     */
    public function get_settings() {
        return [
            'api_key' => get_option('tutor_ai_openai_api_key', ''),
            'ai_provider' => get_option('tutor_ai_provider', 'openai'),
            'ai_model' => get_option('tutor_ai_model', 'gpt-4o-mini'),
            'temperature' => get_option('tutor_ai_temperature', 0.7),
            'max_tokens' => get_option('tutor_ai_max_tokens', 800),
            
            'bot_name' => get_option('tutor_ai_bot_name', 'Asistente de Cursos'),
            'bot_language' => get_option('tutor_ai_bot_language', 'es'),
            'welcome_message' => get_option('tutor_ai_welcome_message', '¡Hola! Soy tu asistente de cursos. ¿En qué puedo ayudarte hoy?'),
            'system_prompt' => get_option('tutor_ai_system_prompt', 'Eres un asistente educativo especializado en ayudar a estudiantes con cursos online. Responde de manera clara, útil y motivadora.'),
            
            'rag_enabled' => get_option('tutor_ai_rag_enabled', 1),
            'rag_sources' => get_option('tutor_ai_rag_sources', ['courses', 'lessons']),
            
            'lms_platform' => get_option('tutor_ai_lms_platform', 'tutor'),
            'enable_course_recommendations' => get_option('tutor_ai_enable_course_recommendations', 1),
            'enable_progress_tracking' => get_option('tutor_ai_enable_progress_tracking', 1),
            'enable_enrollment_help' => get_option('tutor_ai_enable_enrollment_help', 0),
            
            'chat_enabled' => get_option('tutor_ai_chat_enabled', 1),
            'chat_position' => get_option('tutor_ai_chat_position', 'bottom-right'),
            'chat_color' => get_option('tutor_ai_chat_color', '#2563eb'),
            
            'daily_message_limit' => get_option('tutor_ai_daily_message_limit', 50),
            'response_timeout' => get_option('tutor_ai_response_timeout', 30),
            'enable_logging' => get_option('tutor_ai_enable_logging', 1),
            'enable_debug' => get_option('tutor_ai_enable_debug', 0),
        ];
    }

    /**
     * Check if API key is configured
     *
     * @return bool
     */
    public function is_api_key_configured() {
        $api_key = get_option('tutor_ai_openai_api_key', '');
        return !empty($api_key);
    }

    /**
     * Display admin notices for configuration issues
     */
    public function display_admin_notices() {
        if (!$this->is_api_key_configured()) {
            echo '<div class="notice notice-warning">';
            echo '<p>' . sprintf(
                __('Tutor AI: Please configure your OpenAI API key in the <a href="%s">settings page</a> to enable AI features.', 'tutor-ai'),
                admin_url('options-general.php?page=' . self::PAGE_SLUG)
            ) . '</p>';
            echo '</div>';
        }
    }
}