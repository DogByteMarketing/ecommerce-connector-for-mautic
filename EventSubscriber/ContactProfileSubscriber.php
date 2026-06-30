<?php

declare(strict_types=1);

namespace MauticPlugin\EcommerceConnectorBundle\EventSubscriber;

use Mautic\CoreBundle\CoreEvents;
use Mautic\CoreBundle\Event\CustomTemplateEvent;
use MauticPlugin\EcommerceConnectorBundle\Service\LeadCommerceFieldDefinition;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ContactProfileSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            CoreEvents::VIEW_INJECT_CUSTOM_TEMPLATE => ['onTemplateRender', 0],
        ];
    }

    public function onTemplateRender(CustomTemplateEvent $event): void
    {
        if ('@MauticLead/Lead/lead.html.twig' !== $event->getTemplate()) {
            return;
        }

        $vars = $event->getVars();
        if (!isset($vars['fields']) || !is_array($vars['fields'])) {
            return;
        }

        $vars['fields'] = $this->removeEcommerceGroupFromProfile($vars['fields']);
        $event->setVars($vars);
    }

    /**
     * @param array<string, mixed> $fields
     *
     * @return array<string, mixed>
     */
    private function removeEcommerceGroupFromProfile(array $fields): array
    {
        unset($fields[LeadCommerceFieldDefinition::GROUP]);

        return $fields;
    }
}
