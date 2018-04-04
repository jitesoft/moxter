<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  EmailControllerTest.php - Part of the moxter project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Jitesoft\Moxter\Tests\Http\Controllers;

use Exception;
use Jitesoft\Exceptions\Http\Server\HttpInternalServerErrorException;
use Jitesoft\Exceptions\Validation\ValidationException;
use Jitesoft\Log\NullLogger;
use Jitesoft\Moxter\Config\Config;
use Jitesoft\Moxter\Contracts\EmailServiceInterface;
use Jitesoft\Moxter\Http\Controllers\EmailController;
use Jitesoft\Moxter\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequestFactory;

/**
 * EmailControllerTest
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class EmailControllerTest extends AbstractTestCase {

    /** @var MockObject|EmailServiceInterface */
    protected $mock;

    protected function setUp() {
        parent::setUp();

        $this->mock = $this->createMock(EmailServiceInterface::class);
    }

    public function testHandleValidationFailureTo() {
        $controller = new EmailController(new NullLogger(), $this->mock, new Config());

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('argh! is not a valid email address.');

        $request = ServerRequestFactory::fromGlobals([], [], [
            'to' => 'argh!',
            'subject' => 'abc123',
            'body' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.' .
                      'Morbi nec orci lobortis, maximus sem et, bibendum ex. Maecenas sit amet mattis nisl. In amet.'
        ], [], []);
        $controller->handle($request, 'app');
    }

    public function testHandleValidationFailureSubject() {
        $controller = new EmailController(new NullLogger(), $this->mock, new Config());

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Value was lower than minimum bounds.');

        $request = ServerRequestFactory::fromGlobals([], [], [
            'to' => 'local@local',
            'subject' => '',
            'body' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.' .
                'Morbi nec orci lobortis, maximus sem et, bibendum ex. Maecenas sit amet mattis nisl. In amet.'
        ], [], []);
        $controller->handle($request, 'app');
    }

    public function testHandleValidationFailureBody() {
        $controller = new EmailController(new NullLogger(), $this->mock, new Config());

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Value was lower than minimum bounds.');

        $request = ServerRequestFactory::fromGlobals([], [], [
            'to' => 'local@local',
            'subject' => 'abc',
            'body' => 'Lorem ipsum dolor sit amet'
        ], [], []);
        $controller->handle($request, 'app');
    }

    public function testHandleExceptionOnSend() {
        $this->mock->method('send')->willThrowException(new Exception('ARRGGHHH!!!'));
        $controller = new EmailController(new NullLogger(), $this->mock, new Config());

        $this->expectException(HttpInternalServerErrorException::class);
        $this->expectExceptionMessage('Could not successfully send email. Please contact administrator.');

        $request = ServerRequestFactory::fromGlobals([], [], [
            'to' => 'local@local',
            'subject' => 'abc123',
            'body' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.' .
                'Morbi nec orci lobortis, maximus sem et, bibendum ex. Maecenas sit amet mattis nisl. In amet.'
        ], [], []);
        $controller->handle($request, 'app');
    }

    public function testHandleSuccess() {
        $this->mock->method('send')->willReturn(true);
        $controller = new EmailController(new NullLogger(), $this->mock, new Config());

        $request = ServerRequestFactory::fromGlobals([], [], [
            'to' => 'local@local',
            'subject' => 'abc123',
            'body' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.' .
                'Morbi nec orci lobortis, maximus sem et, bibendum ex. Maecenas sit amet mattis nisl. In amet.'
        ], [], []);
        $this->assertInstanceOf(JsonResponse::class, $controller->handle($request, 'app'));
    }

}
