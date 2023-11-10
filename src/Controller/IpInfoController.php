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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Controller for geolocation API
 */
#[Route("/api", "api_", format: 'json')]
class IpInfoController extends AbstractController
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
    #[Route('/', name: 'index', format: 'html')]
    public function index(): Response
    {
        // TODO: describe how to use the API in a nice and readable way (e.g. swagger)
        return new Response(
            '<html><body>' .
            '<h1>API for requesting information of an IP address</h1>' .
            '<h2>How to use</h2>' .
            '<p>Request geolocation:</p>' .
            '<p>GET https://<b>{host}</b>/api/geolocation/<b>{ip}</b><br>Accept: application/json<br>X-Auth-Token: <b>{auth-token}</b></p>' .
            '</body></html>'
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route('/geolocation', name: 'get_ip_geolocation', methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED")]
    public function requestGeolocationOfIp(Request $request): JsonResponse
    {
        try {
            $requestBody = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $jsonException) {
            throw new BadRequestHttpException('Invalid request body: ' . $jsonException->getMessage());
        }

        if (isset($requestBody["ip"])) {
            $errors = $this->ipInfoService->validateRequestedIp($requestBody["ip"]);
            $errorMessage = '';
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $errorMessage .= $error->getPropertyPath() . ': ' . $error->getMessage() . '; ';
                }
                throw new BadRequestHttpException('Request body invalid. ' . count($errors) . ' errors found: ' . $errorMessage);
            }

        } else {
            throw new BadRequestHttpException('No IP was provided. Please provide the IP in the request body.');
        }

        /**@var IpDataInterface $info */
        $info = $this->ipInfoService->getGeoInformation($requestBody["ip"]);
        return $this->json($info->toArray());
    }
}
