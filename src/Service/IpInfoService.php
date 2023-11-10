<?php
/**
 * Copyright notice:
 * This file is part of a private project.
 */

namespace App\Service;

use App\Converter\SypexGeoConverter;
use App\Entity\IpRequestObject;
use App\Model\IpGeoDataDTO;
use HostBrook\SypexGeo\SypexGeo;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Uses https://packagist.org/packages/ipinfo/ipinfo to retrieve
 */
class IpInfoService
{
    /**
     * @var SypexGeo
     */
    protected SypexGeo $geoReader;
    public function __construct(private ValidatorInterface $validator)
    {
        $this->geoReader = new SypexGeo('SxGeoCity.dat');
    }

    public function validateRequestedIp(mixed $ip): ConstraintViolationListInterface
    {
        $requestedIp = new IpRequestObject();
        $requestedIp->setIp($ip);

        return $this->validator->validate($requestedIp);
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
