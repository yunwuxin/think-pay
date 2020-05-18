<?php

namespace yunwuxin\pay;

use think\Route;

class Service extends \think\Service
{
    public function boot()
    {
        if ($group = $this->app->config->get('pay.route')) {
            $this->registerRoutes(function (Route $route) use ($group) {
                if (is_string($group)) {
                    $rule = "{$group}/pay/:channel/notify";
                } else {
                    $rule = "pay/:channel/notify";
                }
                $route->any($rule, '\\yunwuxin\\pay\\NotifyController@index')
                    ->completeMatch()
                    ->name('PAY_NOTIFY');
            });
        }
    }
}
