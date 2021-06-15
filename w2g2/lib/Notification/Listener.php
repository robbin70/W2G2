<?php

namespace OCA\w2g2\Notification;

use OCP\IUserManager;
use OCP\Notification\IManager;
use OCA\w2g2\Activity\FileLockEvent;
use OCA\w2g2\Db\FavoriteMapper;

class Listener {
    /** @var IManager */
    protected $notificationManager;

    /** @var IUserManager */
    protected $userManager;

    protected $favoriteMapper;

    /**
     * Listener constructor.
     *
     * @param IManager $notificationManager
     * @param IUserManager $userManager
     */
    public function __construct(IManager $notificationManager, IUserManager $userManager, FavoriteMapper  $favoriteMapper)
    {
        $this->notificationManager = $notificationManager;
        $this->userManager = $userManager;
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
