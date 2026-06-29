<?php

declare(strict_types=1);

namespace MauticPlugin\EcommerceConnectorBundle\Tests\Unit\Service;

use MauticPlugin\EcommerceConnectorBundle\Service\WebhookSignatureValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class WebhookSignatureValidatorTest extends TestCase
{
    public function testValidSignatureIsAccepted(): void
    {
        $request = Request::create(
            '/mtc/ecommerce',
            'POST',
            [
                'order_id'    => 'abc-123',
                'order_total' => '25.50',
            ]
        );

        $payload = http_build_query([
            'order_id'    => 'abc-123',
            'order_total' => '25.50',
        ]);
        $signature = hash_hmac('sha256', $payload, 'secret-key');
        $request->headers->set(WebhookSignatureValidator::SIGNATURE_HEADER, $signature);

        $validator = new WebhookSignatureValidator();

        $this->assertTrue($validator->isValid($request, 'secret-key'));
    }

    public function testInvalidSignatureIsRejected(): void
    {
        $request = Request::create(
            '/mtc/ecommerce',
            'POST',
            [
                'order_id'    => 'abc-123',
                'order_total' => '25.50',
            ]
        );
        $request->headers->set(WebhookSignatureValidator::SIGNATURE_HEADER, 'invalid');

        $validator = new WebhookSignatureValidator();

        $this->assertFalse($validator->isValid($request, 'secret-key'));
    }
}
