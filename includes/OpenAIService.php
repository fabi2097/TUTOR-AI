<?php
/**
 * OpenAI Service class
 *
 * @package TutorAI
 */

namespace TutorAI;

use TutorAI\Traits\Singleton;
use OpenAI;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * OpenAI Service class for handling AI interactions
 */
class OpenAIService {
    use Singleton;

    /**
     * OpenAI client instance
     *
     * @var OpenAI\Client
     */
    private $client;

    /**
     * Initialize the OpenAI service
     */
    protected function init() {
        $this->setup_client();
    }

    /**
     * Setup OpenAI client
     */
    private function setup_client() {
        $api_key = get_option('tutor_ai_openai_api_key', '');
        
        if (empty($api_key)) {
            return;
        }

        $this->client = OpenAI::client($api_key);
    }

    /**
     * Generate response from OpenAI
     *
     * @param string $message User message.
     * @param array  $context Additional context.
     * @return string
     * @throws Exception If API call fails.
     */
    public function generate_response($message, $context = []) {
        if (!$this->client) {
            throw new Exception(__('OpenAI API key not configured', 'tutor-ai'));
        }

        $system_prompt = $this->get_system_prompt($context);
        
        try {
            $response = $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $system_prompt,
                    ],
                    [
                        'role' => 'user',
                        'content' => $message,
                    ],
                ],
                'max_tokens' => 500,
                'temperature' => 0.7,
            ]);

            return $response->choices[0]->message->content ?? '';
        } catch (Exception $e) {
            error_log('TutorAI OpenAI Error: ' . $e->getMessage());
            throw new Exception(__('Failed to generate AI response', 'tutor-ai'));
        }
    }

    /**
     * Get system prompt for AI
     *
     * @param array $context Additional context.
     * @return string
     */
    private function get_system_prompt($context = []) {
        $prompt = __('You are a helpful educational assistant for an online learning platform. ', 'tutor-ai');
        $prompt .= __('Help students with course recommendations, answer questions about courses, and provide educational guidance. ', 'tutor-ai');
        $prompt .= __('Be friendly, professional, and educational in your responses.', 'tutor-ai');

        if (!empty($context['user_courses'])) {
            $prompt .= ' ' . sprintf(
                __('The user is currently enrolled in: %s', 'tutor-ai'),
                implode(', ', $context['user_courses'])
            );
        }

        return $prompt;
    }

    /**
     * Generate course recommendations using AI
     *
     * @param array $user_data User learning data.
     * @return array
     */
    public function generate_course_recommendations($user_data) {
        $prompt = $this->build_recommendation_prompt($user_data);
        
        try {
            $response = $this->generate_response($prompt);
            return $this->parse_recommendations($response);
        } catch (Exception $e) {
            error_log('TutorAI Recommendations Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Build recommendation prompt
     *
     * @param array $user_data User data.
     * @return string
     */
    private function build_recommendation_prompt($user_data) {
        $prompt = __('Based on the following user learning profile, recommend 3-5 relevant courses:', 'tutor-ai') . "\n\n";
        
        if (!empty($user_data['completed_courses'])) {
            $prompt .= __('Completed courses: ', 'tutor-ai') . implode(', ', $user_data['completed_courses']) . "\n";
        }
        
        if (!empty($user_data['interests'])) {
            $prompt .= __('Interests: ', 'tutor-ai') . implode(', ', $user_data['interests']) . "\n";
        }
        
        if (!empty($user_data['skill_level'])) {
            $prompt .= __('Skill level: ', 'tutor-ai') . $user_data['skill_level'] . "\n";
        }

        $prompt .= "\n" . __('Please provide course recommendations with brief explanations.', 'tutor-ai');
        
        return $prompt;
    }

    /**
     * Parse AI recommendations response
     *
     * @param string $response AI response.
     * @return array
     */
    private function parse_recommendations($response) {
        // Simple parsing - in production, you might want more sophisticated parsing
        $lines = explode("\n", $response);
        $recommendations = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line) && !str_starts_with($line, '#')) {
                $recommendations[] = $line;
            }
        }
        
        return array_slice($recommendations, 0, 5); // Limit to 5 recommendations
    }
}