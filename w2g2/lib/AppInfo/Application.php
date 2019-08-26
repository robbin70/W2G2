<?php

namespace OCA\w2g2\AppInfo;

use OCP\AppFramework\App;
use OCA\w2g2\Notification\Notifier;

class Application extends App
{
    const name = 'w2g2';

    public function __construct()
    {
        parent::__construct(self::name);
    }

    public function boot()
    {
        if ( ! \OC::$server->getAppManager()->isEnabledForUser(self::name)) {
            return;
        }

        $this->registerHooks();
        $this->registerScripts();
    }

    public function registerHooks()
    {
        $notificationManager = \OC::$server->getNotificationManager();

        $notificationManager->registerNotifierService(Notifier::class);
    }

    public function registerScripts()
    {
        $eventDispatcher = \OC::$server->getEventDispatcher();
        $eventDispatcher->addListener('OCA\Files::loadAdditionalScripts', function() {
            script(self::name, 'w2g2');
            style(self::name, 'styles');
        });
    }
}
