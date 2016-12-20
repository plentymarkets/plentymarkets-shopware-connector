<?php

use Shopware\Kernel;
use Shopware\Models\Shop\Repository;
use Shopware\Models\Shop\Shop;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

require __DIR__ . '/../../../../../autoload.php';

/**
 * Class TestKernel
 */
class TestKernel extends Kernel
{
    /**
     * @return string
     */
    protected function getConfigPath()
    {
        return __DIR__ . '/../../../../../tests/Functional/config.php';
    }

    /**
     * @param ContainerBuilder $container
     * @param $filename
     *
     * @throws Exception
     */
    private function loadFile(ContainerBuilder $container, $filename)
    {
        if (!is_file($filename)) {
            return;
        }

        $loader = new XmlFileLoader(
            $container,
            new FileLocator()
        );

        $loader->load($filename);
    }

    /**
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    protected function prepareContainer(ContainerBuilder $container)
    {
        parent::prepareContainer($container);

        $this->loadFile($container, __DIR__ . '/DependencyInjection/services.xml');
    }

    /**
     * Static method to start boot kernel without leaving local scope in test helper
     *
     * @throws \Exception
     */
    public static function start()
    {
        $kernel = new self('testing', true);
        $kernel->boot();

        $container = $kernel->getContainer();
        $container->get('plugins')->Core()->ErrorHandler()->registerErrorHandler(E_ALL | E_STRICT);

        /**
         * @var $repository Repository
         */
        $repository = $container->get('models')->getRepository(Shop::class);

        $shop = $repository->getActiveDefault();
        $shop->registerResources();

        $_SERVER['HTTP_HOST'] = $shop->getHost();
    }
}

TestKernel::start();
