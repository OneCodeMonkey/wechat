<?php

/*
 * This file is part of the overtrue/wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * ServiceProvider.php.
 *
 * This file is part of the wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace EasyWeChat\Applications\OfficialAccount\OAuth;

use Overtrue\Socialite\SocialiteManager as Socialite;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Class ServiceProvider.
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}.
     */
    public function register(Container $container)
    {
        $container['official_account.oauth'] = function ($container) {
            $callback = $this->prepareCallbackUrl($container);
            $scopes = $container['config']->get('oauth.scopes', []);
            $socialite = (new Socialite(
                [
                    'wechat' => [
                        'client_id' => $container['config']['app_id'],
                        'client_secret' => $container['config']['secret'],
                        'redirect' => $callback,
                    ],
                ]
            ))->driver('wechat');

            if (!empty($scopes)) {
                $socialite->scopes($scopes);
            }

            return $socialite;
        };
    }

    /**
     * Prepare the OAuth callback url for wechat.
     *
     * @param Container $container
     *
     * @return string
     */
    private function prepareCallbackUrl($container)
    {
        $callback = $container['config']->get('oauth.callback');
        if (0 === stripos($callback, 'http')) {
            return $callback;
        }
        $baseUrl = $container['request']->getSchemeAndHttpHost();

        return $baseUrl.'/'.ltrim($callback, '/');
    }
}
