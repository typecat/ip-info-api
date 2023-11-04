<?php
/**
 * Copyright notice:
 * This file is part of a private project.
 */

namespace App\Service;

class GeolocationService
{
    public function getCityByIp(string $ip): array
    {
        //TODO: Connect to geolocation service
        // Use https://packagist.org/packages/ipinfo/ipinfo
        // Retrieve information
        $information = [
            'ip' => $ip,
            'city' => 'test',
            'state' => 'test'
        ];

        return $information;
    }
}