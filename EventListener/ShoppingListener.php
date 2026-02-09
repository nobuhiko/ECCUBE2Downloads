<?php

namespace Plugin\ECCUBE2Downloads\EventListener;

use Eccube\Event\TemplateEvent;
use Eccube\Service\CartService;
use Plugin\ECCUBE2Downloads\PluginManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ShoppingListener implements EventSubscriberInterface
{
    /**
     * @var CartService
     */
    private $cartService;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(
        CartService $cartService,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->cartService = $cartService;
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
            'Shopping/login.twig' => 'onShoppingLogin',
        ];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        if ($route !== 'shopping_nonmember') {
            return;
        }

        if ($this->hasDownloadProduct()) {
            $event->setResponse(new RedirectResponse($this->urlGenerator->generate('shopping_login')));
        }
    }

    public function onShoppingLogin(TemplateEvent $event)
    {
        if ($this->hasDownloadProduct()) {
            $event->addSnippet('@ECCUBE2Downloads/Shopping/login_download_notice.twig');
        }
    }

    private function hasDownloadProduct(): bool
    {
        $Carts = $this->cartService->getCarts();
        foreach ($Carts as $Cart) {
            foreach ($Cart->getCartItems() as $CartItem) {
                $ProductClass = $CartItem->getProductClass();
                if ($ProductClass && $ProductClass->getSaleType() && $ProductClass->getSaleType()->getId() === PluginManager::SALE_TYPE_ID) {
                    return true;
                }
            }
        }

        return false;
    }
}
