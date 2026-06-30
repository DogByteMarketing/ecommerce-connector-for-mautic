<?php

declare(strict_types=1);

namespace MauticPlugin\EcommerceConnectorBundle\Tests\Unit\Service;

use MauticPlugin\EcommerceConnectorBundle\Service\CurrencyFormatter;
use PHPUnit\Framework\TestCase;

class CurrencyFormatterTest extends TestCase
{
    public function testFormatsUsdWithSymbol(): void
    {
        $formatter = new CurrencyFormatter();

        $this->assertSame('$15.25', $formatter->format('USD', '15.25', 'en_US'));
    }

    public function testFormatsEurWithSymbol(): void
    {
        $formatter = new CurrencyFormatter();

        $this->assertSame('€199.00', $formatter->format('EUR', '199', 'en_US'));
    }
}
