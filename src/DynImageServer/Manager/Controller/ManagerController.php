<?php

namespace DynImageServer\Manager\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class ManagerController
{
    /** /
    public function loginAction(Request $request, Application $app)
    {
        $form = $app['form.factory']->createBuilder('form')
                ->add('username', 'text', array('label' => 'Username', 'data' => $app['session']->get('_security.last_username')))
                ->add('password', 'password', array('label' => 'Password'))
                ->getForm()
        ;

        return $app['twig']->render('Manager\login.html.twig', array(
                    'form' => $form->createView(),
                    'error' => $app['security.last_error']($request),
        ));
    }
    /**/
    public function dashboardAction(Request $request, Application $app)
    {
        $error=false;
        $packages = array();
        try {

            $packages = $app['package.service']->getPackages();
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }
        
        return $app['twig']->render('Manager\dashboard.html.twig',
                array('error' => $error,'packages' => $packages, 'title' => 'Dashboard'));

    }
    
    public function configurationAction(Request $request, Application $app)
    {
        
        
        $libs = array();
        $libs['Gmagick'] = false;
        $libs['Imagick'] = false;
        $libs['Gd'] = false;
        if (class_exists('\Gmagick')) {
            $libs['Gmagick'] = true;
        } 
        if (class_exists('\Imagick')) {
            $libs['Imagick'] = true;
        } 
        if (extension_loaded('gd') && function_exists('gd_info')) {
            $libs['Gd'] = true;
        }
        
        $packages_unloaded = array();
        $packages_loaded = array();
        $packages_available = array();
        $packages_ids = array();
        
        try {
            $packages = $app['package.service']->getPackages();
            $container = $app['package.service']->getContainer();
        }
        catch (\Exception $e) {
            echo $e->getMessage();
        }
        
        
       
  
        
        if ($container->hasParameter('dynimage.packages_loaded')) {
             $packages_loaded = $container->getParameter('dynimage.packages_loaded');
        }
        
        if ($container->hasParameter('dynimage.packages_unloaded')) {
             $packages_unloaded = $container->getParameter('dynimage.packages_unloaded');
        }
  
        if ($container->hasParameter('dynimage.packages_ids')) {
             $packages_ids = $container->getParameter('dynimage.packages_ids');
        }
        
        foreach(glob($app['dynimage.packages_dir']."*.xml") as $filename) {
            $packages_available[] = basename($filename);
        }
       
        
        return $app['twig']->render('Manager\configuration.html.twig',
                array(
                    'packages_unloaded' => $packages_unloaded,
                    'packages_loaded' => $packages_loaded,
                    'packages_available' => $packages_available,
                    'packages_ids' => $packages_ids,
                    'libs' => $libs,
                    'packages' => $packages,
                    'title' => 'Configuration'
                    )
                );

    }

}
