<?php

namespace OCA\w2g2\Controller;

use Countable;
use OCP\IRequest;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Controller;

use OCA\w2g2\Locker;
use OCA\w2g2\CheckState;
use OCA\w2g2\Service\LockService;

class LockController extends Controller {
    /** @var LockService */
    private $service;

    public function __construct($AppName, IRequest $request, LockService $lockService)
    {
        parent::__construct($AppName, $request);

        $this->service = $lockService;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param $files
     * @param $folder
     * @return JSONResponse
     */
    public function index($files)
    {
        $files = json_decode($files, true);

        if ( ! is_array($files) && ! $files instanceof Countable) {
            $files = [];
        }

        for ($i = 0; $i < count($files); $i++) {
            $fileData = [];

            $fileData['id'] = $files[$i][0];
            $fileData['fileType'] = count($files[$i]) >= 5 ? $files[$i][5] : null;

            $response = $this->service->check($fileData['id'], $fileData['fileType']);

            if ($response !== null) {
                $files[$i][3] = $response;
            }
        }

        return new JSONResponse($files);
    }

    /**
     * @NoAdminRequired
     *
     * @param $id
     * @param $fileType
     * @return JSONResponse
     */
    public function store($id, $fileType)
    {
        $data = $this->service->lock($id, $fileType);

        if ($data['success']) {
            return new JSONResponse([
                'message' => $data['message']
            ], 201);
        }

        return new JSONResponse([
            'message' => $data['message']
        ], 403);
    }

    /**
     * @NoAdminRequired
     *
     * @param $action
     * @param null $id
     * @return JSONResponse
     */
    public function delete($action = null, $id = null)
    {
        if ($action === "admin_all") {
            return new JSONResponse($this->service->deleteAll());
        }

        $data = $this->service->unlock($id, $action);

        if ($data['success']) {
            return new JSONResponse([
                'message' => $data['message']
            ], 201);
        }

        return new JSONResponse([
            'message' => $data['message']
        ], 403);
    }
}
