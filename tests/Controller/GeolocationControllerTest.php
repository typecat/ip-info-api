<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GeolocationControllerTest extends WebTestCase
{
    private UserRepository $userRepository;
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $this->userRepository = $entityManager->getRepository(User::class);
    }
    public function testEntypoint(): void
    {
        $crawler = $this->client->request('GET', '/api/geolocation');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'API for requesting geolocation information');
    }
    public function testApiRequestNoAuthentication(): void
    {
        $this->client->request('GET', '/api/geolocation/127.0.0.1');
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
    public function testApiRequestInvalidAuthentication(): void
    {
        $this->client->request(
            'GET',
            '/api/geolocation/127.0.0.1',
            server: [
                "HTTP_X_AUTH_TOKEN" => 'invalidtoken'
            ],
        );
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
    public function testApiRequestInvalidRequestData(): void
    {
        $user = $this->userRepository->findOneBy([]);
        $this->client->request(
            'GET',
            '/api/geolocation/invalidip',
            server: [
                "HTTP_X_AUTH_TOKEN" => $user->getToken()
            ],
        );
      self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    private function testGeolocationFormat($response): void
    {
        $expectedKeys = ['ip', 'country', 'city'];
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $response);
        }
    }
    public function testApiRequestSuccess(): void
    {
        $user = $this->userRepository->findOneBy([]);
        $this->client->request(
            'GET',
            '/api/geolocation/127.0.0.1',
            server: [
                "HTTP_X_AUTH_TOKEN" => $user->getToken()
            ],
        );

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertResponseFormatSame("json");

        $response = $this->client->getResponse();
        $result = json_decode($response->getContent(), true);
        $this->testGeolocationFormat($result);
    }
}
