<?php
/**
 * Copyright notice:
 * This file is part of a private project.
 */

namespace App\Controller;

use App\Service\GeolocationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/api", "api_")]
class GeolocationController extends AbstractController
{
    /**
     * @param GeolocationService $geolocationService
     */
    public function __construct(private GeolocationService $geolocationService)
    {
    }

    #[Route('/geolocation', name: 'geolocation_main')]
    public function index(): Response
    {
        return new Response(
            '<html><body><p>Request geolocation information of an IP.</p></body></html>'
        );
    }

    #[Route('/geolocation/{ip}', name: 'get_ip_geolocation', requirements: ['ip' => '(\d{3}\.){3}\d{3}'], methods: ["GET"])]
    public function requestGeolocationOfIp(string $ip): JsonResponse
    {
        // TODO: User futhentication


        $info = $this->geolocationService->getCityByIp($ip);

        return $this->json($info);
    }
}