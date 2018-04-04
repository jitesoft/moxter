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
use Zend\Diactoros\Request;
use Zend\Diactoros\Response\JsonResponse;

/**
 * ExceptionHandlerTest
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class ExceptionHandlerTest extends AbstractTestCase {

    public function testWithoutException() {
        $eh     = new ExceptionHandler(new NullLogger());
        $called = false;

        $eh->handle(new Request(), function() use(&$called) {
            $called = true;
            return new JsonResponse([]);
        });

        $this->assertTrue($called);
    }

    public function testWithValidationException() {
        $eh     = new ExceptionHandler(new NullLogger());
        $called = false;

        $out = $eh->handle(new Request(), function() use(&$called) {
            $called = true;
            throw new ValidationException();
        });

        $this->assertTrue($called);
        $this->assertInstanceOf(JsonResponse::class, $out);
        $this->assertEquals(400, $out->getStatusCode());
        $out->getBody()->rewind();
        $result = $out->getBody()->getContents();
        $this->assertEquals('Validation failed.', json_decode($result)->error);
    }

    public function testWithHttpException() {
        $eh     = new ExceptionHandler(new NullLogger());
        $called = false;

        $out = $eh->handle(new Request(), function() use(&$called) {
            $called = true;
            throw new HttpNotFoundException();
        });

        $this->assertTrue($called);
        $this->assertInstanceOf(JsonResponse::class, $out);
        $this->assertEquals(404, $out->getStatusCode());
        $out->getBody()->rewind();
        $result = $out->getBody()->getContents();
        $this->assertEquals('Resource not found.', json_decode($result)->error);
    }

    public function testWithStandardException() {
        $eh     = new ExceptionHandler(new NullLogger());
        $called = false;

        $out = $eh->handle(new Request(), function() use(&$called) {
            $called = true;
            throw new Exception('Argh!');
        });

        $this->assertTrue($called);
        $this->assertInstanceOf(JsonResponse::class, $out);
        $this->assertEquals(500, $out->getStatusCode());
        $out->getBody()->rewind();
        $result = $out->getBody()->getContents();
        $this->assertEquals('Unknown error.', json_decode($result)->error);
    }

}
