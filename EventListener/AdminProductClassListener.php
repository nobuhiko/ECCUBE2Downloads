<?php

namespace Plugin\ECCUBE2Downloads44\EventListener;

use Eccube\Event\TemplateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AdminProductClassListener implements EventSubscriberInterface
{
    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            '@admin/Product/product_class.twig' => 'onAdminProductClass',
            '@admin/Product/product.twig' => 'onAdminProduct',
        ];
    }

    public function onAdminProductClass(TemplateEvent $event): void
    {
        $event->addSnippet('@ECCUBE2Downloads44/admin/product_class_file_upload.twig');
    }

    public function onAdminProduct(TemplateEvent $event): void
    {
        $event->addSnippet('@ECCUBE2Downloads44/admin/product_class_file_upload.twig');
    }
}
