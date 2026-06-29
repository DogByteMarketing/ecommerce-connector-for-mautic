<?php

declare(strict_types=1);

namespace MauticPlugin\EcommerceConnectorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\EmailBundle\Entity\Email;
use Mautic\LeadBundle\Entity\Lead;

class EcommerceOrder
{
    private int $id;

    private ?Lead $lead = null;

    private ?Email $email = null;

    private string $orderId;

    private float $orderTotal;

    private ?string $orderCurrency = null;

    private string $orderSource;

    private \DateTimeInterface $dateAdded;

    public static function loadMetadata(ORM\ClassMetadata $metadata): void
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('ecommerce_orders')
            ->setCustomRepositoryClass(EcommerceOrderRepository::class)
            ->addIndex(['email_id'], 'ecommerce_email_id')
            ->addIndex(['lead_id'], 'ecommerce_lead_id')
            ->addIndex(['order_id'], 'ecommerce_order_id')
            ->addUniqueConstraint(['order_id', 'order_source'], 'ecommerce_order_source_unique');

        $builder->addId();

        $builder->createManyToOne('lead', Lead::class)
            ->addJoinColumn('lead_id', 'id', true, false, 'SET NULL')
            ->build();

        $builder->createManyToOne('email', Email::class)
            ->addJoinColumn('email_id', 'id', true, false, 'SET NULL')
            ->build();

        $builder->addNamedField('orderId', 'string', 'order_id');
        $builder->addNamedField('orderTotal', 'float', 'order_total');
        $builder->addNamedField('orderCurrency', 'string', 'order_currency', true);
        $builder->addNamedField('orderSource', 'string', 'order_source');
        $builder->addNamedField('dateAdded', 'datetime', 'date_added');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLead(): ?Lead
    {
        return $this->lead;
    }

    public function setLead(?Lead $lead): void
    {
        $this->lead = $lead;
    }

    public function getEmail(): ?Email
    {
        return $this->email;
    }

    public function setEmail(?Email $email): void
    {
        $this->email = $email;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function setOrderId(string $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function getOrderTotal(): float
    {
        return $this->orderTotal;
    }

    public function setOrderTotal(float $orderTotal): void
    {
        $this->orderTotal = $orderTotal;
    }

    public function getOrderCurrency(): ?string
    {
        return $this->orderCurrency;
    }

    public function setOrderCurrency(?string $orderCurrency): void
    {
        $this->orderCurrency = $orderCurrency;
    }

    public function getOrderSource(): string
    {
        return $this->orderSource;
    }

    public function setOrderSource(string $orderSource): void
    {
        $this->orderSource = $orderSource;
    }

    public function getDateAdded(): \DateTimeInterface
    {
        return $this->dateAdded;
    }

    public function setDateAdded(\DateTimeInterface $dateAdded): void
    {
        $this->dateAdded = $dateAdded;
    }
}
