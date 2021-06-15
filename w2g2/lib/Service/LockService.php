<?php

namespace OCA\w2g2\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\w2g2\Db\Lock;
use OCA\w2g2\Db\LockMapper;
use OCA\w2g2\Db\ConfigMapper;
use OCA\w2g2\UIMessage;
use OCA\w2g2\File;

class LockService
{
    protected $lockMapper;
    protected $configMapper;
    protected $file;
    protected $uiMessage;
    protected $currentUser;

    public function __construct(
        LockMapper $lockMapper,
        ConfigMapper $configMapper,
        File $file
    )
    {
        $this->lockMapper = $lockMapper;
        $this->configMapper = $configMapper;

        $this->file = $file;

        $this->currentUser = UserService::get();

        $this->uiMessage = new UIMessage($this->configMapper);
    }

    public function lock($fileId, $fileType)
    {
        // Admin option to regarding directory locking is set to none.
        if ($this->configMapper->getDirectoryLock() === 'directory_locking_none' && $fileType === 'dir') {
            return [
                'success' => false,
                'message' => $this->uiMessage->getDirectoryLockingNone()
            ];
        }

        $this->file->boot($fileId);

        if ($this->file->isLocked()) {
            return [
                'success' => true,
                'message' => $this->uiMessage->getAlreadyLocked()
            ];
        }

        if ($this->file->isGroupFolder()) {
            return [
                'success' => false,
                'message' => $this->uiMessage->getGroupFolderLockingNone()
            ];
        }

        $this->create($this->file->getId());

        $this->file->onLocked();

        return [
            'success' => true,
            'message' => $this->uiMessage->getLocked($this->currentUser)
        ];
    }

    public function unlock($id, $action = null)
    {
        $this->file->boot($id);

        if ( ! $this->file->isLocked()) {
            return [
                'success' => true,
                'message' => $this->uiMessage->getAlreadyUnlocked()
            ];
        }

        if ($this->file->canBeUnlockedBy($this->currentUser) || $action === 'admin_one') {
            $this->delete($id);

            $this->file->onUnlocked();

            return [
                'success' => true,
                'message' => $this->uiMessage->getUnlocked()
            ];
        }

        return [
            'success' => false,
            'message' => $this->uiMessage->getNoPermission()
        ];
    }

    public function all() {
        return $this->lockMapper->findAll();
    }

    public function find($fileId) {
        try {
            return $this->lockMapper->find($fileId);
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }

    public function create($fileId) {
        $lock = new Lock();

        $lock->setFileId($fileId);
        $lock->setLockedBy($this->currentUser);

        $this->lockMapper->store($lock);
    }

    public function delete($fileId) {
        try {
            $lock = $this->lockMapper->find($fileId);

            $this->lockMapper->deleteOne($lock);
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }

    public function deleteAll()
    {
        $this->lockMapper->deleteAll();
    }

    public function check($fileId, $fileType)
    {
        $this->file->boot($fileId);

        if ($this->file->isLocked()) {
            return $this->uiMessage->getLocked($this->file->getLocker());
        }

        $directoryLock = $this->configMapper->getDirectoryLock();

        // Admin config to not check the upper directories.
        if ($directoryLock === 'directory_locking_none') {
            return '';
        }

        $fileParentId = $this->file->getParentId();

        $this->file->boot($fileParentId);
        $fileParentData = $this->file->getCompleteData();

        // Root directory or a group folder root, so no parent.
        if ( ! $fileParentData || $fileParentData['path'] === 'files' || $fileParentData['path'] === '__groupfolders') {
            return '';
        }

        // Check the parent directory above, depending on the admin config.
        if ($directoryLock === 'directory_locking_files') {
            if ($fileType === 'file' && $this->file->isLocked()) {
                return $this->uiMessage->getLocked($this->file->getLocker());
            }

            return '';
        }

        // Check all parent directories above, depending on the admin config.
        // $this->directoryLock === 'directory_locking_all'
        if ($this->file->isLocked()) {
            return $this->uiMessage->getLocked($this->file->getLocker());
        }

        $currentDirectoryData = $this->file->getCompleteData();

        while (
            $currentDirectoryData &&
            $currentDirectoryData['path'] !== 'files' &&
            $currentDirectoryData['path'] !== '__groupfolders'
        ) {
            $upperDirectoryId = $this->file->getParentId();

            $this->file->boot($upperDirectoryId);

            if ($this->file->isLocked()) {
                return $this->uiMessage->getLocked($this->file->getLocker());
            }

            $currentDirectoryData = $this->file->getCompleteData();
        }

        return '';
    }

    private function handleException ($e) {
        if ($e instanceof DoesNotExistException || $e instanceof MultipleObjectsReturnedException) {
            throw new NotFoundException($e->getMessage());
        } else {
            throw $e;
        }
    }
}
