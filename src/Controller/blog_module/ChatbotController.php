<?php

namespace App\Controller\blog_module;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Offre;

#[Route('/blog')]
class ChatbotController extends AbstractController
{
    private $httpClient;
    private $apiKey;
    private $logger;
    private $entityManager;

    public function __construct(
        HttpClientInterface $httpClient, 
        ParameterBagInterface $params, 
        LoggerInterface $logger,
        EntityManagerInterface $entityManager
    ) {
        $this->httpClient = $httpClient;
        $this->apiKey = $params->get('gemini_api_key');
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    #[Route('/api/generate-content', name: 'api_generate_content', methods: ['POST'])]
    public function generateContent(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $prompt = $data['prompt'] ?? '';

        if (empty($prompt)) {
            return new JsonResponse(['error' => 'Le prompt ne peut pas être vide'], 400);
        }

        // Enrichir le prompt avec des informations sur le projet
        $enrichedPrompt = $this->enrichPromptWithContext($prompt);

        try {
            $response = $this->httpClient->request('POST', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'query' => [
                    'key' => $this->apiKey,
                ],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $enrichedPrompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 2048,
                    ]
                ]
            ]);

            $content = $response->toArray(false); // important: pas d'exception auto
            if (isset($content['error'])) {
                $this->logger->error('Erreur Gemini API: ' . json_encode($content['error']));
                return new JsonResponse(['error' => $content['error']['message'] ?? 'Erreur inconnue'], 500);
            }

            $generatedText = $content['candidates'][0]['content']['parts'][0]['text'] ?? '';
            return new JsonResponse(['content' => $generatedText]);

        } catch (\Exception $e) {
            $this->logger->error('Erreur interne Gemini: ' . $e->getMessage());
            return new JsonResponse([
                'error' => 'Erreur interne du serveur',
                'message' => $e->getMessage() // à commenter ou retirer en production
            ], 500);
        }
    }

    private function enrichPromptWithContext($userPrompt)
    {
        $promptLower = strtolower($userPrompt);
        $contextInfo = [];

        // Information de base sur Lumina RH
        $contextInfo[] = "Tu es un assistant virtuel pour Lumina RH, une plateforme complète de gestion des ressources humaines qui permet aux entreprises de gérer efficacement le recrutement, l'intégration, la paie, la formation et le développement des employés, avec une interface intuitive et des fonctionnalités avancées pour optimiser les processus RH.";

        // Si la question concerne les offres d'emploi, ajouter des informations spécifiques
        if (strpos($promptLower, 'offre') !== false || 
            strpos($promptLower, 'emploi') !== false || 
            strpos($promptLower, 'poste') !== false || 
            strpos($promptLower, 'recrutement') !== false) {
            
            // Description du module des offres
            $contextInfo[] = "Le module Offres de Lumina RH permet de créer, publier et gérer des offres d'emploi. Il facilite le processus de recrutement en automatisant la publication sur différentes plateformes et en centralisant la gestion des candidatures.";
            
            // Si la question concerne les champs/structure des offres
            if (strpos($promptLower, 'champ') !== false || 
                strpos($promptLower, 'information') !== false || 
                strpos($promptLower, 'structure') !== false ||
                strpos($promptLower, 'détail') !== false) {
                $offreInfo = $this->getOffreStructureInfo();
                $contextInfo[] = $offreInfo;
            }

            // Récupérer des statistiques sur les offres si demandé
            if (strpos($promptLower, 'nombre') !== false || 
                strpos($promptLower, 'statistique') !== false || 
                strpos($promptLower, 'combien') !== false) {
                $statsInfo = $this->getOffreStats();
                $contextInfo[] = $statsInfo;
            }
        }

        // Construire le prompt enrichi
        $systemInstruction = implode("\n\n", $contextInfo);
        $enrichedPrompt = $systemInstruction . "\n\nQuestion de l'utilisateur: " . $userPrompt . "\n\nRéponds de manière précise et professionnelle en utilisant les informations fournies.";
        
        return $enrichedPrompt;
    }

    private function getOffreStructureInfo(): string
    {
        try {
            // Récupérer la structure de l'entité Offre (métadonnées)
            $classMetadata = $this->entityManager->getClassMetadata(Offre::class);
            $fieldMappings = $classMetadata->fieldMappings;
            
            // Formatter les informations des champs
            $fieldDescriptions = [];
            foreach ($fieldMappings as $fieldName => $mapping) {
                $type = $mapping['type'] ?? 'inconnu';
                $fieldDescriptions[] = "- $fieldName (type: $type)";
            }
            
            // Récupérer également les relations
            $associations = [];
            foreach ($classMetadata->associationMappings as $fieldName => $mapping) {
                $targetEntity = isset($mapping['targetEntity']) ? 
                    (new \ReflectionClass($mapping['targetEntity']))->getShortName() : 'inconnu';
                $type = isset($mapping['type']) ? $this->getAssociationType($mapping['type']) : 'inconnu';
                $associations[] = "- $fieldName (relation $type avec $targetEntity)";
            }
            
            $result = "Structure d'une offre d'emploi dans Lumina RH:\n";
            $result .= "Champs: \n" . implode("\n", $fieldDescriptions) . "\n";
            
            if (!empty($associations)) {
                $result .= "\nRelations: \n" . implode("\n", $associations);
            }
            
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la récupération des métadonnées: ' . $e->getMessage());
            return "Les informations sur la structure des offres ne sont pas disponibles pour le moment.";
        }
    }
    
    private function getAssociationType($type): string
    {
        $types = [
            1 => 'one-to-one',
            2 => 'many-to-one',
            4 => 'one-to-many',
            8 => 'many-to-many'
        ];
        
        return $types[$type] ?? 'inconnu';
    }
    
    private function getOffreStats(): string
    {
        try {
            // Récupérer le nombre total d'offres
            $totalOffres = $this->entityManager->createQueryBuilder()
                ->select('COUNT(o.id)')
                ->from(Offre::class, 'o')
                ->getQuery()
                ->getSingleScalarResult();
            
            // Récupérer le nombre d'offres actives
            $activeOffres = $this->entityManager->createQueryBuilder()
                ->select('COUNT(o.id)')
                ->from(Offre::class, 'o')
                ->where('o.active = :active')
                ->setParameter('active', true)
                ->getQuery()
                ->getSingleScalarResult();
            
            return "Statistiques des offres d'emploi: il y a actuellement $totalOffres offres d'emploi dans la base de données, dont $activeOffres offres actives.";
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la récupération des statistiques: ' . $e->getMessage());
            return "Les statistiques des offres ne sont pas disponibles pour le moment.";
        }
    }
} 