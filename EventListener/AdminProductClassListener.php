<?php

namespace Plugin\ECCUBE2Downloads\EventListener;

use Eccube\Event\TemplateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AdminProductClassListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            '@admin/Product/product_class.twig' => 'onAdminProductClass',
            '@admin/Product/product.twig' => 'onAdminProduct',
        ];
    }

    public function onAdminProductClass(TemplateEvent $event)
    {
        $event->addSnippet('@ECCUBE2Downloads/admin/product_class_file_upload.twig');
    }

    public function onAdminProduct(TemplateEvent $event)
    {
        $event->addSnippet('@ECCUBE2Downloads/admin/product_class_file_upload.twig');
    }
}
