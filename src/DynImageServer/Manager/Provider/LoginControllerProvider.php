<?php

namespace DynImageServer\Manager\Provider;

use Silex\Application;
use Silex\ControllerProviderInterface;

class LoginControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->match('/', 'DynImageServer\Manager\Controller\LoginController::loginAction')
                ->bind('manager.login');

        $controllers->match('/logout', 'DynImageServer\Manager\Controller\LoginController::logoutAction')
                ->bind('manager.logout');

        return $controllers;
    }

}
