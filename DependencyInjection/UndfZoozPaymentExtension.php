<?php

namespace Undf\ZoozPaymentBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class UndfZoozPaymentExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('undf_zooz_payment.unique_id', $config['config']['unique_id']);
        $container->setParameter('undf_zooz_payment.app_key', $config['config']['app_key']);
        $container->setParameter('undf_zooz_payment.response_type', $config['config']['response_type']);
        $container->setParameter('undf_zooz_payment.sandbox_mode', $config['config']['sandbox_mode']);

        $container->setParameter('undf_zooz_payment.ajax_mode', $config['ajax_mode']);

        $container->setParameter('undf_zooz_payment.success.template', $config['templates']['return']);
        $container->setParameter('undf_zooz_payment.error.template', $config['templates']['cancel']);

        $container->setParameter('undf_zooz_payment.payment.entity', $config['payment']['entity']);
        $container->setParameter('undf_zooz_payment.payment.manager.service', $config['payment']['manager']);

        $handler = $container->getDefinition('undf_zooz_payment.handler.open_transaction');
        $handler->replaceArgument(0, new Reference($container->getParameter('undf_zooz_payment.payment.manager.service')));

    }
}
