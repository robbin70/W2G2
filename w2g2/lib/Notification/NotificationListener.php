<?php

namespace OCA\w2g2\Notification;

use OCA\w2g2\Activity\FileLockEvent;
use OCA\w2g2\Db\FavoriteMapper;

class NotificationListener
{
    protected $notificationManager;

    protected $favoriteMapper;

    public function __construct(FavoriteMapper $favoriteMapper)
    {
        $this->notificationManager = \OC::$server->get(\OCP\Notification\IManager::class);
        $this->favoriteMapper = $favoriteMapper;
    }

    public function handle(FileLockEvent $event)
    {
        $fileId = $event->getFileId();

        $usersIds = $this->favoriteMapper->getUsersForFile($fileId);

        // No user favorited the locked file, don't send any notifications.
        if (count($usersIds) <= 0) {
            return;
        }

        $lockerUser = $event->getLockerUser();
        $eventType = $event->getEvent() === $event->getLockEventName() ? 'lock' : 'unlock';

        $notification = $this->instantiateNotification($fileId, $lockerUser, $eventType);

        foreach ($usersIds as $userId) {
            $notification->setUser($userId);

            $this->notificationManager->notify($notification);
        }
    }

    public function instantiateNotification($fileId, $lockerUser, $eventType)
    {
        $notification = $this->notificationManager->createNotification();

        $notification
            ->setApp('w2g2')
            ->setObject('w2g2', $fileId)
            ->setSubject('fileLock', ['files', $fileId, $lockerUser, $eventType])
            ->setDateTime(new \DateTime());

        return $notification;
    }
}
