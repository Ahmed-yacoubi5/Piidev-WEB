<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CvParserService
{
    private $client;
    private $apiKey;
    private $apiUrl;

    public function __construct(ParameterBagInterface $params)
    {
        $this->client = HttpClient::create();
        $this->apiKey = $params->get('app.affinda_api_key');
        $this->apiUrl = 'https://api.affinda.com/v1/resumes';
    }

    public function parseCv(UploadedFile $file): array
    {
        try {
            // Préparer le fichier pour l'envoi
            $fileContent = file_get_contents($file->getPathname());
            $base64Content = base64_encode($fileContent);

            // Envoyer la requête à l'API
            $response = $this->client->request('POST', $this->apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'file' => $base64Content,
                    'file_name' => $file->getClientOriginalName(),
                ],
            ]);

            $data = json_decode($response->getContent(), true);

            // Extraire les informations pertinentes
            return [
                'nom' => $data['data']['name']['first'] ?? null,
                'prenom' => $data['data']['name']['last'] ?? null,
                'email' => $data['data']['emails'][0] ?? null,
                'telephone' => $data['data']['phone_numbers'][0] ?? null,
                'adresse' => $data['data']['location']['raw'] ?? null,
                'experience' => $this->formatExperience($data['data']['work_experience'] ?? []),
                'education' => $this->formatEducation($data['data']['education'] ?? []),
                'competences' => $data['data']['skills'] ?? [],
            ];
        } catch (\Exception $e) {
            throw new \Exception('Erreur lors de l\'analyse du CV: ' . $e->getMessage());
        }
    }

    private function formatExperience(array $experiences): array
    {
        return array_map(function($exp) {
            return [
                'poste' => $exp['job_title'] ?? '',
                'entreprise' => $exp['organization'] ?? '',
                'date_debut' => $exp['dates']['start_date'] ?? null,
                'date_fin' => $exp['dates']['end_date'] ?? null,
                'description' => $exp['job_description'] ?? '',
            ];
        }, $experiences);
    }

    private function formatEducation(array $education): array
    {
        return array_map(function($edu) {
            return [
                'diplome' => $edu['accreditation']['education'] ?? '',
                'etablissement' => $edu['organization'] ?? '',
                'date_obtention' => $edu['dates']['completion_date'] ?? null,
            ];
        }, $education);
    }
} 