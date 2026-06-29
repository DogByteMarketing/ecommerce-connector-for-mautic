<?php

declare(strict_types=1);

namespace MauticPlugin\EcommerceConnectorBundle\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Mautic\EmailBundle\Entity\Email;
use Mautic\PageBundle\Event\PageHitEvent;
use Mautic\PageBundle\PageEvents;
use MauticPlugin\EcommerceConnectorBundle\Service\EcommerceOrderTracker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TrackingSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EcommerceOrderTracker $orderTracker,
        private EntityManagerInterface $entityManager
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PageEvents::PAGE_ON_HIT => ['onPageHit', 0],
        ];
    }

    public function onPageHit(PageHitEvent $event): void
    {
        $hit   = $event->getHit();
        $query = $hit->getQuery() ?? [];

        if (!isset($query['order_total'], $query['order_id'])) {
            return;
        }

        $orderTotal = $query['order_total'];
        if (!is_numeric($orderTotal)) {
            return;
        }

        $orderId = trim((string) $query['order_id']);
        if ($orderId === '') {
            return;
        }

        $orderSource = isset($query['order_source']) ? trim((string) $query['order_source']) : 'web';
        $orderSource = $orderSource !== '' ? $orderSource : 'web';
        $orderCurrency = isset($query['order_currency']) ? trim((string) $query['order_currency']) : null;

        $email = $hit->getEmail();
        if (!$email) {
            $clickthrough = $event->getClickthroughData();
            if (is_array($clickthrough) && isset($clickthrough['email'])) {
                $emailId = (int) $clickthrough['email'];
                if ($emailId > 0) {
                    $email = $this->entityManager->getRepository(Email::class)->find($emailId);
                }
            }
        }

        $this->orderTracker->recordOrder(
            $orderId,
            (float) $orderTotal,
            $orderSource,
            $orderCurrency,
            $hit->getLead(),
            $email
        );
    }
}
