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

    public function getRevenueForEmail(int $emailId): float
    {
        $query = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $query->select('SUM(eo.order_total) as revenue_total')
            ->from(MAUTIC_TABLE_PREFIX.'ecommerce_orders', 'eo')
            ->where('eo.email_id = :emailId')
            ->setParameter('emailId', $emailId);

        return (float) $query->executeQuery()->fetchOne();
    }
}
