<?php

declare(strict_types=1);

namespace MauticPlugin\EcommerceConnectorBundle\Entity;

use Mautic\CoreBundle\Entity\CommonRepository;

/**
 * @extends CommonRepository<EcommerceOrder>
 */
class EcommerceOrderRepository extends CommonRepository
{
    public function orderExists(string $orderId, string $orderSource): bool
    {
        $query = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $query->select('1')
            ->from(MAUTIC_TABLE_PREFIX.'ecommerce_orders', 'eo')
            ->where('eo.order_id = :orderId')
            ->andWhere('eo.order_source = :orderSource')
            ->setParameter('orderId', $orderId)
            ->setParameter('orderSource', $orderSource)
            ->setMaxResults(1);

        return (bool) $query->executeQuery()->fetchOne();
    }

    /**
     * @return array<string, string>
     */
    public function getRevenueGroupedByCurrencyForEmail(int $emailId): array
    {
        $query = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $query->select('COALESCE(eo.order_currency, :defaultCurrency) AS currency')
            ->addSelect('SUM(eo.order_total) AS revenue_total')
            ->from(MAUTIC_TABLE_PREFIX.'ecommerce_orders', 'eo')
            ->where('eo.email_id = :emailId')
            ->groupBy('currency')
            ->setParameter('emailId', $emailId)
            ->setParameter('defaultCurrency', 'USD');

        $results = $query->executeQuery()->fetchAllAssociative();
        $revenue = [];

        foreach ($results as $row) {
            $currency = strtoupper((string) $row['currency']);
            $revenue[$currency] = number_format((float) $row['revenue_total'], 2, '.', '');
        }

        return $revenue;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array{results: array<int, array<string, mixed>>, total: int}
     */
    public function getOrdersForLeadTimeline(int $leadId, array $options = []): array
    {
        $query = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $query->select(
            'eo.id',
            'eo.order_id',
            'eo.order_total',
            'eo.order_currency',
            'eo.order_source',
            'eo.date_added',
            'e.id AS email_id',
            'e.subject AS email_subject'
        )
            ->from(MAUTIC_TABLE_PREFIX.'ecommerce_orders', 'eo')
            ->leftJoin('eo', MAUTIC_TABLE_PREFIX.'emails', 'e', 'e.id = eo.email_id')
            ->where('eo.lead_id = :leadId')
            ->setParameter('leadId', $leadId)
            ->orderBy('eo.date_added', 'DESC');

        if (!empty($options['limit'])) {
            $query->setMaxResults((int) $options['limit']);
        }

        if (!empty($options['offset'])) {
            $query->setFirstResult((int) $options['offset']);
        }

        $results = $query->executeQuery()->fetchAllAssociative();

        $countQuery = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $countQuery->select('COUNT(eo.id)')
            ->from(MAUTIC_TABLE_PREFIX.'ecommerce_orders', 'eo')
            ->where('eo.lead_id = :leadId')
            ->setParameter('leadId', $leadId);

        return [
            'results' => $results,
            'total'   => (int) $countQuery->executeQuery()->fetchOne(),
        ];
    }
}
