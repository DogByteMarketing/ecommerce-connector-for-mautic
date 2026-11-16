<?php

declare(strict_types=1);

namespace MauticPlugin\EcommerceConnectorBundle\Tests\Unit\Twig;

use MauticPlugin\EcommerceConnectorBundle\Twig\EcommerceExtension;
use PHPUnit\Framework\TestCase;

class EcommerceExtensionTest extends TestCase
{
    public function testDateGroupMethodUsesTextShortWhenAvailable(): void
    {
        $dateHelper = new class {
            public function toTextShort(): string
            {
                return 'Today';
            }

            public function toShort(): string
            {
                return 'Mon, Nov 16';
            }
        };

        $this->assertSame('toTextShort', EcommerceExtension::dateGroupMethod($dateHelper));
    }

    public function testDateGroupMethodFallsBackToShortBeforeMautic72(): void
    {
        $dateHelper = new class {
            public function toShort(): string
            {
                return 'Mon, Nov 16';
            }
        };

        $this->assertSame('toShort', EcommerceExtension::dateGroupMethod($dateHelper));
    }
}
