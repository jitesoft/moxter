<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  ExceptionHandlerTest.php - Part of the moxter project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Jitesoft\Moxter\Tests\Http\Middleware;

use Exception;
use Jitesoft\Exceptions\Http\Client\HttpNotFoundException;
use Jitesoft\Exceptions\Validation\ValidationException;
use Jitesoft\Log\NullLogger;
use Jitesoft\Moxter\Http\Middleware\ExceptionHandler;
use Jitesoft\Moxter\Tests\AbstractTestCase;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

/**
 * ExceptionHandlerTest
 *
 * @author  Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class ExceptionHandlerTest extends AbstractTestCase {

    public function testWithoutException(): void {
        $eh = new ExceptionHandler(new NullLogger());
        $callback = $this->createRequestHandler();
        $eh->process(new ServerRequest(), $callback);
        $this->assertTrue($callback->called);
    }

    public function testWithValidationException(): void {
        $eh = new ExceptionHandler(new NullLogger());
        $callback = $this->createRequestHandler(function () {
            throw new ValidationException();
        });

        $out = $eh->process(new ServerRequest(), $callback);
        $this->assertTrue($callback->called);
        $this->assertInstanceOf(JsonResponse::class, $out);
        $this->assertEquals(400, $out->getStatusCode());
        $out->getBody()->rewind();
        $result = $out->getBody()->getContents();
        $this->assertEquals('Validation failed.', json_decode($result)->error);
    }

    public function testWithHttpException(): void {
        $eh = new ExceptionHandler(new NullLogger());
        $callback = $this->createRequestHandler(function () {
            throw new HttpNotFoundException();
        });

        $out = $eh->process(new ServerRequest(), $callback);
        $this->assertTrue($callback->called);
        $this->assertInstanceOf(JsonResponse::class, $out);
        $this->assertEquals(404, $out->getStatusCode());
        $out->getBody()->rewind();
        $result = $out->getBody()->getContents();
        $this->assertEquals('Resource not found.', json_decode($result)->error);
    }

    public function testWithStandardException(): void {
        $eh = new ExceptionHandler(new NullLogger());
        $callback = $this->createRequestHandler(static function () {
            throw new Exception('Argh!');
        });

        $out = $eh->process(new ServerRequest(), $callback);
        $this->assertTrue($callback->called);
        $this->assertInstanceOf(JsonResponse::class, $out);
        $this->assertEquals(500, $out->getStatusCode());
        $out->getBody()->rewind();
        $result = $out->getBody()->getContents();
        $this->assertEquals('Unknown error.', json_decode($result)->error);
    }

}
