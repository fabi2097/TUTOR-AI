<?php
/**
 * REST API Controller
 *
 * @package TutorAI
 */

namespace TutorAI;

use TutorAI\Traits\Singleton;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * REST API Controller class
 */
class RestController extends WP_REST_Controller {
    use Singleton;

    /**
     * API namespace
     */
    protected $namespace = 'tutor-ai/v1';

    /**
     * Initialize the REST controller
     */
    protected function init() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST API routes
     */
    public function register_routes() {
        register_rest_route($this->namespace, '/chat', [
            [
                'methods' => 'POST',
                'callback' => [$this, 'handle_chat'],
                'permission_callback' => [$this, 'check_permissions'],
                'args' => [
                    'message' => [
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_textarea_field',
                    ],
                ],
            ],
        ]);

        register_rest_route($this->namespace, '/recommendations', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_recommendations'],
                'permission_callback' => [$this, 'check_permissions'],
            ],
        ]);
    }

    /**
     * Handle chat requests
     *
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response|WP_Error
     */
    public function handle_chat(WP_REST_Request $request) {
        $message = $request->get_param('message');
        
        if (empty($message)) {
            return new WP_Error('empty_message', __('Message cannot be empty', 'tutor-ai'), ['status' => 400]);
        }

        try {
            $openai_service = OpenAIService::instance();
            $response = $openai_service->generate_response($message);
            
            return new WP_REST_Response([
                'success' => true,
                'response' => $response,
            ], 200);
        } catch (Exception $e) {
            return new WP_Error('chat_error', $e->getMessage(), ['status' => 500]);
        }
    }

    /**
     * Get course recommendations
     *
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response|WP_Error
     */
    public function get_recommendations(WP_REST_Request $request) {
        try {
            $recommender = Recommender::instance();
            $recommendations = $recommender->get_user_recommendations(get_current_user_id());
            
            return new WP_REST_Response([
                'success' => true,
                'recommendations' => $recommendations,
            ], 200);
        } catch (Exception $e) {
            return new WP_Error('recommendations_error', $e->getMessage(), ['status' => 500]);
        }
    }

    /**
     * Check permissions for API requests
     *
     * @return bool
     */
    public function check_permissions() {
        return is_user_logged_in();
    }
}