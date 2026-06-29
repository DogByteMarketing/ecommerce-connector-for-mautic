<?php

declare(strict_types=1);

use MauticPlugin\EcommerceConnectorBundle\Integration\Config;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $services->load('MauticPlugin\\EcommerceConnectorBundle\\', '../')
        ->exclude('../{'.implode(',', Mautic\CoreBundle\DependencyInjection\MauticCoreExtension::DEFAULT_EXCLUDES).'}');

    $services->load('MauticPlugin\\EcommerceConnectorBundle\\Entity\\', '../Entity/*Repository.php')
        ->tag(Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\ServiceRepositoryCompilerPass::REPOSITORY_SERVICE_TAG);

    $services->set(Config::class)
        ->arg('$integrationHelper', service('mautic.helper.integration'));
};
