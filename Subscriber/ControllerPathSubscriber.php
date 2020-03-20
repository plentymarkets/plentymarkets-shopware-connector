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
     * @param string $pluginDirectory
     */
    public function __construct(Enlight_Template_Manager $template, $pluginDirectory)
    {
        $this->template = $template;
        $this->pluginDirectory = $pluginDirectory;
    }

    /**
     * {@inheritdoc9
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_PlentyConnector' => 'onControllerBackendPlentyConnector',
        ];
    }

    public function onControllerBackendPlentyConnector(Enlight_Event_EventArgs $args): string
    {
        $this->template->addTemplateDir(
            $this->pluginDirectory . '/Resources/Views/'
        );

        return $this->pluginDirectory . '/Controller/Backend/PlentyConnector.php';
    }
}
