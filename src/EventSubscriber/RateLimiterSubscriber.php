<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class RateLimiterSubscriber implements EventSubscriberInterface
{
    public function __construct(private RateLimiterFactory $authenticatedApiLimiter)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void {
        $request = $event->getRequest();

        // Apply to all api routes except index
        if($request->get("_route") !== 'api_index' && str_contains($request->get("_route"), 'api_')) {
            $limiter = $this->authenticatedApiLimiter->create($request->getClientIp());
            $limit = $limiter->consume();

            //if (false === $limiter->consume(1)->isAccepted()) {
            if (false === $limit->isAccepted()) {
                //throw new TooManyRequestsHttpException();
                $headers = [
                    'X-RateLimit-Remaining' => $limit->getRemainingTokens(),
                    'X-RateLimit-Retry-After' => $limit->getRetryAfter()->getTimestamp(),
                    'X-RateLimit-Limit' => $limit->getLimit(),
                ];
                $response = new Response(null, Response::HTTP_TOO_MANY_REQUESTS, $headers);
                $event->setResponse($response);
            }
        }
    }
}
