<?php

namespace PlentyConnector\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use Enlight_Template_Manager;

class ControllerPathSubscriber implements SubscriberInterface
{
    /**
     * @var Enlight_Template_Manager
     */
    private $template;

    /**
     * @var string
     */
    private $pluginDirectory;

    /**
     * ControllerPathSubscriber constructor.
     *
     * @param Enlight_Template_Manager $template
     * @param string                   $pluginDirectory
     */
    public function __construct(Enlight_Template_Manager $template, string $pluginDirectory)
    {
        $this->template = $template;
        $this->pluginDirectory = $pluginDirectory;
    }

    /**
     * {@inheritdoc9
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
     * @return string
     */
    public function onControllerBackendPlentyConnector(Enlight_Event_EventArgs $args)
    {
        $this->template->addTemplateDir(
            $this->pluginDirectory . '/Resources/Views/'
        );

        return $this->pluginDirectory . '/Controller/Backend/PlentyConnector.php';
    }
}
