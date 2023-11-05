<?php
/**
 * Copyright notice:
 * This file is part of a private project.
 */

namespace App\Service;

class GeolocationService
{
    /**
     * @param string $ip
     *
     * @return string[]
     */
    public function getCityByIp(string $ip): array
    {
        //TODO: Connect to geolocation service
        // Use https://packagist.org/packages/ipinfo/ipinfo
        // Retrieve information
        $information = [
            'ip' => $ip,
            'city' => 'test',
            'country' => 'test'
        ];

        return $information;
    }
}
