<?php

namespace OCA\w2g2\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;

class AdminSettings implements ISettings
{
    protected $appName;
    protected $configService;
    protected $adminService;

    public function __construct()
    {
        $this->appName = 'w2g2';

        $app = new \OCP\AppFramework\App('w2g2');

        $this->configService = $app->getContainer()->get('ConfigService');
        $this->adminService = $app->getContainer()->get('AdminService');
    }

    public function getForm()
    {
        $lockingByNameRule = $this->configService->getLockingByNameRule();
        $directoryLock = $this->configService->getDirectoryLock();

        $data = [
            'appName' => $this->appName,

            'color' => $this->configService->getColor(),
            'fontColor' => $this->configService->getFontColor(),
            'lockedFiles' => $this->adminService->getLocks(),

            'lockingByUsername' => $lockingByNameRule === "rule_username" ? 'checked' : '',
            'lockingByDisplayName' => $lockingByNameRule === "rule_displayname" ? 'checked' : '',

            'directoryLockingAll' => $directoryLock === "directory_locking_all",
            'directoryLockingFiles' => $directoryLock === "directory_locking_files",
            'directoryLockingNone' => $directoryLock === "directory_locking_none",
        ];

        return new TemplateResponse($this->appName, 'admin', $data);
    }

    /**
     * @return string the section ID, e.g. 'sharing'
     */
    public function getSection() {
        return 'additional';
    }

    /**
     * @return int whether the form should be rather on the top or bottom of
     * the admin section. The forms are arranged in ascending order of the
     * priority values. It is required to return a value between 0 and 100.
     */
    public function getPriority() {
        return 0;
    }
}
