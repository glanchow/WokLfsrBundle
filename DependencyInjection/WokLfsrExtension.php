<?php
namespace Wok\LfsrBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * Extension Class
 */
class WokLfsrExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
            );
        $loader->load('services.yml');

        $container->setParameter('wok_lfsr.feedback', $config['feedback']);
        $container->setParameter('wok_lfsr.state', $config['state']);
        $container->setParameter('wok_lfsr.base', $config['base']);
        $container->setParameter('wok_lfsr.pad', $config['pad']);
    }

}
