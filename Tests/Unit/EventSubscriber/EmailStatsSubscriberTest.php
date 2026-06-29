<?php

declare(strict_types=1);

namespace MauticPlugin\EcommerceConnectorBundle\Tests\Unit\EventSubscriber;

use Mautic\CoreBundle\Event\CustomContentEvent;
use Mautic\CoreBundle\Translation\Translator;
use Mautic\EmailBundle\Entity\Email;
use MauticPlugin\EcommerceConnectorBundle\Entity\EcommerceOrderRepository;
use MauticPlugin\EcommerceConnectorBundle\EventSubscriber\EmailStatsSubscriber;
use MauticPlugin\EcommerceConnectorBundle\Integration\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EmailStatsSubscriberTest extends TestCase
{
    private EcommerceOrderRepository&MockObject $orderRepository;

    private Config&MockObject $config;

    private EmailStatsSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->orderRepository = $this->createMock(EcommerceOrderRepository::class);
        $this->config          = $this->createMock(Config::class);
        $translator            = $this->createMock(Translator::class);
        $translator->method('trans')->willReturnArgument(0);

        $this->subscriber = new EmailStatsSubscriber(
            $this->orderRepository,
            $this->config,
            $translator,
        );
    }

    public function testShowsZeroRevenueBadgeWhenIntegrationPublishedAndNoOrders(): void
    {
        $email = new Email();
        $email->setId(1);

        $this->config->expects($this->once())->method('isPublished')->willReturn(true);
        $this->config->expects($this->once())->method('getDefaultCurrency')->willReturn('USD');
        $this->orderRepository->expects($this->once())
            ->method('getRevenueGroupedByCurrencyForEmail')
            ->with(1)
            ->willReturn([]);

        $event = new CustomContentEvent('@MauticEmail/Email/list.html.twig', 'email.stats', ['item' => $email]);
        $this->subscriber->onViewInjectCustomContent($event);

        $content = implode('', $event->getContent());
        $this->assertStringContainsString('$0.00', $content);
        $this->assertStringNotContainsString('USD 0.00', $content);
        $this->assertStringContainsString('label-green', $content);
    }

    public function testFormatsRevenueWithCurrencySymbol(): void
    {
        $email = new Email();
        $email->setId(2);

        $this->config->method('isPublished')->willReturn(true);
        $this->orderRepository->method('getRevenueGroupedByCurrencyForEmail')
            ->with(2)
            ->willReturn(['EUR' => '125.50']);

        $event = new CustomContentEvent('@MauticEmail/Email/list.html.twig', 'email.stats', ['item' => $email]);
        $this->subscriber->onViewInjectCustomContent($event);

        $content = implode('', $event->getContent());
        $this->assertStringContainsString('€125.50', $content);
        $this->assertStringNotContainsString('EUR 125.50', $content);
    }

    public function testDoesNotShowBadgeWhenIntegrationUnpublished(): void
    {
        $email = new Email();
        $email->setId(1);

        $this->config->expects($this->once())->method('isPublished')->willReturn(false);
        $this->orderRepository->expects($this->never())->method('getRevenueGroupedByCurrencyForEmail');

        $event = new CustomContentEvent('@MauticEmail/Email/list.html.twig', 'email.stats', ['item' => $email]);
        $this->subscriber->onViewInjectCustomContent($event);

        $this->assertSame([], $event->getContent());
    }
}
