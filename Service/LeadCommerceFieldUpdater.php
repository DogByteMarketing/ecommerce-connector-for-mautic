<?php

declare(strict_types=1);

namespace MauticPlugin\EcommerceConnectorBundle\Service;

use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Model\LeadModel;
use MauticPlugin\EcommerceConnectorBundle\Entity\EcommerceOrderRepository;

class LeadCommerceFieldUpdater
{
    public function __construct(
        private EcommerceOrderRepository $orderRepository,
        private LeadModel $leadModel,
    ) {
    }

    public function syncForLead(Lead $lead): void
    {
        $leadId = $lead->getId();
        if ($leadId <= 0) {
            return;
        }

        $stats = $this->orderRepository->getCommerceStatsForLead($leadId);

        $values = [
            LeadCommerceFieldDefinition::LIFETIME_VALUE    => $stats['lifetime_value'],
            LeadCommerceFieldDefinition::ORDER_COUNT       => $stats['order_count'],
            LeadCommerceFieldDefinition::LAST_ORDER_TOTAL => $stats['last_order_total'],
        ];

        if (null !== $stats['last_order_date']) {
            $values[LeadCommerceFieldDefinition::LAST_ORDER_DATE] = $stats['last_order_date'];
        }

        $this->leadModel->setFieldValues($lead, $values, false);
        $this->leadModel->saveEntity($lead, false);
    }

    public function backfillAllContacts(): void
    {
        foreach ($this->orderRepository->getLeadIdsWithOrders() as $leadId) {
            $lead = $this->leadModel->getEntity($leadId);
            if ($lead instanceof Lead) {
                $this->syncForLead($lead);
            }
        }
    }
}
