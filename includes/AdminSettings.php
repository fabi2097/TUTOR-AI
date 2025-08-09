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
        if (isset($_POST['submit'])) {
            check_admin_referer('tutor_ai_settings');
            
            update_option('tutor_ai_openai_api_key', sanitize_text_field($_POST['tutor_ai_openai_api_key'] ?? ''));
            update_option('tutor_ai_chat_enabled', isset($_POST['tutor_ai_chat_enabled']) ? 1 : 0);
            update_option('tutor_ai_recommendations_enabled', isset($_POST['tutor_ai_recommendations_enabled']) ? 1 : 0);
            
            echo '<div class="notice notice-success"><p>' . __('Settings saved successfully!', 'tutor-ai') . '</p></div>';
        }

        include TUTOR_AI_PATH . 'admin/views/settings-page.php';
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
            'chat_enabled' => get_option('tutor_ai_chat_enabled', 1),
            'recommendations_enabled' => get_option('tutor_ai_recommendations_enabled', 1),
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