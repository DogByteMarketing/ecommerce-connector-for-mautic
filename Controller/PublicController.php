<?php

declare(strict_types=1);

namespace MauticPlugin\EcommerceConnectorBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Mautic\CoreBundle\Controller\CommonController;
use Mautic\CoreBundle\Helper\ClickthroughHelper;
use Mautic\EmailBundle\Entity\Email;
use Mautic\LeadBundle\Helper\ContactRequestHelper;
use Mautic\LeadBundle\Model\LeadModel;
use MauticPlugin\EcommerceConnectorBundle\Service\EcommerceOrderTracker;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PublicController extends CommonController
{
    public function ecommerceAction(
        Request $request,
        ContactRequestHelper $contactRequestHelper,
        LeadModel $leadModel,
        EntityManagerInterface $entityManager,
        EcommerceOrderTracker $orderTracker
    ): Response {
        $query = array_merge($request->query->all(), $request->request->all());

        $orderId = isset($query['order_id']) ? trim((string) $query['order_id']) : '';
        $orderTotalRaw = $query['order_total'] ?? null;

        if ($orderId === '' || !is_numeric($orderTotalRaw)) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        $orderSource   = isset($query['order_source']) ? trim((string) $query['order_source']) : 'web';
        $orderSource   = $orderSource !== '' ? $orderSource : 'web';
        $orderCurrency = isset($query['order_currency']) ? trim((string) $query['order_currency']) : null;

        $lead = $contactRequestHelper->getContactFromQuery($query);

        if (!$lead && isset($query['email']) && is_string($query['email'])) {
            $emailAddress = trim($query['email']);
            if ($emailAddress !== '') {
                $leadData = $leadModel->getRepository()->getLeadByEmail($emailAddress);
                if (isset($leadData['id'])) {
                    $lead = $leadModel->getEntity((int) $leadData['id']);
                }
            }
        }

        $email = null;
        $emailId = isset($query['email_id']) ? (int) $query['email_id'] : 0;
        if (!$emailId && isset($query['ct'])) {
            $clickthrough = is_array($query['ct']) ? $query['ct'] : ClickthroughHelper::decodeArrayFromUrl((string) $query['ct']);
            if (is_array($clickthrough) && isset($clickthrough['email'])) {
                $emailId = (int) $clickthrough['email'];
            }
        }

        if ($emailId > 0) {
            $email = $entityManager->getRepository(Email::class)->find($emailId);
        }

        $orderTracker->recordOrder(
            $orderId,
            (float) $orderTotalRaw,
            $orderSource,
            $orderCurrency,
            $lead,
            $email
        );

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
