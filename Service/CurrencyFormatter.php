<?php

declare(strict_types=1);

namespace MauticPlugin\EcommerceConnectorBundle\Service;

final class CurrencyFormatter
{
    public function format(string $currencyCode, float|string $amount, ?string $locale = null): string
    {
        $locale    = $locale ?: (\Locale::getDefault() ?: 'en_US');
        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        $formatted = $formatter->formatCurrency((float) $amount, strtoupper($currencyCode));

        if (false !== $formatted) {
            return $formatted;
        }

        return number_format((float) $amount, 2, '.', '');
    }
}
