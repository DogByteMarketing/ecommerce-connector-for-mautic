<?php

declare(strict_types=1);

namespace MauticPlugin\EcommerceConnectorBundle\Tests\Unit\Segment;

use Mautic\LeadBundle\Segment\ContactSegmentFilterCrate;
use Mautic\LeadBundle\Segment\ContactSegmentFilterOperator;
use Mautic\LeadBundle\Services\ContactSegmentFilterDictionary;
use MauticPlugin\EcommerceConnectorBundle\EventSubscriber\SegmentFilterSubscriber;
use MauticPlugin\EcommerceConnectorBundle\Segment\HasOrderFilterDecorator;
use PHPUnit\Framework\TestCase;

class HasOrderFilterDecoratorTest extends TestCase
{
    public function testYesMapsToNotEmpty(): void
    {
        $decorator = $this->createDecorator();

        $crate = new ContactSegmentFilterCrate([
            'field'    => SegmentFilterSubscriber::FILTER_HAS_ORDER,
            'type'     => 'boolean',
            'operator' => '=',
            'filter'   => '1',
        ]);

        $this->assertSame('notEmpty', $decorator->getOperator($crate));
    }

    public function testNoMapsToEmpty(): void
    {
        $decorator = $this->createDecorator();

        $crate = new ContactSegmentFilterCrate([
            'field'    => SegmentFilterSubscriber::FILTER_HAS_ORDER,
            'type'     => 'boolean',
            'operator' => '=',
            'filter'   => '0',
        ]);

        $this->assertSame('empty', $decorator->getOperator($crate));
    }

    private function createDecorator(): HasOrderFilterDecorator
    {
        return new HasOrderFilterDecorator(
            $this->createMock(ContactSegmentFilterOperator::class),
            $this->createMock(ContactSegmentFilterDictionary::class),
        );
    }
}
