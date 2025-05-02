<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    private $apiKey;
    private $httpClient;

    public function __construct(string $apiKey = 'd18bf41c02057a05611396c8d6951f4d', HttpClientInterface $httpClient = null)
    {
        $this->apiKey = $apiKey;
        $this->httpClient = $httpClient ?? HttpClient::create();
    }

    public function getCurrentWeather(string $location): array
    {
        try {
            $response = $this->httpClient->request('GET', 'https://api.openweathermap.org/data/2.5/weather', [
                'query' => [
                    'q' => $location,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                    'lang' => 'fr',
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('Failed to fetch weather data: ' . $response->getContent(false));
            }

            $data = $response->toArray();

            return [
                'success' => true,
                'temperature' => round($data['main']['temp']),
                'description' => ucfirst($data['weather'][0]['description']),
                'icon' => $data['weather'][0]['icon'],
                'humidity' => $data['main']['humidity'],
                'wind_speed' => $data['wind']['speed'],
                'city' => $data['name'],
                'icon_url' => 'https://openweathermap.org/img/wn/' . $data['weather'][0]['icon'] . '@2x.png',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
} 