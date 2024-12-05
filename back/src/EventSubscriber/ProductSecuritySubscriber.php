<?php

namespace App\EventSubscriber;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ProductSecuritySubscriber implements EventSubscriberInterface
{
    private const ADMIN_EMAIL = 'admin@admin.com';
    private const PROTECTED_METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function __construct(
        private Security $security
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api/products')) {
            return;
        }

        if (!in_array($request->getMethod(), self::PROTECTED_METHODS)) {
            return;
        }

        $user = $this->security->getUser();

        if (!$user || $user->getUserIdentifier() !== self::ADMIN_EMAIL) {
            throw new AccessDeniedHttpException('Seul admin@admin.com peut creer ou modifier, supprimer les produits.');
        }
    }
}