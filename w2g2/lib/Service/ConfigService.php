<?php

namespace OCA\w2g2\Service;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\w2g2\Db\ConfigMapper;

class ConfigService
{
    protected $configMapper;

    public function __construct(ConfigMapper $configMapper)
    {
        $this->configMapper = $configMapper;
    }

    public function getColor()
    {
        return $this->configMapper->getColor();
    }

    public function getFontColor()
    {
        return $this->configMapper->getFontColor();
    }

    public function getDirectoryLock()
    {
        return $this->configMapper->getDirectoryLock();
    }

    public function getLockingByNameRule()
    {
        return $this->configMapper->getLockingByNameRule();
    }

    public function store($type, $value)
    {
        $this->configMapper->store($type, $value);
    }

    public function update($type, $value)
    {
        $this->configMapper->update($type, $value);
    }

    private function handleException($e)
    {
        if ($e instanceof DoesNotExistException ||
            $e instanceof MultipleObjectsReturnedException) {
            throw new NotFoundException($e->getMessage());
        } else {
            throw $e;
        }
    }
}
