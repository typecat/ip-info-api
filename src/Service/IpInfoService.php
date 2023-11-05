<?php
/**
 * Copyright notice:
 * This file is part of a private project.
 */

namespace App\Service;

use App\Converter\SypexGeoConverter;
use App\Model\IpGeoDataDTO;
use HostBrook\SypexGeo\SypexGeo;

/**
 * Uses https://packagist.org/packages/ipinfo/ipinfo to retrieve
 */
class IpInfoService
{
    /**
     * @var SypexGeo
     */
    protected SypexGeo $geoReader;
    public function __construct()
    {
        $this->geoReader = new SypexGeo('SxGeoCity.dat');
    }

    private function toDto(string $ip, $geoData): IpGeoDataDTO
    {
        $ipGeoData = new IpGeoDataDTO($ip);
        if (is_array($geoData['city'])) {
            $ipGeoData->setCity($geoData['city']['name_en'] ?? '');
            $ipGeoData->setCountry($geoData['country']['iso'] ?? '');
        }

        return $ipGeoData;
    }

    /**
     * @param string $ip
     *
     * @return IpGeoDataDTO
     */
    public function getGeoInformation(string $ip): IpGeoDataDTO
    {
        return $this->toDto($ip, $this->geoReader->getCity($ip));
    }
}
