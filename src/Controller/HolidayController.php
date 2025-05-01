<?php

namespace App\Controller;

use App\Service\CalendarificService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class HolidayController extends AbstractController
{
    #[Route('/calendar', name: 'calendar_view')]
    public function calendar(Request $request, CalendarificService $calendarificService, LoggerInterface $logger): Response
    {
        try {
            // Get selected country from query, defaulting to FR
            $selectedCountry = $request->query->get('country', 'FR');
            
            // Validate country code (only allow those we have data for)
            $validCountries = ['FR', 'MA', 'US', 'DE', 'IT'];
            if (!in_array($selectedCountry, $validCountries)) {
                $logger->warning("Invalid country code requested: {$selectedCountry}");
                $selectedCountry = 'FR'; // Default to France if invalid
            }
            
            // Get current year
            $year = (int)(new \DateTime())->format('Y');
            
            // Get holidays for the selected country and year
            $holidays = $calendarificService->getHolidays($selectedCountry, $year);
            
            // Log the number of holidays retrieved for debugging
            $logger->info("Retrieved " . count($holidays) . " holidays for {$selectedCountry} in {$year}");
            
            // Additional debugging for the first holiday to verify structure
            if (count($holidays) > 0) {
                $firstHoliday = $holidays[0];
                $logger->debug("First holiday structure: " . json_encode($firstHoliday));
                
                // Ensure all holidays have the correct structure
                foreach ($holidays as $index => $holiday) {
                    // Make sure 'date' is properly structured
                    if (isset($holiday['date']) && !isset($holiday['date']['iso']) && is_string($holiday['date'])) {
                        $holidays[$index]['date'] = ['iso' => $holiday['date']];
                        $logger->debug("Fixed date structure for holiday at index {$index}");
                    }
                }
            }
            
            // Return the rendered template with the data
            return $this->render('holiday/calendar.html.twig', [
                'selected_country' => $selectedCountry,
                'holidays' => $holidays,
                'year' => $year,
                'debug' => true
            ]);
            
        } catch (\Exception $e) {
            // Log any uncaught exceptions
            $logger->error("Error in calendar view: " . $e->getMessage());
            $logger->error("Exception trace: " . $e->getTraceAsString());
            
            // Provide fallback holidays for error cases
            $year = (int)(new \DateTime())->format('Y');
            $fallbackHolidays = [
                [
                    'name' => 'Jour de l\'An',
                    'date' => ['iso' => "{$year}-01-01"],
                    'description' => 'Premier jour de l\'année'
                ],
                [
                    'name' => 'Fête du Travail',
                    'date' => ['iso' => "{$year}-05-01"],
                    'description' => 'Jour férié international'
                ]
            ];
            
            // Provide a user-friendly error message
            $this->addFlash('error', 'Une erreur est survenue lors du chargement du calendrier: ' . $e->getMessage());
            
            // Render the template with fallback data
            return $this->render('holiday/calendar.html.twig', [
                'selected_country' => $request->query->get('country', 'FR'),
                'holidays' => $fallbackHolidays,
                'year' => $year,
                'error' => true
            ]);
        }
    }
}