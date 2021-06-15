<?php

namespace OCA\w2g2\Db;

class AdminMapper
{
    protected $lockMapper;
    protected $groupFolderMapper;

    public function __construct(LockMapper $lockMapper, GroupFolderMapper $groupFolderMapper)
    {
        $this->lockMapper = $lockMapper;
        $this->groupFolderMapper = $groupFolderMapper;
    }

    public function getLocks()
    {
        $lockedFiles = $this->lockMapper->all();

        $groupFolderName = "__groupfolders/";
        $fileName = 'files/';

        for ($i = 0; $i < count($lockedFiles); $i++) {
            $groupFolderIndex = strpos($lockedFiles[$i]['path'], $groupFolderName);
            $fileIndex = strpos($lockedFiles[$i]['path'], $fileName);

            if ($groupFolderIndex === 0) {
                $path = substr($lockedFiles[$i]['path'], strlen($groupFolderName));

                $slashIndex = strpos($path, '/');

                $groupFolderId = substr($path, 0, $slashIndex);
                $file = substr($path, $slashIndex + 1);

                $result = $this->groupFolderMapper->getMountPoints($groupFolderId);

                if ($result && count($result) > 0) {
                    $path = $result[0]['mount_point'];

                    $details = $path . '/' . $file;

                    if (array_key_exists('created', $lockedFiles[$i]) && $lockedFiles[$i]['created']) {
                        $details .= ' --- Created: ' . $lockedFiles[$i]['created'];
                    }

                    $lockedFiles[$i]['path'] = $details;
                }
            } else if ($fileIndex === 0) {
                $filePath = substr($lockedFiles[$i]['path'], strlen('files/'));

                $details = $lockedFiles[$i]['locked_by'] . '/' . $filePath;

                if (array_key_exists('created', $lockedFiles[$i]) && $lockedFiles[$i]['created']) {
                    $details .= ' --- Created: ' . $lockedFiles[$i]['created'];
                }

                $lockedFiles[$i]['path'] = $details;
            }
        }

        return $lockedFiles;
    }
}
