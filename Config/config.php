<?php

return [
    'name'        => 'Ecommerce Connector',
    'description' => 'Adds eCommerce stats to Mautic.',
    'version'     => '1.0.0',
    'author'      => 'Dog Byte Marketing',
    'routes'      => [
        'public' => [
            'mautic_ecommerce_track' => [
                'path'       => '/mtc/ecommerce',
                'controller' => 'MauticPlugin\EcommerceConnectorBundle\Controller\PublicController::ecommerceAction',
                'method'     => 'POST',
            ],
        ],
    ],
];
