<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Twig\Environment;

class ExceptionSubscriber implements EventSubscriberInterface
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Code HTTP
        $statusCode = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;

        // Redirection automatique pour 500 (optionnelle)
        if ($statusCode === 500) {
            // Pour la prod tu peux décommenter la ligne suivante si tu veux auto-rediriger
            // $event->setResponse(new RedirectResponse('/profile'));
            // return;
        }

        // Template Twig
        $template = sprintf('bundles/TwigBundle/Exception/error%s.html.twig', $statusCode);
        if (!$this->twig->getLoader()->exists($template)) {
            $template = 'bundles/TwigBundle/Exception/error.html.twig';
        }

        $content = $this->twig->render($template, [
            'status_code' => $statusCode,
            'message' => $exception->getMessage(),
        ]);

        $response = new Response($content, $statusCode);
        $event->setResponse($response);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
