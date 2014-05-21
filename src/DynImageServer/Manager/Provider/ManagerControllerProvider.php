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
        
        $controllers->match('/configuration', 'DynImageServer\Manager\Controller\ManagerController::configurationAction')
                ->bind('configuration');

        //Packages
        $controllers->match('/package/edit/{packageFile}', 'DynImageServer\Manager\Controller\PackageController::packageEditAction')
                ->bind('package.edit');
        
        $controllers->match('/packages/manager', 'DynImageServer\Manager\Controller\PackageController::packagesManagerAction')
                ->bind('packages.manager');

        $controllers->match('/packages/menu', 'DynImageServer\Manager\Controller\PackageController::packagesMenuAction')
                ->bind('packages.menu');

        $controllers->match('/package/show/{packageKey}', 'DynImageServer\Manager\Controller\PackageController::packageShowAction')
                ->bind('package.show');

        $controllers->match('/package/loaded', 'DynImageServer\Manager\Controller\PackageController::packageContainerLoadedAction')
                ->bind('package.loaded');

        $controllers->match('/package/deploy', 'DynImageServer\Manager\Controller\PackageController::packageDeployAction')
                ->bind('package.deploy');
        
        $controllers->match('/package/deployandreload', 'DynImageServer\Manager\Controller\PackageController::packageDeployReloadAction')
                ->bind('package.deployandreload');
        
        $controllers->match('/package/new', 'DynImageServer\Manager\Controller\PackageController::packageNewAction')
                ->bind('package.new');

        $controllers->match('/package/test/{packageFile}', 'DynImageServer\Manager\Controller\PackageController::packageTestAction')
                ->bind('package.test');

        
        
        
        
     

        $controllers->match('/module/edit/{package}/{module}/{from}/{key}', 'DynImageServer\Manager\Controller\ModuleController::moduleEditAction')
                ->value('from',null)
                ->value('key',null)
                ->bind('module.edit');

        $controllers->match('/module/loaded/{package}/{module}', 'DynImageServer\Manager\Controller\ModuleController::moduleLoadedAction')
                ->bind('module.loaded');

        $controllers->match('/module/test/{package}/{module}/{from}/{key}', 'DynImageServer\Manager\Controller\ModuleController::moduleTestAction')
                ->value('from',null)
                ->value('key',null)
                ->bind('module.test');

        $controllers->match('/module/deploy/{package}/{module}', 'DynImageServer\Manager\Controller\ModuleController::moduleDeployAction')
            
                ->bind('module.deploy');

        $controllers->match('/module/new', 'DynImageServer\Manager\Controller\ModuleController::moduleNewAction')
                ->bind('module.new');

        return $controllers;
    }

}
