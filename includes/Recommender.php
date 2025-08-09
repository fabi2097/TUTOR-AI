<?php
/**
 * Course Recommender class
 *
 * @package TutorAI
 */

namespace TutorAI;

use TutorAI\Traits\Singleton;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Course Recommender class
 */
class Recommender {
    use Singleton;

    /**
     * Initialize the recommender
     */
    protected function init() {
        // Hook into user activity to collect data
        add_action('tutor_course_complete_after', [$this, 'update_user_profile']);
        add_action('tutor_quiz_attempt_ended', [$this, 'track_quiz_performance']);
    }

    /**
     * Get recommendations for a user
     *
     * @param int $user_id User ID.
     * @return array
     */
    public function get_user_recommendations($user_id) {
        $user_data = $this->get_user_learning_profile($user_id);
        
        // Try AI-powered recommendations first
        $ai_recommendations = $this->get_ai_recommendations($user_data);
        
        if (!empty($ai_recommendations)) {
            return $ai_recommendations;
        }
        
        // Fallback to rule-based recommendations
        return $this->get_rule_based_recommendations($user_data);
    }

    /**
     * Get AI-powered recommendations
     *
     * @param array $user_data User learning profile.
     * @return array
     */
    private function get_ai_recommendations($user_data) {
        try {
            $openai_service = OpenAIService::instance();
            return $openai_service->generate_course_recommendations($user_data);
        } catch (Exception $e) {
            error_log('TutorAI Recommender AI Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get rule-based recommendations as fallback
     *
     * @param array $user_data User learning profile.
     * @return array
     */
    private function get_rule_based_recommendations($user_data) {
        $recommendations = [];
        
        // Get all available courses
        $courses = get_posts([
            'post_type' => 'courses',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ]);

        foreach ($courses as $course) {
            $score = $this->calculate_course_relevance_score($course, $user_data);
            
            if ($score > 0.5) { // Threshold for recommendations
                $recommendations[] = [
                    'course_id' => $course->ID,
                    'title' => $course->post_title,
                    'score' => $score,
                    'reason' => $this->get_recommendation_reason($course, $user_data),
                ];
            }
        }

        // Sort by score and return top 5
        usort($recommendations, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($recommendations, 0, 5);
    }

    /**
     * Calculate course relevance score
     *
     * @param WP_Post $course Course post.
     * @param array   $user_data User data.
     * @return float
     */
    private function calculate_course_relevance_score($course, $user_data) {
        $score = 0.0;
        
        // Check category overlap
        $course_categories = wp_get_post_terms($course->ID, 'course-category', ['fields' => 'names']);
        $user_interests = $user_data['interests'] ?? [];
        
        $category_overlap = array_intersect($course_categories, $user_interests);
        $score += count($category_overlap) * 0.3;
        
        // Check difficulty level
        $course_level = get_post_meta($course->ID, '_tutor_course_level', true);
        $user_level = $user_data['skill_level'] ?? 'beginner';
        
        if ($course_level === $user_level) {
            $score += 0.4;
        } elseif ($this->is_next_level($user_level, $course_level)) {
            $score += 0.3;
        }
        
        // Check if user has prerequisites
        $prerequisites = get_post_meta($course->ID, '_tutor_course_prerequisites', true);
        if (!empty($prerequisites) && !empty($user_data['completed_courses'])) {
            $completed_prereqs = array_intersect($prerequisites, $user_data['completed_courses']);
            if (count($completed_prereqs) === count($prerequisites)) {
                $score += 0.3;
            }
        }
        
        return min($score, 1.0); // Cap at 1.0
    }

    /**
     * Check if course level is appropriate next step
     *
     * @param string $user_level User's current level.
     * @param string $course_level Course level.
     * @return bool
     */
    private function is_next_level($user_level, $course_level) {
        $levels = ['beginner', 'intermediate', 'advanced'];
        $user_index = array_search($user_level, $levels);
        $course_index = array_search($course_level, $levels);
        
        return $course_index === $user_index + 1;
    }

    /**
     * Get recommendation reason
     *
     * @param WP_Post $course Course post.
     * @param array   $user_data User data.
     * @return string
     */
    private function get_recommendation_reason($course, $user_data) {
        $reasons = [];
        
        $course_categories = wp_get_post_terms($course->ID, 'course-category', ['fields' => 'names']);
        $user_interests = $user_data['interests'] ?? [];
        
        $category_overlap = array_intersect($course_categories, $user_interests);
        if (!empty($category_overlap)) {
            $reasons[] = sprintf(__('Matches your interest in %s', 'tutor-ai'), implode(', ', $category_overlap));
        }
        
        $course_level = get_post_meta($course->ID, '_tutor_course_level', true);
        $user_level = $user_data['skill_level'] ?? 'beginner';
        
        if ($this->is_next_level($user_level, $course_level)) {
            $reasons[] = __('Perfect next step for your skill level', 'tutor-ai');
        }
        
        return !empty($reasons) ? implode('. ', $reasons) : __('Recommended for you', 'tutor-ai');
    }

    /**
     * Get user learning profile
     *
     * @param int $user_id User ID.
     * @return array
     */
    private function get_user_learning_profile($user_id) {
        return [
            'completed_courses' => $this->get_completed_courses($user_id),
            'interests' => $this->get_user_interests($user_id),
            'skill_level' => $this->determine_skill_level($user_id),
            'learning_goals' => get_user_meta($user_id, 'tutor_ai_learning_goals', true),
        ];
    }

    /**
     * Get completed courses for user
     *
     * @param int $user_id User ID.
     * @return array
     */
    private function get_completed_courses($user_id) {
        global $wpdb;
        
        $completed_courses = $wpdb->get_col($wpdb->prepare(
            "SELECT course_id FROM {$wpdb->prefix}tutor_enrollments 
             WHERE user_id = %d AND status = 'completed'",
            $user_id
        ));
        
        return array_map('intval', $completed_courses);
    }

    /**
     * Get user interests based on course categories
     *
     * @param int $user_id User ID.
     * @return array
     */
    private function get_user_interests($user_id) {
        $completed_courses = $this->get_completed_courses($user_id);
        $interests = [];
        
        foreach ($completed_courses as $course_id) {
            $categories = wp_get_post_terms($course_id, 'course-category', ['fields' => 'names']);
            $interests = array_merge($interests, $categories);
        }
        
        return array_unique($interests);
    }

    /**
     * Determine user skill level
     *
     * @param int $user_id User ID.
     * @return string
     */
    private function determine_skill_level($user_id) {
        $completed_count = count($this->get_completed_courses($user_id));
        
        if ($completed_count === 0) {
            return 'beginner';
        } elseif ($completed_count < 5) {
            return 'intermediate';
        } else {
            return 'advanced';
        }
    }

    /**
     * Update user profile after course completion
     *
     * @param int $course_id Course ID.
     */
    public function update_user_profile($course_id) {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return;
        }
        
        // Update skill level
        $new_level = $this->determine_skill_level($user_id);
        update_user_meta($user_id, 'tutor_ai_skill_level', $new_level);
        
        // Update interests
        $interests = $this->get_user_interests($user_id);
        update_user_meta($user_id, 'tutor_ai_interests', $interests);
    }

    /**
     * Track quiz performance
     *
     * @param int $attempt_id Quiz attempt ID.
     */
    public function track_quiz_performance($attempt_id) {
        // Implementation for tracking quiz performance
        // This could be used to adjust skill level recommendations
    }
}