<?php

namespace Plugin\ECCUBE2Downloads44\EventListener;

use Eccube\Event\TemplateEvent;
use Plugin\ECCUBE2Downloads44\Repository\ConfigRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MypageHistoryListener implements EventSubscriberInterface
{
    public function __construct(private readonly ConfigRepository $configRepository)
    {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            'Mypage/history.twig' => 'onMypageHistory',
        ];
    }

    public function onMypageHistory(TemplateEvent $event): void
    {
        $Config = $this->configRepository->get();
        $event->setParameter('eccube2downloads44_config', $Config);

        $source = $event->getSource();
        $insert = "{% include '@ECCUBE2Downloads44/Mypage/download_link.twig' %}";
        $source = str_replace('{% endblock %}', $insert."\n{% endblock %}", $source);
        $event->setSource($source);
    }
}
