<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class PdfReportService
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Génère un rapport PDF à partir des statistiques
     */
    public function generateStatisticsPdf(array $data, string $template): Response
    {
        try {
            // Configure Dompdf
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', true);
            $options->set('defaultFont', 'Arial');

            $dompdf = new Dompdf($options);

            // Generate HTML content for the PDF
            $html = $this->twig->render($template, array_merge(
                $data,
                ['date' => new \DateTime()]
            ));

            // Load HTML to Dompdf
            $dompdf->loadHtml($html);

            // Set paper size and orientation
            $dompdf->setPaper('A4', 'portrait');

            // Render the HTML as PDF
            $dompdf->render();

            // Generate the PDF file name
            $filename = sprintf('statistiques_rh_%s.pdf', date('Y-m-d_H-i-s'));

            // Output the generated PDF
            return new Response(
                $dompdf->output(),
                Response::HTTP_OK,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ]
            );
        } catch (\Exception $e) {
            throw new \Exception('Erreur lors de la génération du PDF: ' . $e->getMessage());
        }
    }
} 