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
        return $app['twig']->render('Manager\dashboard.html.twig',
                array('libs' => $libs,'title' => 'Dashboard'));

    }

}
