<?php

namespace PlentyConnector\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class ControllerPath
 */
class ControllerPathSubscriber implements SubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

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
     * @param Enlight_Event_EventArgs $args
     *
     * @return string
     *
     * @throws InvalidArgumentException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    public function onControllerBackendPlentymarkets(Enlight_Event_EventArgs $args)
    {
        $basePath = $this->container->getParameter('plentyconnector.plugin_dir');

        $this->container->get('template')->addTemplateDir(
            $basePath . '/Resources/Views/'
        );

        return $basePath . '/Controller/Backend/Plentymarkets.php';
    }
}
