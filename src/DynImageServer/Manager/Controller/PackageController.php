<?php

namespace DynImageServer\Manager\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class PackageController {

    public function isWritable($file) {
        $writable = false;
        if (is_writable($file)) {
            $writable = true;
        }

        return $writable;
    }

    /**
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Silex\Application $app
     * @param type $packageFile
     * @return type
     */
    public function packageEditAction(Request $request, Application $app, $packageFile) {
        //$pl = $app['package.service']->getContainer()->getParameter('dynimage.packages_files');
        $app['package.service']->cacheDir = $app['dynimage_manager.cache_dir'];
        $app['package.service']->debug = true;
        $packagesFilename = $app['package.service']->getPackagesFilename();

        $packageContent = file_get_contents($app['dynimage.packages_dir'] . '/' . $packageFile);

        $perms = substr(sprintf('%o', fileperms($app['dynimage.packages_dir'] . '/' . $packageFile)), -4);

        return $app['twig']->render('Manager\package_edit.html.twig', array(
                    'writable' => is_writable($app['dynimage.packages_dir'] . '/' . $packageFile),
                    'current_file' => $packageFile,
                    'packages_filename' => $packagesFilename,
                    'perms' => $perms,
                    'editor_content' => $packageContent,
                    'url_display_loaded' => $app['url_generator']->generate('package.loaded'),
                    'url_deploy' => $app['url_generator']->generate('package.deploy'),
                    'url_test' => $app['url_generator']->generate('package.test', array('packageFile' => $packageFile)),
                    'title' => $packageFile));
    }

    /** /
      public function packagesManagerAction(Request $request, Application $app)
      {
      $packagerContent = file_get_contents(->file);

      $perms = substr(sprintf('%o', fileperms($app['dynimage.packager_file'])), -4);

      return $app['twig']->render('Manager\packages_manager.html.twig', array('writable' => ->isWritable(),
      'perms' => $perms,
      'editor_content' => $packagerContent,
      'url_display_loaded' => $app['url_generator']->generate('packager.loaded'),
      'url_save' => $app['url_generator']->generate('packager.save'),
      'url_test' => $app['url_generator']->generate('packager.test'),
      'title' => 'Packages'));
      }
      /* */
    public function packageShowAction(Request $request, Application $app, $packageKey) {
        $error = false;
        $packagerLoaded = array();
        try {
            $app['package.service']->cacheDir = $app['dynimage_manager.cache_dir'];
            $app['package.service']->debug = true;

            $packages = $app['package.service']->getPackages();

            $package = $packages[$packageKey];
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        return $app['twig']->render('Manager\package_show.html.twig', array('error' => $error,
                    'package' => $package,
                    'package_key' => $packageKey,
                    'title' => $packageKey));
    }

    /**
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Silex\Application $app
     * @return type
     */
    public function packageDeployReloadAction(Request $request, Application $app) {
        $referer = $request->headers->get('referer');
        try {

            $app['package.service']->compile($app['dynimage_manager.cache_dir'] . '/' . $app['env'], 'testPackageContainer');

            $packages = $app['package.service']->getPackages();
            /**/
            foreach ($packages as $key => $package) {
                $modules = $package->getModules();
                
                foreach ($modules as $module) {
                    try {
                        $app['module.service']->compile($module,$key);
                    } catch (\Exception $e) {
                        $app['session']->getFlashBag()->add('danger', 'Deploy module failed:' . $e->getMessage());
                        
                    }
                    
                    
                }
            }
            /**/
            $app['package.service']->compile($app['dynimage.cache_dir'] . '/' . $app['env']);
        } catch (\Exception $e) {

            $app['session']->getFlashBag()->add('danger', 'Deploy failed:' . $e->getMessage());
            return $app->redirect($referer);
        }

        $app['session']->getFlashBag()->add('warning', 'Deploy successful');


        return $app->redirect($referer);
    }

    /**
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Silex\Application $app
     * @return type
     */
    public function packageNewAction(Request $request, Application $app) {


        $newPackage = $request->request->get('new_package');
        $ext = pathinfo($newPackage, PATHINFO_EXTENSION);
        if ($ext != 'xml') {
            $newPackage .= '.xml';
        }
        $referer = $request->headers->get('referer');
        if (file_exists($app['dynimage.packages_dir'] . '/' . $newPackage)) {
            $app['session']->getFlashBag()->add('danger', 'New package failed. The file ' . $newPackage . ' already exist.');
            return $app->redirect($referer);
        }
        if (!is_writable($app['dynimage.packages_dir'])) {
            $app['session']->getFlashBag()->add('danger', 'New package failed. Permission denied.');
            return $app->redirect($referer);
        }
        $tpl = file_get_contents(__DIR__ . '/../../../../resources/dynimage/packages/new_package.tpl.xml');
        if (file_put_contents($app['dynimage.packages_dir'] . '/' . $newPackage, $tpl) === false) {
            $app['session']->getFlashBag()->add('danger', 'New package failed');
        } else {
            $app['session']->getFlashBag()->add('success', 'New package created');
        }

        return $app->redirect($referer);
    }

    /**
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Silex\Application $app
     * @param type $packageFile
     * @return type
     */
    public function packageDeleteAction(Request $request, Application $app, $packageFile) {

        $referer = $request->headers->get('referer');

        unlink($app['dynimage.packages_dir'] . '/' . $packageFile);

        $app['package.service']->compile($app['dynimage.cache_dir'] . '/' . $app['env']);

        return $app->redirect($referer);
    }

    /**
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Silex\Application $app
     * @return type
     */
    public function packagesMenuAction(Request $request, Application $app) {
        $error = false;

        try {

            $packages = $app['package.service']->getPackages();
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        return $app['twig']->render('Manager\menu_packages.html.twig', array('error' => $error, 'packages' => $packages));
    }

    /**
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Silex\Application $app
     * @return type
     */
    public function packageContainerLoadedAction(Request $request, Application $app) {
        $error = false;
        $packagerLoaded = array();
        try {
            $app['package.service']->cacheDir = $app['dynimage_manager.cache_dir'] . '/' . $app['env'];

            $packagerLoaded = $app['package.service']->getContainer(true);
            $app['package.service']->getPackages();
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        return $app['twig']->render('Manager\package_container_loaded.html.twig', array('error' => $error,
                    'packager_loaded' => $packagerLoaded,
                    'title' => 'Packages'));
    }

    /**
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Silex\Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function packageTestAction(Request $request, Application $app, $packageFile) {
        $msg = 'Response: success';

        try {
            file_put_contents($app['dynimage.packages_dir'] . '/' . $packageFile, $request->request->get('contents'));

            $app['package.service']->compile($app['dynimage_manager.cache_dir'] . '/' . $app['env'], 'testPackageContainer');

            $app['package.service']->getPackages();

            //$app['package.service']->compile($app['dynimage_manager.cache_dir'].'/'.$app['env']);
        } catch (\Exception $e) {

            return new \Symfony\Component\HttpFoundation\Response($e->getMessage(), 500);
        }
        if ($app['package.service']->getContainer()->hasParameter('dynimage.packages_unloaded')) {
            $pu = $app['package.service']->getContainer()->getParameter('dynimage.packages_unloaded');

            $msg = 'Error :';
            foreach ($pu as $p) {
                $msg .= $p;
            }
        }
        return new \Symfony\Component\HttpFoundation\Response($msg);
    }

    /**
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Silex\Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function packageDeployAction(Request $request, Application $app) {
        $msg = 'Response: success';



        try {

           
            $app['package.service']->compile($app['dynimage_manager.cache_dir'] . '/' . $app['env'], 'testPackageContainer');

            $app['package.service']->getPackages();

            $app['package.service']->compile($app['dynimage.cache_dir'] . '/' . $app['env']);
        } catch (\Exception $e) {
            $msg = $e->getMessage();

            return new \Symfony\Component\HttpFoundation\Response($msg, 500);
        }

        return new \Symfony\Component\HttpFoundation\Response($msg);
    }

}
