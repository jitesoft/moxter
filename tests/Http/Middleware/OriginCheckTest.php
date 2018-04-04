<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  OriginCheckTest.php - Part of the moxter project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Jitesoft\Moxter\Tests\Http\Middleware;

use Jitesoft\Exceptions\Http\Client\HttpUnauthorizedException;
use Jitesoft\Log\NullLogger;
use Jitesoft\Log\StdLogger;
use Jitesoft\Moxter\Contracts\ConfigInterface;
use Jitesoft\Moxter\Http\Middleware\OriginCheck;
use Jitesoft\Moxter\Tests\AbstractTestCase;
use Psr\Log\LoggerInterface;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

/**
 * OriginCheckTest
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class OriginCheckTest extends AbstractTestCase {

    public function testIsDevelopment() {
        $mockConfig = $this->createMock(ConfigInterface::class);
        $mockConfig
            ->method('get')
            ->willReturn('development', '*');

        $mockLogger = $this->createMock(LoggerInterface::class);
        $mockLogger->method('debug')->with('Is development, ignoring origin constraints.');

        $middleware = new OriginCheck($mockConfig, $mockLogger);
        $called = false;
        $middleware->handle(new Request(), function() use (&$called) {
            $called = true;
            return new JsonResponse([]);
        });

        $this->assertTrue($called);
    }

    public function testProductionInvalidOrigin() {
        $mockConfig = $this->createMock(ConfigInterface::class);
        $mockConfig
            ->method('get')
            ->willReturn('production', '/https:\/\/(.*\.)?(example)(\.(com))/');

        $middleware = new OriginCheck($mockConfig, new NullLogger());

        $this->expectException(HttpUnauthorizedException::class);

        $middleware->handle(new ServerRequest([
            'HTTP_ORIGIN' => 'https://example.se'
        ]), function() {});
    }

    public function testProductionValidOrigin() {
        $mockConfig = $this->createMock(ConfigInterface::class);
        $mockConfig
            ->method('get')
            ->willReturn('production', '/https:\/\/(.*\.)?(example)(\.(com))/');

        $middleware = new OriginCheck($mockConfig, new NullLogger());

        $called = false;
        $result = $middleware->handle(new ServerRequest([
            'HTTP_ORIGIN' => 'https://example.com'
        ]), function() use(&$called) {
            $called = true;
            return new JsonResponse([]);
        });

        $this->assertTrue($called);
        $this->assertEquals('https://example.com', $result->getHeader('Access-Control-Allow-Origin')[0]);
    }

}
