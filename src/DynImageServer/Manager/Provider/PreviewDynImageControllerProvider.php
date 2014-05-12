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

            $app['packager.service']->debug = true;


            $moduleFile = $app['packager.service']->getModuleFilename($package, $module);

            $filetmp = dirname($moduleFile) . '/' . $package.$module . '.module.tmp.' . $app['session']->getId();
            if (!file_exists($filetmp)) {
                $filetmp = $moduleFile;
            }
           
            $app['module.service']->cacheDir = $app['dynimage_manager.cache_dir'];
            $app['module.service']->debug = true;
            $module = $app['module.service']->getModule($filetmp,$package);
            

            $imageFilename = $module->getParameter('dynimage.image_test');

            if ($module->hasParameter('image')) {

                $imageFilename = $module->getParameter('image');
            }
             $app['monolog']->addDebug("image : $imageFilename");
            if (!file_exists($imageFilename) || !is_file($imageFilename)) {
                throw new NotFoundHttpException();
            }

            $dynimage = $module->get('dynimage');
            
            $app['dynimage.imagefilename'] = $imageFilename;
            $app['dynimage.image'] = $dynimage->apply( 
                    file_get_contents($imageFilename), 
                    $module->getParameter('dynimage.driver'), 
                    $module->getParameterBag()->all());
        });

        $controllers->after('DynImageSilex\Controller::terminateAction');

        $controllers->get('/{package}/{module}', function (Request $request) use ($app) {
            return new Response;
        })->bind('preview.dynimage');

        return $controllers;
    }

}
