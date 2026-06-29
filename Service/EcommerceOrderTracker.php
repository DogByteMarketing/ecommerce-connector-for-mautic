<?php

declare(strict_types=1);

namespace MauticPlugin\EcommerceConnectorBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mautic\EmailBundle\Entity\Email;
use Mautic\EmailBundle\Entity\Stat;
use Mautic\LeadBundle\Entity\Lead;
use MauticPlugin\EcommerceConnectorBundle\Entity\EcommerceOrder;
use MauticPlugin\EcommerceConnectorBundle\Entity\EcommerceOrderRepository;

class EcommerceOrderTracker
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EcommerceOrderRepository $orderRepository,
    ) {
    }

    public function recordOrder(
        string $orderId,
        float $orderTotal,
        string $orderSource,
        ?string $orderCurrency,
        ?Lead $lead,
        ?Email $email,
    ): ?EcommerceOrder {
        $orderId     = trim($orderId);
        $orderSource = trim($orderSource);

        if ($orderId === '' || $orderSource === '' || !is_finite($orderTotal)) {
            return null;
        }

        if ($this->orderRepository->orderExists($orderId, $orderSource)) {
            return null;
        }

        if (null === $email && null !== $lead) {
            $email = $this->resolveEmailFromLead($lead);
        }

        $order = new EcommerceOrder();
        $order->setOrderId($orderId);
        $order->setOrderTotal($orderTotal);
        $order->setOrderCurrency($orderCurrency ?: null);
        $order->setOrderSource($orderSource);
        $order->setDateAdded(new \DateTimeImmutable());
        $order->setLead($lead);
        $order->setEmail($email);

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $order;
    }

    private function resolveEmailFromLead(Lead $lead): ?Email
    {
        $qb = $this->entityManager->getRepository(Stat::class)->createQueryBuilder('s');
        $qb->innerJoin('s.email', 'e')
            ->where('s.lead = :lead')
            ->setParameter('lead', $lead)
            ->orderBy('s.dateSent', 'DESC')
            ->addOrderBy('s.id', 'DESC')
            ->setMaxResults(1);

        $stat = $qb->getQuery()->getOneOrNullResult();

        return $stat?->getEmail();
    }
}
