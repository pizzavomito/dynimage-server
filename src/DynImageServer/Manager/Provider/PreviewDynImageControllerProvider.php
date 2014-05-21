<?php

namespace DynImageServer\Manager\Provider;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use DynImage\DynImage;

/**
 * Le controller du dynimage
 */
class PreviewDynImageControllerProvider implements ControllerProviderInterface {

    public function connect(Application $app) {
        $controllers = $app['controllers_factory'];

        $controllers->before(function (Request $request) use ($app) {

            
            $package = $request->attributes->get('package');
            $module = $request->attributes->get('module');

            $app['package.service']->debug = true;


            $moduleFile = $app['package.service']->getModuleFilename($package, $module);

            

            $app['module.service']->cacheDir = $app['dynimage_manager.cache_dir'];
            $app['module.service']->debug = true;
            $module = $app['module.service']->getModule($moduleFile, $package);


            $imageFilename = $module->getParameter('dynimage.image_test');

            
            if ($module->hasParameter('image')) {

                $imageFilename = $module->getParameter('image');
            }
            $app['monolog']->addDebug("image : $imageFilename");
            if (!file_exists($imageFilename) || !is_file($imageFilename)) {
                throw new NotFoundHttpException();
            }

            $app['dynimage.imagefilename'] = $imageFilename;
 
        });

        $controllers->after('DynImageSilex\Controller::terminateAction');

        $controllers->get('/{package}/{module}', function (Request $request) use ($app) {
            return new Response;
        })->bind('preview.dynimage');

        return $controllers;
    }

}
