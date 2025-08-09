<?php
/**
 * Install class for plugin activation/deactivation
 *
 * @package TutorAI
 */

namespace TutorAI;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Install class
 */
class Install {

    /**
     * Plugin activation
     */
    public static function activate() {
        // Create plugin tables if needed
        self::create_tables();
        
        // Set default options
        self::set_default_options();
        
        // Schedule cron jobs
        self::schedule_events();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public static function deactivate() {
        // Clear scheduled events
        self::clear_scheduled_events();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin uninstall
     */
    public static function uninstall() {
        // Remove plugin options
        self::remove_options();
        
        // Drop plugin tables
        self::drop_tables();
        
        // Clear scheduled events
        self::clear_scheduled_events();
    }

    /**
     * Create plugin tables
     */
    private static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Chat history table
        $chat_table = $wpdb->prefix . 'tutor_ai_chat_history';
        $chat_sql = "CREATE TABLE $chat_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            message text NOT NULL,
            response text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) $charset_collate;";

        // Recommendations table
        $recommendations_table = $wpdb->prefix . 'tutor_ai_recommendations';
        $recommendations_sql = "CREATE TABLE $recommendations_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            course_id bigint(20) NOT NULL,
            score decimal(3,2) NOT NULL,
            reason text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY course_id (course_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($chat_sql);
        dbDelta($recommendations_sql);
    }

    /**
     * Set default options
     */
    private static function set_default_options() {
        add_option('tutor_ai_chat_enabled', 1);
        add_option('tutor_ai_recommendations_enabled', 1);
        add_option('tutor_ai_version', TUTOR_AI_VERSION);
    }

    /**
     * Schedule cron events
     */
    private static function schedule_events() {
        if (!wp_next_scheduled('tutor_ai_generate_recommendations')) {
            wp_schedule_event(time(), 'daily', 'tutor_ai_generate_recommendations');
        }
    }

    /**
     * Clear scheduled events
     */
    private static function clear_scheduled_events() {
        wp_clear_scheduled_hook('tutor_ai_generate_recommendations');
    }

    /**
     * Remove plugin options
     */
    private static function remove_options() {
        delete_option('tutor_ai_openai_api_key');
        delete_option('tutor_ai_chat_enabled');
        delete_option('tutor_ai_recommendations_enabled');
        delete_option('tutor_ai_version');
    }

    /**
     * Drop plugin tables
     */
    private static function drop_tables() {
        global $wpdb;
        
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}tutor_ai_chat_history");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}tutor_ai_recommendations");
    }

    /**
     * Check if plugin needs to be updated
     */
    public static function maybe_update() {
        $current_version = get_option('tutor_ai_version', '0.0.0');
        
        if (version_compare($current_version, TUTOR_AI_VERSION, '<')) {
            self::update_plugin($current_version);
            update_option('tutor_ai_version', TUTOR_AI_VERSION);
        }
    }

    /**
     * Update plugin from old version
     *
     * @param string $from_version Previous version.
     */
    private static function update_plugin($from_version) {
        // Future version updates can be handled here
        // Example:
        // if (version_compare($from_version, '1.1.0', '<')) {
        //     self::update_to_110();
        // }
    }
}