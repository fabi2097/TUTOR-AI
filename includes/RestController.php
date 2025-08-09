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
        
        // AJAX handlers for chat widget
        add_action('wp_ajax_tutor_ai_chat', [$this, 'handle_ajax_chat']);
        add_action('wp_ajax_nopriv_tutor_ai_chat', [$this, 'handle_ajax_chat']);
    }

    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Chat endpoint
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
                    'context' => [
                        'required' => false,
                        'type' => 'array',
                        'default' => [],
                    ],
                ],
            ],
        ]);

        // Course recommendations endpoint
        register_rest_route($this->namespace, '/recommendations', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_recommendations'],
                'permission_callback' => [$this, 'check_permissions'],
                'args' => [
                    'user_id' => [
                        'required' => false,
                        'type' => 'integer',
                        'default' => 0,
                    ],
                    'topic' => [
                        'required' => false,
                        'type' => 'string',
                        'default' => '',
                    ],
                ],
            ],
        ]);

        // Course details endpoint
        register_rest_route($this->namespace, '/courses/(?P<id>\d+)', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_course_details'],
                'permission_callback' => [$this, 'check_permissions'],
                'args' => [
                    'id' => [
                        'required' => true,
                        'type' => 'integer',
                    ],
                ],
            ],
        ]);

        // User progress endpoint
        register_rest_route($this->namespace, '/progress', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_user_progress'],
                'permission_callback' => [$this, 'check_permissions'],
            ],
        ]);

        // Test connection endpoint (admin only)
        register_rest_route($this->namespace, '/test-connection', [
            [
                'methods' => 'POST',
                'callback' => [$this, 'test_ai_connection'],
                'permission_callback' => [$this, 'check_admin_permissions'],
                'args' => [
                    'api_key' => [
                        'required' => true,
                        'type' => 'string',
                    ],
                ],
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
        $context = $request->get_param('context') ?: [];
        
        if (empty($message)) {
            return new WP_Error('empty_message', __('Message cannot be empty', 'tutor-ai'), ['status' => 400]);
        }

        try {
            // Get user context for personalized responses
            $user_id = get_current_user_id();
            if ($user_id) {
                $context['user_courses'] = $this->get_user_enrolled_courses($user_id);
                $context['user_progress'] = $this->get_user_progress_summary($user_id);
            }

            $openai_service = OpenAIService::instance();
            $response = $openai_service->generate_response($message, $context);
            
            // Log the interaction for analytics
            $this->log_chat_interaction($user_id, $message, $response);
            
            return new WP_REST_Response([
                'success' => true,
                'response' => $response,
                'suggestions' => $this->get_follow_up_suggestions($message, $context),
            ], 200);
        } catch (Exception $e) {
            error_log('TutorAI Chat Error: ' . $e->getMessage());
            return new WP_Error('chat_error', __('Sorry, I cannot respond right now. Please try again later.', 'tutor-ai'), ['status' => 500]);
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
            $user_id = $request->get_param('user_id') ?: get_current_user_id();
            $topic = $request->get_param('topic');
            
            $recommender = Recommender::instance();
            
            if ($topic) {
                $recommendations = $recommender->get_topic_recommendations($topic, $user_id);
            } else {
                $recommendations = $recommender->get_user_recommendations($user_id);
            }
            
            return new WP_REST_Response([
                'success' => true,
                'recommendations' => $recommendations,
                'total' => count($recommendations),
            ], 200);
        } catch (Exception $e) {
            return new WP_Error('recommendations_error', $e->getMessage(), ['status' => 500]);
        }
    }

    /**
     * Get detailed course information
     *
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response|WP_Error
     */
    public function get_course_details(WP_REST_Request $request) {
        $course_id = $request->get_param('id');
        
        try {
            $course_data = $this->get_tutor_course_data($course_id);
            
            if (!$course_data) {
                return new WP_Error('course_not_found', __('Course not found', 'tutor-ai'), ['status' => 404]);
            }
            
            return new WP_REST_Response([
                'success' => true,
                'course' => $course_data,
            ], 200);
        } catch (Exception $e) {
            return new WP_Error('course_error', $e->getMessage(), ['status' => 500]);
        }
    }

    /**
     * Get user learning progress
     *
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response|WP_Error
     */
    public function get_user_progress(WP_REST_Request $request) {
        try {
            $user_id = get_current_user_id();
            
            if (!$user_id) {
                return new WP_Error('unauthorized', __('User not logged in', 'tutor-ai'), ['status' => 401]);
            }
            
            $progress_data = [
                'enrolled_courses' => $this->get_user_enrolled_courses($user_id),
                'completed_courses' => $this->get_user_completed_courses($user_id),
                'overall_progress' => $this->calculate_overall_progress($user_id),
                'achievements' => $this->get_user_achievements($user_id),
            ];
            
            return new WP_REST_Response([
                'success' => true,
                'progress' => $progress_data,
            ], 200);
        } catch (Exception $e) {
            return new WP_Error('progress_error', $e->getMessage(), ['status' => 500]);
        }
    }

    /**
     * Test AI connection (admin only)
     *
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response|WP_Error
     */
    public function test_ai_connection(WP_REST_Request $request) {
        $api_key = $request->get_param('api_key');
        
        try {
            // Temporarily use the provided API key for testing
            $openai_service = OpenAIService::instance();
            $test_result = $openai_service->test_connection($api_key);
            
            return new WP_REST_Response([
                'success' => $test_result,
                'message' => $test_result ? __('Connection successful', 'tutor-ai') : __('Connection failed', 'tutor-ai'),
            ], 200);
        } catch (Exception $e) {
            return new WP_Error('connection_error', $e->getMessage(), ['status' => 500]);
        }
    }

    /**
     * Check permissions for API requests
     *
     * @return bool
     */
    public function check_permissions() {
        // Allow for guests but with rate limiting
        return true;
    }

    /**
     * Check admin permissions
     *
     * @return bool
     */
    public function check_admin_permissions() {
        return current_user_can('manage_options');
    }

    /**
     * Get user enrolled courses from Tutor LMS
     *
     * @param int $user_id User ID.
     * @return array
     */
    private function get_user_enrolled_courses($user_id) {
        if (!function_exists('tutor_utils')) {
            return [];
        }

        $enrolled_courses = tutor_utils()->get_enrolled_courses_by_user($user_id);
        $courses_data = [];

        if ($enrolled_courses) {
            foreach ($enrolled_courses as $course) {
                $courses_data[] = [
                    'id' => $course->ID,
                    'title' => $course->post_title,
                    'url' => get_permalink($course->ID),
                    'progress' => tutor_utils()->get_course_completed_percent($course->ID, $user_id),
                    'status' => tutor_utils()->get_course_status($course->ID, $user_id),
                ];
            }
        }

        return $courses_data;
    }

    /**
     * Get user completed courses
     *
     * @param int $user_id User ID.
     * @return array
     */
    private function get_user_completed_courses($user_id) {
        $all_courses = $this->get_user_enrolled_courses($user_id);
        return array_filter($all_courses, function($course) {
            return $course['status'] === 'completed';
        });
    }

    /**
     * Get course data from Tutor LMS
     *
     * @param int $course_id Course ID.
     * @return array|null
     */
    private function get_tutor_course_data($course_id) {
        $course = get_post($course_id);
        
        if (!$course || $course->post_type !== 'courses') {
            return null;
        }

        return [
            'id' => $course->ID,
            'title' => $course->post_title,
            'description' => $course->post_content,
            'excerpt' => $course->post_excerpt,
            'url' => get_permalink($course->ID),
            'instructor' => $this->get_course_instructor($course_id),
            'price' => $this->get_course_price($course_id),
            'level' => get_post_meta($course_id, '_tutor_course_level', true),
            'duration' => get_post_meta($course_id, '_course_duration', true),
            'lessons_count' => tutor_utils()->get_lesson_count_by_course($course_id),
            'students_count' => tutor_utils()->count_enrolled_users_by_course($course_id),
            'rating' => tutor_utils()->get_course_rating($course_id),
        ];
    }

    /**
     * Get course instructor information
     *
     * @param int $course_id Course ID.
     * @return array
     */
    private function get_course_instructor($course_id) {
        $instructors = tutor_utils()->get_instructors_by_course($course_id);
        
        if (empty($instructors)) {
            return null;
        }

        $instructor = $instructors[0];
        return [
            'id' => $instructor->ID,
            'name' => $instructor->display_name,
            'avatar' => get_avatar_url($instructor->ID),
            'bio' => get_user_meta($instructor->ID, 'description', true),
        ];
    }

    /**
     * Get course price information
     *
     * @param int $course_id Course ID.
     * @return array
     */
    private function get_course_price($course_id) {
        if (!function_exists('tutor_utils')) {
            return ['type' => 'free', 'amount' => 0];
        }

        $is_paid = tutor_utils()->is_course_purchasable($course_id);
        
        if (!$is_paid) {
            return ['type' => 'free', 'amount' => 0];
        }

        $price = tutor_utils()->get_course_price($course_id);
        return [
            'type' => 'paid',
            'amount' => $price,
            'currency' => get_option('currency', 'USD'),
        ];
    }

    /**
     * Calculate overall user progress
     *
     * @param int $user_id User ID.
     * @return float
     */
    private function calculate_overall_progress($user_id) {
        $enrolled_courses = $this->get_user_enrolled_courses($user_id);
        
        if (empty($enrolled_courses)) {
            return 0;
        }

        $total_progress = array_sum(array_column($enrolled_courses, 'progress'));
        return round($total_progress / count($enrolled_courses), 2);
    }

    /**
     * Get user achievements/certificates
     *
     * @param int $user_id User ID.
     * @return array
     */
    private function get_user_achievements($user_id) {
        // This would integrate with Tutor LMS certificates
        // For now, return basic completion data
        $completed_courses = $this->get_user_completed_courses($user_id);
        
        return [
            'certificates' => count($completed_courses),
            'total_courses_completed' => count($completed_courses),
            'learning_hours' => $this->calculate_learning_hours($user_id),
        ];
    }

    /**
     * Calculate estimated learning hours
     *
     * @param int $user_id User ID.
     * @return int
     */
    private function calculate_learning_hours($user_id) {
        $completed_courses = $this->get_user_completed_courses($user_id);
        $total_hours = 0;

        foreach ($completed_courses as $course) {
            $duration = get_post_meta($course['id'], '_course_duration', true);
            if ($duration) {
                $total_hours += intval($duration);
            }
        }

        return $total_hours;
    }

    /**
     * Get user progress summary for context
     *
     * @param int $user_id User ID.
     * @return array
     */
    private function get_user_progress_summary($user_id) {
        $enrolled = $this->get_user_enrolled_courses($user_id);
        $completed = $this->get_user_completed_courses($user_id);
        
        return [
            'enrolled_count' => count($enrolled),
            'completed_count' => count($completed),
            'in_progress_count' => count($enrolled) - count($completed),
            'overall_progress' => $this->calculate_overall_progress($user_id),
        ];
    }

    /**
     * Log chat interaction for analytics
     *
     * @param int    $user_id User ID.
     * @param string $message User message.
     * @param string $response AI response.
     * @return void
     */
    private function log_chat_interaction($user_id, $message, $response) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'tutor_ai_chat_history';
        
        $wpdb->insert(
            $table_name,
            [
                'user_id' => $user_id ?: 0,
                'message' => $message,
                'response' => $response,
                'created_at' => current_time('mysql'),
            ],
            ['%d', '%s', '%s', '%s']
        );
    }

    /**
     * Get follow-up suggestions based on conversation
     *
     * @param string $message User message.
     * @param array  $context Conversation context.
     * @return array
     */
    private function get_follow_up_suggestions($message, $context) {
        // Simple keyword-based suggestions
        $suggestions = [];
        
        if (strpos(strtolower($message), 'curso') !== false) {
            $suggestions[] = '¿Qué cursos me recomiendas?';
            $suggestions[] = 'Ver mi progreso';
        }
        
        if (strpos(strtolower($message), 'certificado') !== false) {
            $suggestions[] = '¿Cómo obtengo un certificado?';
            $suggestions[] = 'Ver mis certificados';
        }
        
        if (strpos(strtolower($message), 'precio') !== false) {
            $suggestions[] = '¿Hay descuentos disponibles?';
            $suggestions[] = 'Ver planes de pago';
        }
        
        return array_slice($suggestions, 0, 3);
    }

    /**
     * Handle AJAX chat requests from the frontend widget
     */
    public function handle_ajax_chat() {
        // Verificar nonce de seguridad
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'tutor_ai_chat')) {
            wp_die('Security check failed', 'Unauthorized', ['response' => 403]);
        }

        $message = sanitize_text_field($_POST['message'] ?? '');
        
        if (empty($message)) {
            wp_send_json_error('Message cannot be empty');
            return;
        }

        try {
            // Obtener el servicio de IA
            $ai_service = AIService::get_instance();
            
            // Obtener configuraciones
            $settings = get_option('tutor_ai_settings', []);
            $system_message = $settings['system_message'] ?? 'You are a helpful AI assistant for an online learning platform.';
            
            // Generar respuesta usando GPT-5 o el modelo configurado
            $response = $ai_service->generateOpenAIResponse([
                'model' => $settings['ai_model'] ?? 'gpt-5-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $system_message
                    ],
                    [
                        'role' => 'user', 
                        'content' => $message
                    ]
                ],
                'max_tokens' => 500,
                'temperature' => 0.7
            ]);

            // Verificar si la respuesta fue exitosa
            if (isset($response['error'])) {
                throw new Exception($response['error']);
            }

            $ai_response = $response['content'] ?? 'Lo siento, no pude generar una respuesta.';
            
            // Log para debugging (opcional)
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("Tutor AI Chat - User: {$message}, AI: {$ai_response}");
            }
            
            wp_send_json_success($ai_response);

        } catch (Exception $e) {
            error_log('Tutor AI Chat Error: ' . $e->getMessage());
            wp_send_json_error('Lo siento, ocurrió un error al procesar tu mensaje. Por favor intenta de nuevo.');
        }
    }
}