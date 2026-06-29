<?php

declare(strict_types=1);

namespace MauticPlugin\EcommerceConnectorBundle\EventSubscriber;

use Mautic\CoreBundle\CoreEvents;
use Mautic\CoreBundle\Event\CustomContentEvent;
use Mautic\EmailBundle\Entity\Email;
use MauticPlugin\EcommerceConnectorBundle\Entity\EcommerceOrderRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EmailStatsSubscriber implements EventSubscriberInterface
{
    /**
     * @var array<int, float>
     */
    private array $revenueCache = [];

    public function __construct(private EcommerceOrderRepository $orderRepository)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CoreEvents::VIEW_INJECT_CUSTOM_CONTENT => ['onViewInjectCustomContent', 0],
        ];
    }

    public function onViewInjectCustomContent(CustomContentEvent $event): void
    {
        if ('email.stats' !== $event->getContext()) {
            return;
        }

        $vars  = $event->getVars();
        $email = $vars['item'] ?? null;

        if (!$email instanceof Email || !$email->getId()) {
            return;
        }

        $emailId = (int) $email->getId();
        if (!array_key_exists($emailId, $this->revenueCache)) {
            $this->revenueCache[$emailId] = $this->orderRepository->getRevenueForEmail($emailId);
        }

        $revenue = number_format($this->revenueCache[$emailId], 2, '.', '');

        $content = '
            <span class="mt-xs label label-green">
                <i class="ri-money-dollar-circle-line"></i><span title="Revenue">'.$revenue.'</span>
            </span>';

        $event->addContent($content);
    }
}
