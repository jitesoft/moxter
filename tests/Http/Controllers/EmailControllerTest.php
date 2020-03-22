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

    protected function setUp(): void {
        parent::setUp();

        $this->mock = $this->createMock(EmailServiceInterface::class);
    }

    public function testHandleValidationFailureTo(): void {
        $controller = new EmailController(new NullLogger(), $this->mock, new Config());

        $request = ServerRequestFactory::fromGlobals([], [], [
            'to' => 'argh!',
            'subject' => 'abc123',
            'body' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.' .
                      'Morbi nec orci lobortis, maximus sem et, bibendum ex. Maecenas sit amet mattis nisl. In amet.'
        ], [], []);
        $response = $controller->handle($request, 'app');

        $this->assertEquals([
            'to' => [
                'email' => 'argh! is not a valid email address.'
            ]
        ], json_decode($response->getBody()->getContents(), true));

    }

    public function testHandleValidationFailureSubject(): void {
        $controller = new EmailController(new NullLogger(), $this->mock, new Config());

        $request = ServerRequestFactory::fromGlobals([], [], [
            'to' => 'local@local',
            'subject' => '',
            'body' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.' .
                'Morbi nec orci lobortis, maximus sem et, bibendum ex. Maecenas sit amet mattis nisl. In amet.'
        ], [], []);
        $response = $controller->handle($request, 'app');

        $this->assertEquals([
            'subject' => [
                'min' => 'Value was lower than minimum bounds.'
            ]
        ], json_decode($response->getBody()->getContents(), true));
    }

    public function testHandleValidationFailureBody(): void {
        $controller = new EmailController(new NullLogger(), $this->mock, new Config());

        $request = ServerRequestFactory::fromGlobals([], [], [
            'to' => 'local@local',
            'subject' => 'abc',
            'body' => 'Lorem ipsum dolor sit amet'
        ], [], []);
        $response = $controller->handle($request, 'app');

        $this->assertEquals([
            'body' => [
                'min' => 'Value was lower than minimum bounds.'
            ]
        ], json_decode($response->getBody()->getContents(), true));
    }

    public function testHandleExceptionOnSend(): void {
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

    public function testHandleSuccess(): void {
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
