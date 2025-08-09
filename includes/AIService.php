<?php
namespace TutorAI;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class AIService
{
    private $api_key;
    private $provider;
    private $model;
    private $client;
    private $endpoints;

    public function __construct()
    {
        $settings = get_option('tutor_ai_settings', []);
        $this->api_key = $settings['openai_api_key'] ?? '';
        $this->provider = $settings['ai_provider'] ?? 'openai';
        $this->model = $settings['ai_model'] ?? $this->getDefaultModel();
        
        $this->client = new Client([
            'timeout' => 30,
            'verify' => false
        ]);

        $this->setupEndpoints();
    }

    private function setupEndpoints()
    {
        $this->endpoints = [
            'openai' => [
                'base_url' => 'https://api.openai.com/v1',
                'chat_endpoint' => '/chat/completions',
                'test_endpoint' => '/models',
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->api_key,
                    'Content-Type' => 'application/json'
                ]
            ],
            'anthropic' => [
                'base_url' => 'https://api.anthropic.com/v1',
                'chat_endpoint' => '/messages',
                'test_endpoint' => '/messages',
                'headers' => [
                    'x-api-key' => $this->api_key,
                    'Content-Type' => 'application/json',
                    'anthropic-version' => '2023-06-01'
                ]
            ],
            'google' => [
                'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
                'chat_endpoint' => '/models/' . $this->model . ':generateContent',
                'test_endpoint' => '/models',
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]
        ];
    }

    private function getDefaultModel()
    {
        $defaults = [
            'openai' => 'gpt-5-mini', // GPT-5 Mini es el más económico y recomendado
            'anthropic' => 'claude-3-5-sonnet-20241022',
            'google' => 'gemini-1.5-pro'
        ];

        return $defaults[$this->provider] ?? 'gpt-5-mini';
    }

    public function test_connection($api_key = null, $provider = null)
    {
        if ($api_key) {
            $this->api_key = $api_key;
        }
        if ($provider) {
            $this->provider = $provider;
        }

        $this->setupEndpoints();

        if (!isset($this->endpoints[$this->provider])) {
            return [
                'success' => false,
                'message' => 'Proveedor no soportado: ' . $this->provider
            ];
        }

        $config = $this->endpoints[$this->provider];

        try {
            switch ($this->provider) {
                case 'openai':
                    return $this->testOpenAIConnection($config);
                case 'anthropic':
                    return $this->testAnthropicConnection($config);
                case 'google':
                    return $this->testGoogleConnection($config);
                default:
                    return [
                        'success' => false,
                        'message' => 'Proveedor no soportado: ' . $this->provider
                    ];
            }
        } catch (RequestException $e) {
            return [
                'success' => false,
                'message' => 'Error de conexión: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    private function testOpenAIConnection($config)
    {
        try {
            $response = $this->client->get(
                $config['base_url'] . $config['test_endpoint'],
                ['headers' => $config['headers']]
            );

            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody(), true);
                
                // Verificar que la respuesta contenga modelos
                if (isset($data['data']) && is_array($data['data'])) {
                    return [
                        'success' => true,
                        'message' => 'Conexión exitosa con ' . ucfirst($this->provider) . '. ' . count($data['data']) . ' modelos disponibles.'
                    ];
                }
                
                return [
                    'success' => true,
                    'message' => 'Conexión exitosa con ' . ucfirst($this->provider)
                ];
            }

            return [
                'success' => false,
                'message' => 'Error de autenticación. Código: ' . $response->getStatusCode()
            ];

        } catch (RequestException $e) {
            $message = 'Error de conexión';
            
            if ($e->hasResponse()) {
                $statusCode = $e->getResponse()->getStatusCode();
                $responseBody = $e->getResponse()->getBody()->getContents();
                $errorData = json_decode($responseBody, true);
                
                switch ($statusCode) {
                    case 401:
                        $message = 'API key inválida o expirada';
                        break;
                    case 403:
                        $message = 'Acceso denegado. Verifica permisos de la API key';
                        break;
                    case 429:
                        $message = 'Límite de velocidad excedido. Intenta más tarde';
                        break;
                    case 500:
                        $message = 'Error del servidor de OpenAI. Intenta más tarde';
                        break;
                    default:
                        if (isset($errorData['error']['message'])) {
                            $message = $errorData['error']['message'];
                        } else {
                            $message = 'Error HTTP ' . $statusCode;
                        }
                }
            }

            return [
                'success' => false,
                'message' => $message
            ];
        }
    }

    private function testAnthropicConnection($config)
    {
        $response = $this->client->post(
            $config['base_url'] . $config['chat_endpoint'],
            [
                'headers' => $config['headers'],
                'json' => [
                    'model' => $this->model,
                    'max_tokens' => 10,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => 'Test'
                        ]
                    ]
                ]
            ]
        );

        if ($response->getStatusCode() === 200) {
            return [
                'success' => true,
                'message' => 'Conexión exitosa con Anthropic Claude'
            ];
        }

        return [
            'success' => false,
            'message' => 'Error de autenticación con Anthropic'
        ];
    }

    private function testGoogleConnection($config)
    {
        $url = $config['base_url'] . $config['chat_endpoint'] . '?key=' . $this->api_key;
        
        $response = $this->client->post(
            $url,
            [
                'headers' => $config['headers'],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => 'Test']
                            ]
                        ]
                    ]
                ]
            ]
        );

        if ($response->getStatusCode() === 200) {
            return [
                'success' => true,
                'message' => 'Conexión exitosa con Google Gemini'
            ];
        }

        return [
            'success' => false,
            'message' => 'Error de autenticación con Google'
        ];
    }

    public function generate_response($messages, $context = [])
    {
        if (!$this->api_key) {
            throw new \Exception('API key no configurada');
        }

        if (!isset($this->endpoints[$this->provider])) {
            throw new \Exception('Proveedor no soportado: ' . $this->provider);
        }

        $config = $this->endpoints[$this->provider];

        switch ($this->provider) {
            case 'openai':
                return $this->generateOpenAIResponse($config, $messages, $context);
            case 'anthropic':
                return $this->generateAnthropicResponse($config, $messages, $context);
            case 'google':
                return $this->generateGoogleResponse($config, $messages, $context);
            default:
                throw new \Exception('Método de generación no implementado para: ' . $this->provider);
        }
    }

    private function generateOpenAIResponse($config, $messages, $context)
    {
        $settings = get_option('tutor_ai_settings', []);
        
        // Usar el formato de respuesta estructurada recomendado por OpenAI
        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => floatval($settings['temperature'] ?? 0.7),
            'max_tokens' => intval($settings['max_tokens'] ?? 1000),
            'response_format' => [
                'type' => 'text'
            ]
        ];

        // Si el contexto requiere respuesta estructurada (para recomendaciones)
        if (isset($context['structured']) && $context['structured']) {
            $payload['response_format'] = [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'course_recommendation',
                    'strict' => true,
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'recommendations' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'course_title' => ['type' => 'string'],
                                        'reason' => ['type' => 'string'],
                                        'relevance_score' => ['type' => 'number', 'minimum' => 0, 'maximum' => 10]
                                    ],
                                    'required' => ['course_title', 'reason', 'relevance_score'],
                                    'additionalProperties' => false
                                ]
                            ],
                            'explanation' => ['type' => 'string']
                        ],
                        'required' => ['recommendations', 'explanation'],
                        'additionalProperties' => false
                    ]
                ]
            ];
        }

        $response = $this->client->post(
            $config['base_url'] . $config['chat_endpoint'],
            [
                'headers' => $config['headers'],
                'json' => $payload
            ]
        );

        $data = json_decode($response->getBody(), true);
        
        // Validar la respuesta según el nuevo formato
        if (!isset($data['choices'][0]['message']['content'])) {
            throw new \Exception('Formato de respuesta inválido de OpenAI');
        }

        $content = $data['choices'][0]['message']['content'];
        
        // Si es respuesta estructurada, validar JSON
        if (isset($context['structured']) && $context['structured']) {
            $decoded = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Respuesta JSON inválida de OpenAI');
            }
            return $decoded;
        }

        return $content;
    }

    private function generateAnthropicResponse($config, $messages, $context)
    {
        $settings = get_option('tutor_ai_settings', []);
        
        // Convertir formato OpenAI a Anthropic
        $anthropicMessages = [];
        foreach ($messages as $message) {
            if ($message['role'] !== 'system') {
                $anthropicMessages[] = [
                    'role' => $message['role'] === 'assistant' ? 'assistant' : 'user',
                    'content' => $message['content']
                ];
            }
        }

        $systemMessage = '';
        foreach ($messages as $message) {
            if ($message['role'] === 'system') {
                $systemMessage = $message['content'];
                break;
            }
        }

        $payload = [
            'model' => $this->model,
            'messages' => $anthropicMessages,
            'max_tokens' => intval($settings['max_tokens'] ?? 1000)
        ];

        if ($systemMessage) {
            $payload['system'] = $systemMessage;
        }

        $response = $this->client->post(
            $config['base_url'] . $config['chat_endpoint'],
            [
                'headers' => $config['headers'],
                'json' => $payload
            ]
        );

        $data = json_decode($response->getBody(), true);
        return $data['content'][0]['text'] ?? '';
    }

    private function generateGoogleResponse($config, $messages, $context)
    {
        $url = $config['base_url'] . $config['chat_endpoint'] . '?key=' . $this->api_key;
        
        // Convertir mensajes al formato de Google
        $contents = [];
        foreach ($messages as $message) {
            if ($message['role'] !== 'system') {
                $contents[] = [
                    'role' => $message['role'] === 'assistant' ? 'model' : 'user',
                    'parts' => [['text' => $message['content']]]
                ];
            }
        }

        $response = $this->client->post(
            $url,
            [
                'headers' => $config['headers'],
                'json' => [
                    'contents' => $contents
                ]
            ]
        );

        $data = json_decode($response->getBody(), true);
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

    public function get_course_recommendations($user_id, $limit = 5)
    {
        $recommender = new Recommender();
        $user_profile = $recommender->get_user_profile($user_id);
        $available_courses = $recommender->get_available_courses($user_id);

        if (empty($available_courses)) {
            return [];
        }

        $prompt = $this->build_recommendation_prompt($user_profile, $available_courses);
        
        $messages = [
            [
                'role' => 'system',
                'content' => 'Eres un asistente educativo especializado en recomendar cursos. Analiza el perfil del estudiante y recomienda los mejores cursos basándote en su experiencia, intereses y progreso actual. Responde SOLO con el JSON solicitado, sin texto adicional.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ];

        try {
            // Usar respuesta estructurada para recomendaciones
            $context = ['structured' => true];
            $response = $this->generate_response($messages, $context);
            
            if (is_array($response) && isset($response['recommendations'])) {
                return array_slice($response['recommendations'], 0, $limit);
            }
            
            return [];
        } catch (\Exception $e) {
            error_log('Error generating recommendations: ' . $e->getMessage());
            return [];
        }
    }

    private function build_recommendation_prompt($user_profile, $courses)
    {
        $prompt = "Perfil del estudiante:\n";
        $prompt .= "- Cursos completados: " . count($user_profile['completed_courses']) . "\n";
        $prompt .= "- Áreas de interés: " . implode(', ', $user_profile['interests']) . "\n";
        $prompt .= "- Nivel de experiencia: " . $user_profile['experience_level'] . "\n\n";
        
        $prompt .= "Cursos disponibles:\n";
        foreach ($courses as $course) {
            $prompt .= "- {$course['title']} (Categoría: {$course['category']}, Nivel: {$course['level']})\n";
        }
        
        $prompt .= "\nGenera un JSON con recomendaciones siguiendo exactamente este formato:\n";
        $prompt .= "{\n";
        $prompt .= '  "recommendations": [' . "\n";
        $prompt .= '    {' . "\n";
        $prompt .= '      "course_title": "Nombre exacto del curso",' . "\n";
        $prompt .= '      "reason": "Explicación breve de por qué es adecuado",' . "\n";
        $prompt .= '      "relevance_score": 8.5' . "\n";
        $prompt .= '    }' . "\n";
        $prompt .= '  ],' . "\n";
        $prompt .= '  "explanation": "Resumen general de las recomendaciones"' . "\n";
        $prompt .= "}\n\n";
        $prompt .= "Recomienda máximo 5 cursos ordenados por relevancia.";
        
        return $prompt;
    }
}
