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
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Controller for geolocation API
 */
#[Route("/api", "api_")]
class GeolocationController extends AbstractController
{
    /**
     * @param GeolocationService $geolocationService
     */
    public function __construct(private GeolocationService $geolocationService)
    {
    }

    /**
     * @return Response
     */
    #[Route('/geolocation', name: 'geolocation_main')]
    public function index(): Response
    {
        return new Response(
            '<html><body><p>Request geolocation information of an IP.</p></body></html>'
        );
    }

    /**
     * @param string $ip
     *
     * @return JsonResponse
     */
    #[Route('/geolocation/{ip}', name: 'get_ip_geolocation', requirements: ['ip' => '(\d{3}\.){3}\d{3}'], methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED")]
    public function requestGeolocationOfIp(string $ip): JsonResponse
    {
        $info = $this->geolocationService->getCityByIp($ip);

        return $this->json($info);
    }
}
