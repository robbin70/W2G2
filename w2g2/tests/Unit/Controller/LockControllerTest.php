<?php

namespace OCA\w2g2\Tests\Unit\Controller;

use PHPUnit\Framework\TestCase;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;

use OCA\w2g2\Controller\LockController;
use OCA\w2g2\Service\NotFoundException;

class LockControllerTest extends TestCase {

    protected $controller;
    protected $service;
    protected $userId = 'john';
    protected $request;

    public function setUp(): void
    {
        $this->request = $this->getMockBuilder('OCP\IRequest')->getMock();

        $this->service = $this->getMockBuilder('OCA\w2g2\Service\LockService')
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = new LockController(
            'w2g2', $this->request, $this->service, $this->userId
        );
    }

    public function testUpdate()
    {
        $note = 'just check if this value is returned correctly';

        $this->service->expects($this->once())
            ->method('update')
            ->with($this->equalTo(3),
                $this->equalTo('title'),
                $this->equalTo('content'),
                $this->equalTo($this->userId))
            ->will($this->returnValue($note));

        $result = $this->controller->update(3, 'title', 'content');

        $this->assertEquals($note, $result->getData());
    }

    public function testUpdateNotFound()
    {
        // test the correct status code if no note is found
        $this->service->expects($this->once())
            ->method('update')
            ->will($this->throwException(new NotFoundException()));

        $result = $this->controller->update(3, 'title', 'content');

        $this->assertEquals(Http::STATUS_NOT_FOUND, $result->getStatus());
    }
}
