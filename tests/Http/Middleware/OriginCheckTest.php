<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  OriginCheckTest.php - Part of the moxter project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Jitesoft\Moxter\Tests\Http\Middleware;

use Jitesoft\Exceptions\Http\Client\HttpUnauthorizedException;
use Jitesoft\Log\NullLogger;
use Jitesoft\Moxter\Contracts\ConfigInterface;
use Jitesoft\Moxter\Http\Middleware\OriginCheck;
use Jitesoft\Moxter\Tests\AbstractTestCase;
use Psr\Log\LoggerInterface;
use Zend\Diactoros\ServerRequest;

/**
 * OriginCheckTest
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class OriginCheckTest extends AbstractTestCase {

    public function testIsDevelopment(): void {
        $mockConfig = $this->createMock(ConfigInterface::class);
        $mockConfig
            ->method('get')
            ->willReturn('development', '*');

        $mockLogger = $this->createMock(LoggerInterface::class);
        $mockLogger->method('debug')->with('Is development, ignoring origin constraints.');

        $middleware = new OriginCheck($mockConfig, $mockLogger);
        $cb = $this->createRequestHandler();
        $middleware->process(new ServerRequest(), $cb);

        $this->assertTrue($cb->called);
    }

    public function testProductionInvalidOrigin(): void {
        $mockConfig = $this->createMock(ConfigInterface::class);
        $mockConfig
            ->method('get')
            ->willReturn('production', '/https:\/\/(.*\.)?(example)(\.(com))/');

        $middleware = new OriginCheck($mockConfig, new NullLogger());

        $this->expectException(HttpUnauthorizedException::class);

        $middleware->process(new ServerRequest([
            'HTTP_ORIGIN' => 'https://example.se'
        ]), $this->createRequestHandler());
    }

    public function testProductionValidOrigin(): void {
        $mockConfig = $this->createMock(ConfigInterface::class);
        $mockConfig
            ->method('get')
            ->willReturn('production', '/https:\/\/(.*\.)?(example)(\.(com))/');

        $middleware = new OriginCheck($mockConfig, new NullLogger());
        $cb = $this->createRequestHandler();
        $result = $middleware->process(new ServerRequest([
            'HTTP_ORIGIN' => 'https://example.com'
        ]), $cb);

        $this->assertTrue($cb->called);
        $this->assertEquals('https://example.com', $result->getHeader('Access-Control-Allow-Origin')[0]);
    }

}
