<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  AbstractTestCase.php - Part of the moxter project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Jitesoft\Moxter\Tests;

use Closure;
use Jitesoft\Container\Container;
use Jitesoft\Log\NullLogger;
use Jitesoft\Moxter\Config\Config;
use Jitesoft\Moxter\Contracts\ConfigInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Zend\Diactoros\Response\JsonResponse;

class TestRequestHandler implements RequestHandlerInterface {
    public bool $called;
    private ?Closure $callback;

    public function __construct(?Closure $callback = null) {
        $this->callback = $callback;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $this->called = true;
        return $this->callback ? call_user_func($this->callback, $request) : new JsonResponse([]);
    }
}

/**
 * AbstractTestCase
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class AbstractTestCase extends TestCase {

    /** @var ContainerInterface */
    protected $container;

    protected function setUp(): void {
        parent::setUp();

        $this->container = new Container();
        // Set up bindings.
        $this->container->set(ConfigInterface::class, Config::class, true);
        $this->container->set(LoggerInterface::class, NullLogger::class, true);
    }

    protected function tearDown(): void {
        parent::tearDown();
        $this->container->clear();
    }

    protected function  createRequestHandler(?Closure $callback = null): TestRequestHandler {
        return new TestRequestHandler($callback);
    }

}
