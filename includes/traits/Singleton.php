<?php
/**
 * Singleton trait for TutorAI plugin
 *
 * @package TutorAI
 */

namespace TutorAI\Traits;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Singleton trait
 */
trait Singleton {
    /**
     * Instance storage
     *
     * @var static
     */
    private static $instance;

    /**
     * Get instance
     *
     * @return static
     */
    final public static function instance() {
        return static::$instance ??= new static();
    }

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        $this->init();
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    final public function __wakeup() {}

    /**
     * Initialize the class
     * Override this method in implementing classes
     */
    protected function init() {
        // Override in implementing classes
    }
}