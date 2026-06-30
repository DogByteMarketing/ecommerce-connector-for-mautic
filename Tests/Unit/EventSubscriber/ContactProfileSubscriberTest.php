<?php

declare(strict_types=1);

namespace MauticPlugin\EcommerceConnectorBundle\Tests\Unit\EventSubscriber;

use Mautic\CoreBundle\Event\CustomTemplateEvent;
use MauticPlugin\EcommerceConnectorBundle\EventSubscriber\ContactProfileSubscriber;
use MauticPlugin\EcommerceConnectorBundle\Service\LeadCommerceFieldDefinition;
use PHPUnit\Framework\TestCase;

class ContactProfileSubscriberTest extends TestCase
{
    public function testEcommerceGroupIsRemovedFromContactProfileDetails(): void
    {
        $fields = [
            LeadCommerceFieldDefinition::GROUP => ['ecommerce_lifetime_value' => ['value' => '100']],
            'core'                               => ['email' => ['value' => 'test@example.com']],
            'personal'                           => [],
            'professional'                       => [],
        ];

        $event = new CustomTemplateEvent(null, '@MauticLead/Lead/lead.html.twig', ['fields' => $fields]);

        $subscriber = new ContactProfileSubscriber();
        $subscriber->onTemplateRender($event);

        $this->assertSame(
            ['core', 'personal', 'professional'],
            array_keys($event->getVars()['fields'])
        );
    }

    public function testOtherTemplatesAreIgnored(): void
    {
        $fields = [
            LeadCommerceFieldDefinition::GROUP => ['ecommerce_lifetime_value' => ['value' => '100']],
            'core'                               => ['email' => ['value' => 'test@example.com']],
        ];

        $event = new CustomTemplateEvent(null, '@MauticLead/Lead/form.html.twig', ['fields' => $fields]);

        $subscriber = new ContactProfileSubscriber();
        $subscriber->onTemplateRender($event);

        $this->assertSame(
            ['ecommerce', 'core'],
            array_keys($event->getVars()['fields'])
        );
    }
}
