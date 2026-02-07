<?php

namespace Plugin\ECCUBE2Downloads\EventListener;

use Eccube\Event\TemplateEvent;
use Plugin\ECCUBE2Downloads\Repository\ConfigRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MypageHistoryListener implements EventSubscriberInterface
{
    /**
     * @var ConfigRepository
     */
    protected $configRepository;

    public function __construct(ConfigRepository $configRepository)
    {
        $this->configRepository = $configRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Mypage/history.twig' => 'onMypageHistory',
        ];
    }

    public function onMypageHistory(TemplateEvent $event)
    {
        $Config = $this->configRepository->get();
        $event->setParameter('eccube2downloads_config', $Config);
        $event->addSnippet('@ECCUBE2Downloads/Mypage/download_link.twig');
    }
}
