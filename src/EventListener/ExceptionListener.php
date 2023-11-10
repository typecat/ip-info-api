<?php
namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class ExceptionListener
{
    private bool $debug;

    public function __construct(KernelInterface $kernel)
    {
        $this->debug = $kernel->isDebug();
    }

    public function __invoke(ExceptionEvent $event): void
    {
        // You get the exception object from the received event
        $exception = $event->getThrowable();

        // Customize your response object to display the exception details
        $response = new Response();
        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details
        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $responseData = [
            'status' => $response->getStatusCode(),
            'message' => $this->debug ? $exception->getMessage() : 'An error occurred',
            'trace' => $this->debug ? $exception->getTrace() : 'Enable debug mode for details.',
        ];
        $response->setContent(json_encode($responseData, JSON_THROW_ON_ERROR));

        // sends the modified response object to the event
        $event->setResponse($response);
    }
}
