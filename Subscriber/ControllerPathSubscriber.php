<?php

namespace PlentyConnector\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class ControllerPath.
 */
class ControllerPathSubscriber implements SubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * ControllerPathSubscriber constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc9.
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_PlentyConnector' => 'onControllerBackendPlentyConnector',
        ];
    }

    /**
     * @param Enlight_Event_EventArgs $args
     *
     * @throws InvalidArgumentException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     *
     * @return string
     */
    public function onControllerBackendPlentyConnector(Enlight_Event_EventArgs $args)
    {
        $basePath = $this->container->getParameter('plenty_connector.plugin_dir');

        $this->container->get('template')->addTemplateDir(
            $basePath.'/Resources/Views/'
        );

        return $basePath.'/Controller/Backend/PlentyConnector.php';
    }
}
