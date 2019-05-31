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
        $notificationManager->registerNotifier(
            function() {
                $Application = new \OCP\AppFramework\App('w2g2');

                return $Application->getContainer()->query(Notifier::class);
            },
            function () {
                $l = \OC::$server->getL10N('w2g2');

                return ['id' => 'w2g2', 'name' => $l->t('w2g2')];
            }
        );
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
