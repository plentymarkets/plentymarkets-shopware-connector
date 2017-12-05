<?php

namespace PlentyConnector\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use Enlight_Template_Manager;

/**
 * Class ControllerPath
 */
class ControllerPathSubscriber implements SubscriberInterface
{
    /**
     * @var Enlight_Template_Manager
     */
    private $template;

    /**
     * @var string
     */
    private $pluginDir;

    /**
     * ControllerPath constructor.
     *
     * @param Enlight_Template_Manager $template
     * @param string $pluginDir
     */
    public function __construct(Enlight_Template_Manager $template, $pluginDir)
    {
        $this->template  = $template;
        $this->pluginDir = $pluginDir;
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
        $this->template->addTemplateDir($this->pluginDir . '/Resources/views/');

        return $this->pluginDir.'/Controller/Backend/PlentyConnector.php';
    }
}
