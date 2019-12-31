<?php

namespace yunwuxin\pay;

use think\Route;

class Service extends \think\Service
{
    public function boot()
    {
        if ($this->app->config->get('pay.route')) {
            $this->registerRoutes(function (Route $route) {
                $route->any("pay/:channel/notify", '\\yunwuxin\\pay\\NotifyController@index')
                    ->completeMatch()
                    ->name('PAY_NOTIFY');
            });
        }
    }
}
