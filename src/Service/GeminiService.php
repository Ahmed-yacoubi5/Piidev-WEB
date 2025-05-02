<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class GeminiService
{
    private $apiKey;
    private $httpClient;
    private $logger;

    public function __construct(string $apiKey, HttpClientInterface $httpClient, LoggerInterface $logger = null)
    {
        $this->apiKey = $apiKey;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    /**
     * Génère un rapport d'expert RH à partir des statistiques fournies.
     * @param array $stats Données statistiques à analyser
     * @return string Rapport généré par l'IA
     */
    public function generateExpertReport(array $stats): string
    {
        try {
            $prompt = $this->buildPrompt($stats);
            
            if ($this->logger) {
                $this->logger->info('Envoi de la requête à Gemini API');
            }
            
            // URL correcte pour l'API Gemini
            $url = "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-pro:generateContent?key=" . urlencode($this->apiKey);
            
            // Payload au format attendu par l'API
            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 2048
                ]
            ];
            
            if ($this->logger) {
                $this->logger->debug('URL API: ' . preg_replace('/key=([^&]+)/', 'key=***', $url));
                $this->logger->debug('Taille du payload: ' . strlen(json_encode($payload)) . ' caractères');
            }
            
            $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode($payload),
                'timeout' => 60,
            ]);
            
            $statusCode = $response->getStatusCode();
            
            if ($statusCode !== 200) {
                if ($this->logger) {
                    $this->logger->error('Erreur HTTP ' . $statusCode);
                    $this->logger->error('Contenu de l\'erreur: ' . $response->getContent(false));
                }
                return 'Erreur HTTP ' . $statusCode . ' lors de l\'appel à l\'API Gemini';
            }
            
            $content = $response->getContent();
            
            if ($this->logger) {
                $this->logger->debug('Réponse brute: ' . substr($content, 0, 500) . '...');
            }
            
            $data = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                if ($this->logger) {
                    $this->logger->error('Erreur JSON: ' . json_last_error_msg());
                }
                return 'Erreur lors du décodage de la réponse: ' . json_last_error_msg();
            }
            
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                $text = $data['candidates'][0]['content']['parts'][0]['text'];
                if ($this->logger) {
                    $this->logger->info('Rapport généré avec succès, longueur: ' . strlen($text) . ' caractères');
                }
                return $text;
            } elseif (isset($data['error'])) {
                if ($this->logger) {
                    $this->logger->error('Erreur API: ' . json_encode($data['error']));
                }
                return 'Erreur de l\'API Gemini: ' . ($data['error']['message'] ?? 'Erreur non spécifiée');
            } else {
                if ($this->logger) {
                    $this->logger->error('Format de réponse inattendu');
                }
                return 'Format de réponse inattendu. Consultez les logs du serveur.';
            }
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Exception: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
            }
            return 'Erreur technique lors de l\'appel à l\'API: ' . $e->getMessage();
        }
    }

    private function buildPrompt(array $stats): string
    {
        // Prompt simplifié pour réduire la taille des données
        return "En tant qu'expert RH avec plus de 20 ans d'expérience, analyse ces statistiques RH et fournis un rapport détaillé et professionnel:
" . json_encode($stats, JSON_UNESCAPED_UNICODE) . "
Le rapport doit inclure:
1. **ANALYSE DE LA SITUATION ACTUELLE** - Analyse de la répartition des effectifs, points forts/faibles de la structure actuelle, déséquilibres potentiels
2. **RISQUES ET OPPORTUNITÉS** - Risques RH à court/moyen terme, opportunités de développement, zones de tension
3. **RECOMMANDATIONS DÉTAILLÉES** - Au moins 5 recommandations précises et concrètes avec explications des bénéfices attendus
4. **PLAN D'ACTION PRIORITAIRE** - 3 actions prioritaires avec échéancier et indicateurs de performance à suivre

Utilise un ton professionnel mais accessible, avec des paragraphes courts et des titres structurés. Sois précis et factuel dans ton analyse.";
    }
} 