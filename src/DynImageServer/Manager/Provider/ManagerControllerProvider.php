<?php

namespace DynImageServer\Manager\Provider;

use Silex\Application;
use Silex\ControllerProviderInterface;

class ManagerControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->match('/', 'DynImageServer\Manager\Controller\ManagerController::dashboardAction')
                ->bind('dashboard');

        //Packages
        $controllers->match('/packages/manager', 'DynImageServer\Manager\Controller\PackageController::packagesManagerAction')
                ->bind('packages.manager');

        $controllers->match('/packages/menu', 'DynImageServer\Manager\Controller\PackageController::packagesMenuAction')
                ->bind('packages.menu');

        $controllers->match('/package/edit/{packageKey}', 'DynImageServer\Manager\Controller\PackageController::packageEditAction')
                ->bind('package.edit');

        $controllers->match('/packager/loaded', 'DynImageServer\Manager\Controller\PackageController::packagerLoadedAction')
                ->bind('packager.loaded');

        $controllers->match('/packager/save', 'DynImageServer\Manager\Controller\PackageController::packagerSaveAction')
                ->bind('packager.save');

        $controllers->match('/packager/test', 'DynImageServer\Manager\Controller\PackageController::packagerTestAction')
                ->bind('packager.test');

        // Modules
        $controllers->match('/module/edit/{package}/{module}', 'DynImageServer\Manager\Controller\ModuleController::moduleEditAction')
                ->bind('module.edit');

        $controllers->match('/module/manager/{package}/{module}', 'DynImageServer\Manager\Controller\ModuleController::moduleManagerAction')
                ->bind('module.manager');

        $controllers->match('/module/loaded/{package}/{module}', 'DynImageServer\Manager\Controller\ModuleController::moduleLoadedAction')
                ->bind('module.loaded');

        $controllers->match('/module/test/{package}/{module}', 'DynImageServer\Manager\Controller\ModuleController::moduleTestAction')
                ->bind('module.test');

        $controllers->match('/module/save/{package}/{module}', 'DynImageServer\Manager\Controller\ModuleController::moduleSaveAction')
                ->bind('module.save');

        $controllers->match('/module/new', 'DynImageServer\Manager\Controller\ModuleController::moduleNewAction')
                ->bind('module.new');

        return $controllers;
    }

}
