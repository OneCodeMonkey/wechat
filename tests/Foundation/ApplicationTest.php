<?php

/*
 * This file is part of the overtrue/wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace EasyWeChat\Tests\Foundation;

use EasyWeChat\Application;
use EasyWeChat\Applications\Base\Core\Http;
use EasyWeChat\Config\Repository as Config;
use EasyWeChat\Tests\TestCase;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class ApplicationTest extends TestCase
{
    /**
     * Test __construct().
     */
    public function testConstructor()
    {
        $app = new Application(['foo' => 'bar']);

        $this->assertInstanceOf(Config::class, $app['config']);

        $providers = $app->getProviders();
        foreach ($providers as $provider) {
            $container = new Container();
            $container->register(new $provider());
            $container['config'] = $app->raw('config');
            $container['request'] = $app->raw('request');
            $container['cache'] = $app->raw('cache');

            foreach ($container->keys() as $providerName) {
                $this->assertEquals($container->raw($providerName), $app->raw($providerName));
            }

            unset($container);
        }
    }

    public function testHttpDefaultOptions()
    {
        $app = new Application([]);

        $this->assertEquals(['timeout' => 5.0], Http::getDefaultOptions());

        $config = ['guzzle' => ['timeout' => 6]];
        $app = new Application($config);

        $this->assertEquals($config['guzzle'], Http::getDefaultOptions());
    }

    /**
     * test __set, __get.
     */
    public function testMagicMethod()
    {
        $app = new Application(['foo' => 'bar']);

        $app->foo = 'bar';

        // getter setter
        $this->assertEquals('bar', $app->foo);
    }

    /**
     * Test addProvider() and setProviders.
     */
    public function testProviders()
    {
        $app = new Application(['foo' => 'bar']);

        $providers = $app->getProviders();

        $app->addProvider(\Mockery::mock(ServiceProviderInterface::class));

        $this->assertCount(count($providers) + 1, $app->getProviders());

        $app->setProviders(['foo', 'bar']);

        $this->assertSame(['foo', 'bar'], $app->getProviders());
    }

    public function testSetCustomAccessToken()
    {
        $config = [
            'app_id' => 'foo',
            'secret' => 'bar',
        ];

        $app = new Application($config);

        $this->assertInstanceOf('EasyWeChat\Applications\OfficialAccount\Core\AccessToken', $app['official_account.access_token']);

        $app['official_account.access_token']->setToken('iamtokenhere');

        $this->assertSame('iamtokenhere', $app['official_account.access_token']->getToken());
    }
}
