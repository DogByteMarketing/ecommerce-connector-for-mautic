<?php

declare(strict_types=1);

namespace MauticPlugin\EcommerceConnectorBundle\Twig;

use Mautic\CoreBundle\Twig\Helper\DateHelper;
use MauticPlugin\EcommerceConnectorBundle\Integration\Config;
use MauticPlugin\EcommerceConnectorBundle\Service\CurrencyFormatter;
use MauticPlugin\EcommerceConnectorBundle\Service\PrivacyUpgradeNotice;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class EcommerceExtension extends AbstractExtension
{
    public function __construct(
        private Config $config,
        private CurrencyFormatter $currencyFormatter,
        private PrivacyUpgradeNotice $privacyUpgradeNotice,
        private DateHelper $dateHelper,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('ecommerce_money', [$this, 'formatMoney']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('ecommerce_privacy_upgrade_notice_pending', [$this, 'isPrivacyUpgradeNoticePending']),
            new TwigFunction('ecommerce_date_group_label', [$this, 'dateGroupLabel']),
        ];
    }

    /**
     * dateToTextShort exists from Mautic 7.2. Earlier 7.x releases only have dateToShort.
     */
    public function dateGroupLabel(mixed $datetime, string $timezone = 'local', string $fromFormat = 'Y-m-d H:i:s'): string
    {
        if ('toTextShort' === self::dateGroupMethod($this->dateHelper)) {
            return $this->dateHelper->toTextShort($datetime, $timezone, $fromFormat);
        }

        return $this->dateHelper->toShort($datetime, $timezone, $fromFormat);
    }

    public static function dateGroupMethod(object $dateHelper): string
    {
        return method_exists($dateHelper, 'toTextShort') ? 'toTextShort' : 'toShort';
    }

    public function isPrivacyUpgradeNoticePending(): bool
    {
        return $this->privacyUpgradeNotice->isPending();
    }

    public function formatMoney(float|int|string|null $amount): string
    {
        return $this->currencyFormatter->format(
            $this->config->getDefaultCurrency(),
            null === $amount || '' === $amount ? 0 : $amount
        );
    }
}
