<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Validation object for requested IPs
 */
class IpRequestObject
{
    /**
     * @var string
     */
    private string $ip;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraint('ip', new NotBlank());
        $metadata->addPropertyConstraint('ip', new Regex(['pattern' => '/(\d{1,3}\.){3}\d{1,3}/']));
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     *
     * @return void
     */
    public function setIp(string $ip): void
    {
        $this->ip = $ip;
    }
}
