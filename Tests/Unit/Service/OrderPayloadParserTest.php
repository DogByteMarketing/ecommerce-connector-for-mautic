<?php

declare(strict_types=1);

namespace MauticPlugin\EcommerceConnectorBundle\Tests\Unit\Service;

use MauticPlugin\EcommerceConnectorBundle\Integration\Config;
use MauticPlugin\EcommerceConnectorBundle\Service\OrderPayloadParser;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class OrderPayloadParserTest extends TestCase
{
    private Config&MockObject $config;

    private OrderPayloadParser $parser;

    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
        $this->config->method('getMaxOrderTotal')->willReturn(1000000.0);
        $this->config->method('getDefaultCurrency')->willReturn('USD');
        $this->config->method('getAllowedOrderSources')->willReturn([]);

        $this->parser = new OrderPayloadParser(
            $this->config,
            $this->createMock(LoggerInterface::class)
        );
    }

    public function testParseReturnsPayloadForValidInput(): void
    {
        $payload = $this->parser->parse([
            'order_id'       => 'order-123',
            'order_total'    => '49.99',
            'order_source'   => 'woocommerce',
            'order_currency' => 'eur',
        ]);

        $this->assertNotNull($payload);
        $this->assertSame('order-123', $payload->orderId);
        $this->assertSame('49.9900', $payload->orderTotal);
        $this->assertSame('woocommerce', $payload->orderSource);
        $this->assertSame('EUR', $payload->orderCurrency);
    }

    public function testParseRejectsNonPositiveTotals(): void
    {
        $this->assertNull($this->parser->parse([
            'order_id'    => 'order-123',
            'order_total' => '0',
        ]));

        $this->assertNull($this->parser->parse([
            'order_id'    => 'order-123',
            'order_total' => '-10',
        ]));
    }

    public function testParseRejectsDisallowedSource(): void
    {
        $config = $this->createMock(Config::class);
        $config->method('getMaxOrderTotal')->willReturn(1000000.0);
        $config->method('getDefaultCurrency')->willReturn('USD');
        $config->method('getAllowedOrderSources')->willReturn(['web']);

        $parser = new OrderPayloadParser(
            $config,
            $this->createMock(LoggerInterface::class)
        );

        $this->assertNull($parser->parse([
            'order_id'     => 'order-123',
            'order_total'  => '10',
            'order_source' => 'shopify',
        ]));
    }
}
