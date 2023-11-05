<?php

namespace App\Model;

interface IpDataInterface
{
    public function getIp(): string;

    public function setIp(string $ip): void;

    public function toArray(): array;
}
