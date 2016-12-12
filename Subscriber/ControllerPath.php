<?php

namespace PlentyConnector\Subscriber;

use Enlight\Event\SubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ControllerPath
 */
class ControllerPath implements SubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_Plentymarkets' => 'onControllerBackendPlentymarkets'
        ];
    }

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function onControllerBackendPlentymarkets()
    {
        $basePath = $this->container->getParameter('plentyconnector.plugin_dir');

        $this->container->get('template')->addTemplateDir(
            $basePath . '/Views/'
        );

        return $basePath . '/Controllers/Backend/Plentymarkets.php';
    }
}
