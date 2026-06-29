<?php

declare(strict_types=1);

namespace MauticPlugin\EcommerceConnectorBundle\EventSubscriber;

use Mautic\CoreBundle\CoreEvents;
use Mautic\CoreBundle\Event\CustomContentEvent;
use Mautic\CoreBundle\Translation\Translator;
use Mautic\EmailBundle\Entity\Email;
use MauticPlugin\EcommerceConnectorBundle\Entity\EcommerceOrderRepository;
use MauticPlugin\EcommerceConnectorBundle\Integration\Config;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EmailStatsSubscriber implements EventSubscriberInterface
{
    /**
     * @var array<int, array<string, string>>
     */
    private array $revenueCache = [];

    public function __construct(
        private EcommerceOrderRepository $orderRepository,
        private Config $config,
        private Translator $translator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CoreEvents::VIEW_INJECT_CUSTOM_CONTENT => ['onViewInjectCustomContent', 0],
        ];
    }

    public function onViewInjectCustomContent(CustomContentEvent $event): void
    {
        if (!$this->config->isPublished()) {
            return;
        }

        if (!in_array($event->getContext(), ['email.stats', 'details.stats.graph.below'], true)) {
            return;
        }

        $vars  = $event->getVars();
        $email = $vars['item'] ?? $vars['entity'] ?? null;

        if (!$email instanceof Email || !$email->getId()) {
            return;
        }

        $event->addContent($this->buildRevenueContent((int) $email->getId()));
    }

    private function buildRevenueContent(int $emailId): string
    {
        if (!array_key_exists($emailId, $this->revenueCache)) {
            $this->revenueCache[$emailId] = $this->orderRepository->getRevenueGroupedByCurrencyForEmail($emailId);
        }

        $revenueByCurrency = $this->revenueCache[$emailId];
        if ([] === $revenueByCurrency) {
            $revenueByCurrency = [
                $this->config->getDefaultCurrency() => '0.00',
            ];
        }

        $labels = [];
        foreach ($revenueByCurrency as $currency => $amount) {
            $labels[] = sprintf(
                '<span class="mt-xs label label-green"><i class="ri-money-dollar-circle-line"></i><span title="%s">%s</span></span>',
                $this->translator->trans('mautic.plugin.ecommerce.revenue'),
                $this->formatRevenueAmount($currency, $amount),
            );
        }

        return implode('', $labels);
    }

    private function formatRevenueAmount(string $currencyCode, string $amount): string
    {
        $formatter = new \NumberFormatter('en_US', \NumberFormatter::CURRENCY);
        $formatted = $formatter->formatCurrency((float) $amount, strtoupper($currencyCode));

        if (false !== $formatted) {
            return $formatted;
        }

        return number_format((float) $amount, 2, '.', '');
    }
}
