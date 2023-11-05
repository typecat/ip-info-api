<?php
/**
 * Copyright notice:
 * This file is part of a private project.
 */

namespace App\Controller;

use App\Model\IpDataInterface;
use App\Service\IpInfoService;
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
     * @param IpInfoService $ipInfoService
     */
    public function __construct(private IpInfoService $ipInfoService)
    {
    }

    /**
     * @return Response
     */
    #[Route('/geolocation', name: 'geolocation_main')]
    public function index(): Response
    {
        // TODO: describe how to use the API in a nice and readable way (e.g. swagger)
        return new Response(
            '<html><body>' .
            '<h1>API for requesting geolocation information.</h1>' .
            '<h2>How to use</h2>' .
            '<p>Request structure:</p>' .
            '<p>GET https://<b>{host}</b>/api/ip/<b>{ip}</b><br>Accept: application/json<br>X-Auth-Token: <b>{auth-token}</b></p>' .
            '</body></html>'
        );
    }

    /**
     * @param string $ip
     *
     * @return JsonResponse
     */
    #[Route('/ip/{ip}', name: 'get_ip_geolocation', requirements: ['ip' => '(\d{1,3}\.){3}\d{1,3}'], methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED")]
    public function requestGeolocationOfIp(string $ip): JsonResponse
    {
        /**@var IpDataInterface $info */
        $info = $this->ipInfoService->getGeoInformation($ip);

        return $this->json($info->toArray());
    }
}
