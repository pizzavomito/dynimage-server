<?php

namespace DynImageServer\Manager\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class LoginController
{
    public function loginAction(Request $request, Application $app)
    {
        return $app['twig']->render('Manager\login.html.twig', array(
                    //'form' => $form->createView(),
                    'error' => $app['security.last_error']($request),
            'last_username' => $app['session']->get('_security.last_username'),
        ));
    }
    
    public function logoutAction(Request $request, Application $app)
    {
        return $app['twig']->render('Manager\login.html.twig', array(
                    //'form' => $form->createView(),
                    'error' => $app['security.last_error']($request),
            'last_username' => $app['session']->get('_security.last_username'),
        ));
    }

}
