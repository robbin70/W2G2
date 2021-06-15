<?php

namespace OCA\w2g2\AppInfo;

use OCA\w2g2\File;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\Notification\IManager;
use Psr\Container\ContainerInterface;

use OCA\w2g2\Notification\Notifier;
use OCA\w2g2\Controller\AdminController;
use OCA\w2g2\Controller\ConfigController;
use OCA\w2g2\Controller\LockController;
use OCA\w2g2\Service\AdminService;
use OCA\w2g2\Service\ConfigService;
use OCA\w2g2\Service\LockService;
use OCA\w2g2\Service\UserService;
use OCA\w2g2\Db\AdminMapper;
use OCA\w2g2\Db\CategoryMapper;
use OCA\w2g2\Db\ConfigMapper;
use OCA\w2g2\Db\FavoriteMapper;
use OCA\w2g2\Db\FileMapper;
use OCA\w2g2\Db\GroupFolderMapper;
use OCA\w2g2\Db\Lock;
use OCA\w2g2\Db\LockMapper;

class Application extends App implements IBootstrap
{
    const name = 'w2g2';

    public function __construct()
    {
        parent::__construct(self::name);

        $container = $this->getContainer();

        $this->registerMappers($container);
        $this->registerServices($container);
        $this->registerControllers($container);
    }

    public function boot(IBootContext $context): void
    {
        if ( ! \OC::$server->getAppManager()->isEnabledForUser(self::name)) {
            return;
        }

        $this->registerHooks($context);
        $this->registerScripts();
    }

    public function register(IRegistrationContext $context): void
    {
        //
    }

    public function registerHooks($context)
    {
        $manager = $context->getAppContainer()->query(IManager::class);
        $manager->registerNotifierService(Notifier::class);
    }

    public function registerScripts()
    {
        $eventDispatcher = \OC::$server->getEventDispatcher();
        $eventDispatcher->addListener('OCA\Files::loadAdditionalScripts', function() {
            script(self::name, 'w2g2');
            style(self::name, 'styles');
        });
    }

    protected function registerControllers($container)
    {
        $container->registerService('AdminController', function(ContainerInterface $c){
            return new AdminController(
                $c->get('AppName'),
                $c->get('Request'),
                $c->get('ConfigService'),
                $c->get('AdminService')
            );
        });

        $container->registerService('ConfigController', function(ContainerInterface $c){
            return new ConfigController(
                $c->get('AppName'),
                $c->get('Request'),
                $c->get('ConfigService')
            );
        });

        $container->registerService('LockController', function(ContainerInterface $c){
            return new LockController(
                $c->get('AppName'),
                $c->get('Request'),
                $c->get('LockService')
            );
        });
    }

    protected function registerServices($container)
    {
        $container->registerService('AdminService', function(ContainerInterface $c){
            return new AdminService(
                $c->get('AdminMapper')
            );
        });

        $container->registerService('ConfigService', function(ContainerInterface $c){
            return new ConfigService(
                $c->get('ConfigMapper')
            );
        });

        $container->registerService('LockService', function(ContainerInterface $c){
            return new LockService(
                $c->get('LockMapper'),
                $c->get('ConfigMapper'),
                $c->get('File')
            );
        });
    }

    protected function registerMappers($container)
    {
        $container->registerService('LockMapper', function(ContainerInterface $c){
            return new LockMapper(
                $c->get('ServerContainer')->getDatabaseConnection()
            );
        });

        $container->registerService('AdminMapper', function(ContainerInterface $c){
            return new AdminMapper(
                $c->get('LockMapper'),
                $c->get('GroupFolderMapper')
            );
        });

        $container->registerService('CategoryMapper', function(ContainerInterface $c){
            return new CategoryMapper(
                $c->get('ServerContainer')->getDatabaseConnection()
            );
        });

        $container->registerService('ConfigMapper', function(ContainerInterface $c){
            return new ConfigMapper(
                $c->get('ServerContainer')->getDatabaseConnection()
            );
        });

        $container->registerService('FavoriteMapper', function(ContainerInterface $c){
            return new FavoriteMapper(
                $c->get('CategoryMapper')
            );
        });

        $container->registerService('FileMapper', function(ContainerInterface $c){
            return new FileMapper(
                $c->get('ServerContainer')->getDatabaseConnection()
            );
        });

        $container->registerService('GroupFolderMapper', function(ContainerInterface $c){
            return new GroupFolderMapper(
                $c->get('ServerContainer')->getDatabaseConnection()
            );
        });

        //
        $container->registerService('File', function(ContainerInterface $c){
            return new File(
                $c->get('LockMapper'),
                $c->get('GroupFolderMapper'),
                $c->get('FileMapper')
            );
        });
    }
}
