<?php

declare(strict_types=1);

namespace MauticPlugin\EcommerceConnectorBundle\EventSubscriber;

use Mautic\PageBundle\Event\PageHitEvent;
use Mautic\PageBundle\PageEvents;
use MauticPlugin\EcommerceConnectorBundle\Integration\Config;
use MauticPlugin\EcommerceConnectorBundle\Service\EcommerceOrderTracker;
use MauticPlugin\EcommerceConnectorBundle\Service\OrderPayloadParser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TrackingSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EcommerceOrderTracker $orderTracker,
        private OrderPayloadParser $payloadParser,
        private Config $config,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PageEvents::PAGE_ON_HIT => ['onPageHit', 0],
        ];
    }

    public function onPageHit(PageHitEvent $event): void
    {
        if (!$this->config->isPublished() || !$this->config->isPageHitTrackingEnabled()) {
            return;
        }

        $query = $event->getHit()->getQuery() ?? [];
        $payload = $this->payloadParser->parse($query, 'page_hit');
        if (null === $payload) {
            return;
        }

        $this->orderTracker->recordOrder(
            $payload,
            $event->getHit()->getLead(),
            $event->getHit()->getEmail()
        );
    }
}
