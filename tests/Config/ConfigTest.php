<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  ConfigTest.php - Part of the moxter project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Jitesoft\Moxter\Tests\Config;

use Jitesoft\Moxter\Contracts\ConfigInterface;
use Jitesoft\Moxter\Tests\AbstractTestCase;

/**
 * ConfigTest
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class ConfigTest extends AbstractTestCase {

    /** @var ConfigInterface */
    private $config;

    protected function setUp() {
        parent::setUp();
        $this->config = $this->container->get(ConfigInterface::class);
    }

    public function testGetReturnDefault() {
        $this->assertEquals('HI!', $this->config->get('abc', 'HI!'));
    }

    public function testGetReturnNoneDefault() {
        $_ENV['abc'] = 'Wee!';
        $this->assertEquals('Wee!', $this->config->get('abc', 'HI!'));
        unset($_ENV['abc']);
    }

}
