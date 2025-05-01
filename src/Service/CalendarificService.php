<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Psr\Log\LoggerInterface;

class CalendarificService
{
    private const BASE_URL = 'https://calendarific.com/api/v2/holidays';
    private string $apiKey;
    private HttpClientInterface $client;
    private ?LoggerInterface $logger;

    public function __construct(
        HttpClientInterface $client, 
        string $apiKey = 'oTDlkbDtYwM5pEzTXqDh3BuKEXIMrEy6',
        LoggerInterface $logger = null
    ) {
        $this->client = $client;
        $this->apiKey = $apiKey;
        $this->logger = $logger;
    }

    public function getHolidays(string $country = 'FR', int $year = null): array
    {
        // Use current year if none specified
        if ($year === null) {
            $year = (int)(new \DateTime())->format('Y');
        }
        
        try {
            $this->logInfo("Requesting holidays for {$country} in {$year}");
            
            $response = $this->client->request('GET', self::BASE_URL, [
                'query' => [
                    'api_key' => $this->apiKey,
                    'country' => $country,
                    'year' => $year,
                    'language' => 'fr'
                ],
                'timeout' => 5.0, // Add timeout to prevent long waits
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                $this->logError("API Calendarific a retourné le code {$statusCode}");
                return $this->getFallbackHolidays($country, $year);
            }

            $data = $response->toArray();
            
            // Check that response contains the required structure
            if (!isset($data['response']) || !isset($data['response']['holidays']) || !is_array($data['response']['holidays'])) {
                $this->logWarning("Structure de réponse invalide pour {$country} en {$year}");
                return $this->getFallbackHolidays($country, $year);
            }
            
            $holidays = $data['response']['holidays'];
            
            // Verify that holidays contain required fields
            foreach ($holidays as $index => $holiday) {
                if (!isset($holiday['name']) || !isset($holiday['date']) || !isset($holiday['date']['iso'])) {
                    $this->logWarning("Données de vacances incomplètes à l'index {$index}");
                    unset($holidays[$index]);
                }
            }
            
            if (empty($holidays)) {
                $this->logWarning("Aucun jour férié valide trouvé pour {$country} en {$year}");
                return $this->getFallbackHolidays($country, $year);
            }

            $this->logInfo("Successfully retrieved " . count($holidays) . " holidays");
            return array_values($holidays); // Reset array keys after possible filtering
            
        } catch (TransportExceptionInterface | ClientExceptionInterface | ServerExceptionInterface | RedirectionExceptionInterface $e) {
            $this->logError("Erreur HTTP lors de l'appel à l'API Calendarific: " . $e->getMessage());
            return $this->getFallbackHolidays($country, $year);
        } catch (\Exception $e) {
            $this->logError("Erreur générale: " . $e->getMessage());
            return $this->getFallbackHolidays($country, $year);
        }
    }
    
    /**
     * Retourne des données de jours fériés de secours en cas d'échec de l'API
     */
    private function getFallbackHolidays(string $country, int $year): array
    {
        $this->logInfo("Using fallback holidays for {$country} in {$year}");
        
        // Quelques jours fériés communs pour différents pays
        $fallbackData = [
            'FR' => [
                ['name' => 'Jour de l\'An', 'date' => ['iso' => "{$year}-01-01"], 'description' => 'Premier jour de l\'année'],
                ['name' => 'Fête du Travail', 'date' => ['iso' => "{$year}-05-01"], 'description' => 'Jour férié international'],
                ['name' => 'Fête Nationale', 'date' => ['iso' => "{$year}-07-14"], 'description' => 'Commémoration de la prise de la Bastille'],
                ['name' => 'Assomption', 'date' => ['iso' => "{$year}-08-15"], 'description' => 'Fête religieuse catholique'],
                ['name' => 'Toussaint', 'date' => ['iso' => "{$year}-11-01"], 'description' => 'Fête de tous les saints'],
                ['name' => 'Armistice', 'date' => ['iso' => "{$year}-11-11"], 'description' => 'Fin de la Première Guerre mondiale'],
                ['name' => 'Noël', 'date' => ['iso' => "{$year}-12-25"], 'description' => 'Fête chrétienne de la nativité']
            ],
            'DE' => [
                ['name' => 'Neujahr', 'date' => ['iso' => "{$year}-01-01"], 'description' => 'Premier jour de l\'année'],
                ['name' => 'Tag der Arbeit', 'date' => ['iso' => "{$year}-05-01"], 'description' => 'Jour férié international'],
                ['name' => 'Tag der Deutschen Einheit', 'date' => ['iso' => "{$year}-10-03"], 'description' => 'Jour de l\'Unité allemande'],
                ['name' => 'Weihnachten', 'date' => ['iso' => "{$year}-12-25"], 'description' => 'Noël']
            ],
            'US' => [
                ['name' => 'New Year\'s Day', 'date' => ['iso' => "{$year}-01-01"], 'description' => 'Premier jour de l\'année'],
                ['name' => 'Independence Day', 'date' => ['iso' => "{$year}-07-04"], 'description' => 'Jour de l\'Indépendance'],
                ['name' => 'Labor Day', 'date' => ['iso' => "{$year}-09-01"], 'description' => 'Fête du Travail (premier lundi de septembre)'],
                ['name' => 'Thanksgiving Day', 'date' => ['iso' => "{$year}-11-28"], 'description' => 'Action de grâce'],
                ['name' => 'Christmas Day', 'date' => ['iso' => "{$year}-12-25"], 'description' => 'Noël']
            ],
            'IT' => [
                ['name' => 'Capodanno', 'date' => ['iso' => "{$year}-01-01"], 'description' => 'Premier jour de l\'année'],
                ['name' => 'Epifania', 'date' => ['iso' => "{$year}-01-06"], 'description' => 'Épiphanie'],
                ['name' => 'Festa della Liberazione', 'date' => ['iso' => "{$year}-04-25"], 'description' => 'Jour de la Libération'],
                ['name' => 'Festa della Repubblica', 'date' => ['iso' => "{$year}-06-02"], 'description' => 'Fête de la République'],
                ['name' => 'Ferragosto', 'date' => ['iso' => "{$year}-08-15"], 'description' => 'Assomption'],
                ['name' => 'Natale', 'date' => ['iso' => "{$year}-12-25"], 'description' => 'Noël']
            ],
            'MA' => [
                ['name' => 'Nouvel An', 'date' => ['iso' => "{$year}-01-01"], 'description' => 'Premier jour de l\'année'],
                ['name' => 'Manifeste de l\'Indépendance', 'date' => ['iso' => "{$year}-01-11"], 'description' => 'Présentation du Manifeste de l\'Indépendance'],
                ['name' => 'Fête du Travail', 'date' => ['iso' => "{$year}-05-01"], 'description' => 'Jour férié international'],
                ['name' => 'Fête du Trône', 'date' => ['iso' => "{$year}-07-30"], 'description' => 'Commémoration de l\'intronisation du roi'],
                ['name' => 'Fête de la Révolution', 'date' => ['iso' => "{$year}-08-20"], 'description' => 'Révolution du Roi et du Peuple'],
                ['name' => 'Fête de la Jeunesse', 'date' => ['iso' => "{$year}-08-21"], 'description' => 'Anniversaire du Roi Mohammed VI'],
                ['name' => 'Fête de l\'Indépendance', 'date' => ['iso' => "{$year}-11-18"], 'description' => 'Proclamation de l\'indépendance']
            ]
        ];
        
        return $fallbackData[$country] ?? $fallbackData['FR'];
    }
    
    /**
     * Log an info message if logger is available
     */
    private function logInfo(string $message): void
    {
        if ($this->logger) {
            $this->logger->info($message);
        }
    }
    
    /**
     * Log a warning message if logger is available
     */
    private function logWarning(string $message): void
    {
        if ($this->logger) {
            $this->logger->warning($message);
        }
    }
    
    /**
     * Log an error message if logger is available
     */
    private function logError(string $message): void
    {
        if ($this->logger) {
            $this->logger->error($message);
        }
    }
}