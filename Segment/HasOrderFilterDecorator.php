<?php

declare(strict_types=1);

namespace MauticPlugin\EcommerceConnectorBundle\Segment;

use Mautic\LeadBundle\Segment\ContactSegmentFilterCrate;
use Mautic\LeadBundle\Segment\ContactSegmentFilterOperator;
use Mautic\LeadBundle\Segment\Decorator\CustomMappedDecorator;
use Mautic\LeadBundle\Services\ContactSegmentFilterDictionary;

final class HasOrderFilterDecorator extends CustomMappedDecorator
{
    public function __construct(
        ContactSegmentFilterOperator $contactSegmentFilterOperator,
        ContactSegmentFilterDictionary $dictionary,
    ) {
        parent::__construct($contactSegmentFilterOperator, $dictionary);
    }

    /**
     * @return string
     */
    public function getOperator(ContactSegmentFilterCrate $contactSegmentFilterCrate)
    {
        if ('boolean' !== $contactSegmentFilterCrate->getType()) {
            return parent::getOperator($contactSegmentFilterCrate);
        }

        return ('=' === $contactSegmentFilterCrate->getOperator()) === (bool) $contactSegmentFilterCrate->getFilter()
            ? 'notEmpty'
            : 'empty';
    }
}
