<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('random_color', [$this, 'randomColor']),
        ];
    }

    /**
     * Generate a random color based on a seed
     */
    public function randomColor(string $base, int $seed = 0): string
    {
        // Use base and seed to generate a consistent color
        $hash = md5($base . $seed);
        
        // Get 6 characters from the hash for the color
        $color = substr($hash, 0, 6);
        
        return $color;
    }
} 