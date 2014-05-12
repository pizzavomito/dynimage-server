<?php

namespace DynImageServer\Manager\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class PackageController
{
    public function packagesManagerAction(Request $request, Application $app)
    {
        $packagerContent = file_get_contents($app['packager.service']->file);

        $perms = substr(sprintf('%o', fileperms($app['dynimage.packager_file'])), -4);

        return $app['twig']->render('Manager\packages_manager.html.twig', array('writable' => $app['packager.service']->isWritable(),
                    'perms' => $perms,
                    'editor_content' => $packagerContent,
                    'url_display_loaded' => $app['url_generator']->generate('packager.loaded'),
                    'url_save' => $app['url_generator']->generate('packager.save'),
                    'url_test' => $app['url_generator']->generate('packager.test'),
                    'title' => 'Packages'));
    }

    public function packageEditAction(Request $request, Application $app, $packageKey)
    {
        $error = false;
        $packagerLoaded = array();
        try {
            $app['packager.service']->cacheDir = $app['dynimage_manager.cache_dir'];
            $app['packager.service']->debug = true;

            $packages = $app['packager.service']->getPackages();

            $package = $packages[$packageKey];
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        return $app['twig']->render('Manager\package_edit.html.twig', array('error' => $error,
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
    public function packagesMenuAction(Request $request, Application $app)
    {
        $error = false;

        try {

            $packages = $app['packager.service']->getPackages();
        } catch (Exception $e) {
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
    public function packagerLoadedAction(Request $request, Application $app)
    {
        $error = false;
        $packagerLoaded = array();
        try {
            $app['packager.service']->cacheDir = $app['dynimage_manager.cache_dir'];
            $app['packager.service']->debug = true;

            $packagerLoaded = $app['packager.service']->getPackager();

        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        return $app['twig']->render('Manager\packager_loaded.html.twig', array('error' => $error,
                    'packager_loaded' => $packagerLoaded,
                    'title' => 'Packages'));
    }

    /**
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Silex\Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function packagerTestAction(Request $request, Application $app)
    {
        $msg = 'ok';
        $packagerLoaded = array();
        $filetmp = $app['packager.service']->getPackagerDir(). '/packager.tmp.' . $app['session']->getId();

        file_put_contents($filetmp, $request->request->get('contents'));
        try {
            $app['packager.service']->file = $filetmp;
            $app['packager.service']->cacheDir = $app['dynimage_manager.cache_dir'];
            $app['packager.service']->debug = true;
            $app['packager.service']->getPackager();

        } catch (Exception $e) {
            $msg = $e->getMessage();

            return new \Symfony\Component\HttpFoundation\Response($msg, 500);
        }

        return new \Symfony\Component\HttpFoundation\Response($msg);
    }

    /**
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Silex\Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function packagerSaveAction(Request $request, Application $app)
    {
        $msg = 'ok';
        $packagerLoaded = array();
        $filetmp = $app['packager.service']->getPackagerDir() . '/packager.tmp.' . $app['session']->getId();

        file_put_contents($filetmp, $request->request->get('contents'));
        try {
            $app['packager.service']->file = $filetmp;
            $app['packager.service']->cacheDir = $app['dynimage_manager.cache_dir'];
            $app['packager.service']->debug = true;
            $app['packager.service']->getPackager();
            if (file_put_contents($app['dynimage.packager'], $request->request->get('contents'))) {
                return new \Symfony\Component\HttpFoundation\Response($msg);
            }
        } catch (Exception $e) {
            $msg = $e->getMessage();

            return new \Symfony\Component\HttpFoundation\Response($msg, 500);
        }

        return new \Symfony\Component\HttpFoundation\Response($msg);
    }

}
