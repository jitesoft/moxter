<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  AbstractTestCase.php - Part of the moxter project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Jitesoft\Moxter\Tests;

use Jitesoft\Container\Container;
use Jitesoft\Log\NullLogger;
use Jitesoft\Moxter\Config\Config;
use Jitesoft\Moxter\Contracts\ConfigInterface;
use Jitesoft\Moxter\Contracts\EmailServiceInterface;
use Jitesoft\Moxter\Http\Controllers\EmailController;
use Jitesoft\Moxter\Services\EmailService;
use PHPMailer\PHPMailer\PHPMailer;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * AbstractTestCase
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class AbstractTestCase extends TestCase {

    /** @var ContainerInterface */
    protected $container;

    protected function setUp() {
        parent::setUp();

        $this->container = new Container();
        // Set up bindings.
        $this->container->set(ConfigInterface::class, Config::class, true);
        $this->container->set(LoggerInterface::class, NullLogger::class, true);
    }

    protected function tearDown() {
        parent::tearDown();
        $this->container->clear();
    }

}
