<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ChatbotController extends AbstractController
{
    private $httpClient;
    private $apiKey;
    
    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->apiKey ="AIzaSyCGvIXkpsIwFejR_3h9W_aqz20WFaVqwzc";
    }

    #[Route('/chatbot', name: 'app_chatbot')]
    public function index(): Response
    {
        return $this->render('chatbot/index.html.twig');
    }
    
    #[Route('/chatbot/send', name: 'app_chatbot_send', methods: ['POST'])]
    public function sendMessage(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userMessage = $data['message'] ?? '';
        
        if (empty($userMessage)) {
            return new JsonResponse(['error' => 'Message cannot be empty'], 400);
        }
        
        if (!$this->apiKey) {
            return new JsonResponse(['error' => 'API key not configured'], 500);
        }
        
        try {
            $response = $this->callGeminiApi($userMessage);
            return new JsonResponse(['response' => $response]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    private function callGeminiApi(string $message): string
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro-latest:generateContent';
        
        try {
            $response = $this->httpClient->request('POST', $url, [
                'query' => [
                    'key' => $this->apiKey
                ],
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $message]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 1024,
                    ]
                ]
            ]);
            
            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                throw new \Exception('API returned status code ' . $statusCode);
            }
            
            $data = $response->toArray();
            
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                return $data['candidates'][0]['content']['parts'][0]['text'];
            } else {
                throw new \Exception('Unexpected API response format');
            }
        } catch (\Exception $e) {
            throw new \Exception('Error calling Gemini API: ' . $e->getMessage());
        }
    }
}
