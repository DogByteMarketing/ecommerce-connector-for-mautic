<?php

declare(strict_types=1);

namespace MauticPlugin\EcommerceConnectorBundle\Service;

final class LeadCommerceFieldDefinition
{
    public const GROUP = 'ecommerce';

    public const LIFETIME_VALUE = 'ecommerce_lifetime_value';

    public const ORDER_COUNT = 'ecommerce_order_count';

    public const LAST_ORDER_DATE = 'ecommerce_last_order_date';

    public const LAST_ORDER_TOTAL = 'ecommerce_last_order_total';

    /**
     * @return array<string, array{label: string, type: string, alias: string}>
     */
    public static function getFields(): array
    {
        return [
            self::LIFETIME_VALUE => [
                'label' => 'mautic.plugin.ecommerce.field.lifetime_value',
                'type'  => 'number',
                'alias' => self::LIFETIME_VALUE,
            ],
            self::ORDER_COUNT => [
                'label' => 'mautic.plugin.ecommerce.field.order_count',
                'type'  => 'number',
                'alias' => self::ORDER_COUNT,
            ],
            self::LAST_ORDER_DATE => [
                'label' => 'mautic.plugin.ecommerce.field.last_order_date',
                'type'  => 'datetime',
                'alias' => self::LAST_ORDER_DATE,
            ],
            self::LAST_ORDER_TOTAL => [
                'label' => 'mautic.plugin.ecommerce.field.last_order_total',
                'type'  => 'number',
                'alias' => self::LAST_ORDER_TOTAL,
            ],
        ];
    }
}
