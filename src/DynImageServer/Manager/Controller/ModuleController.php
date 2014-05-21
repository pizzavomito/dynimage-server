<?php

namespace DynImageServer\Manager\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ModuleController {

    public function moduleNewAction(Request $request, Application $app) {
        return $app['twig']->render('Manager\module_new.html.twig');
    }

    public function parseImports($file, $index = 1, $imports = array(), $from = null) {

        if (!file_exists($file)) {
            throw new \Exception("Cannot import resource. The file [$file] does not exist.");
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($file);

        if (!$xml) {
            $msg = "Failed loading XML:";
            foreach (libxml_get_errors() as $error) {
                $msg .= "\t" . $error->message;
            }

            throw new \Exception($msg);
        }


        if (is_object($xml->imports->import)) {
            if (is_null($from)) {
                $from = $index;
            } else {
                $from .= "." . ($index - 1);
            }
            foreach ($xml->imports->import as $import) {
                $attributes = $import->attributes();


                $imports[$from][] = array('fullname' => realpath(dirname($file) . '/' . $attributes['resource']),
                    'name' => basename($attributes['resource']),
                    'value' => (string) $attributes['resource'],
                    'from' => $file);


                $imports = self::parseImports(dirname($file) . '/' . $attributes['resource'], $index, $imports, $from);

                $index++;
            }
        }

        return $imports;
    }

    public function getResources($file, $cacheDir) {

        $filename = $cacheDir . '/' . md5($file);
        $imports = array();
        try {
            $imports = self::parseImports($file);

            file_put_contents($filename, serialize($imports));
        } catch (\Exception $e) {
            if (file_exists($filename)) {
                $imports = unserialize(file_get_contents($filename));
            }
        }


        return $imports;
    }

   

    /**
     * 
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Silex\Application $app
     * @param String $package
     * @param String $module
     * @param String $from
     * @param String $key
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function moduleEditAction(Request $request, Application $app, $package, $module, $from = null, $key = null) {
        $error = false;
        $resources = array();
        try {

            $moduleFile = $app['package.service']->getModuleFilename($package, $module);
            
            $resources = self::getResources($moduleFile, $app['dynimage_manager.cache_dir']);
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $app['session']->getFlashBag()->add('danger', $e->getMessage());
        }

        

        if (!is_null($from) && !empty($resources)) {
            $moduleContent = file_get_contents($resources[$from][$key]['fullname']);
            $file = $resources[$from][$key]['fullname'];
        } else {
            $moduleContent = file_get_contents($moduleFile);
            $file = $moduleFile;
        }

        $writable = false;
        if (is_writable($file) && is_writable(dirname($file))) {
            $writable = true;
        }

        $perms = substr(sprintf('%o', fileperms($file)), -4);

        return $app['twig']->render('Manager\module_edit.html.twig', array(
                    'error' => $error,
                    'current_file' => realpath($file),
                    'module_file' => array('fullname' => realpath($moduleFile), 'name' => basename($moduleFile)),
                    'resources' => $resources,
                    'package' => $package,
                    'module' => $module,
                    'writable' => $writable,
                    'perms' => $perms,
                    'editor_content' => $moduleContent,
                    'url_display_loaded' => $app['url_generator']->generate('module.loaded', array('package' => $package, 'module' => $module)),
                    'url_deploy' => $app['url_generator']->generate('module.deploy', array('package' => $package, 'module' => $module)),
                    'url_test' => $app['url_generator']->generate('module.test', array('package' => $package, 'module' => $module, 'from' => $from, 'key' => $key)),
                    'title' => $package . "/" . $module));
    }

    /**
     *
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @param  \Silex\Application                        $app
     * @param  type                                      $package
     * @param  type                                      $module
     * @return type
     */
    public function moduleLoadedAction(Request $request, Application $app, $package, $module) {
        $error = false;
        $moduleLoaded = array();
        $filters = array();
        $driver = null;
        try {
            $moduleFilename = $app['package.service']->getModuleFilename($package, $module);
            $app['module.service']->cacheDir = $app['dynimage_manager.cache_dir'].'/'.$app['env'];
            $app['module.service']->debug = true;
            $moduleLoaded = $app['module.service']->getModule($moduleFilename);

            $dynimage = $moduleLoaded->get('dynimage');


            foreach ($dynimage->getFilters() as $filter) {

                $filters[get_class($filter)] = $filter->getArguments();
               
            }

            $dynimage->setDriver($moduleLoaded->getParameter('dynimage.driver'));
            $driver = $dynimage->getDriver();
        } catch (\Exception $e) {

            $error = $e->getMessage();
  
        }

        return $app['twig']->render('Manager\module_loaded.html.twig', array('error' => $error,
                    'module_loaded' => $moduleLoaded,
                    'filters' => $filters,
                    'driver' => $driver,
                    'title' => 'Packages'));
    }

    /**
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @param  \Silex\Application                         $app
     * @param  type                                       $package
     * @param  type                                       $module
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function moduleTestAction(Request $request, Application $app, $package, $module, $from = null, $key = null) {
        $msg = 'Response: success';
        $moduleLoaded = array();


        try {

            
            $moduleFilename = $app['package.service']->getModuleFilename($package, $module);

            if (!is_null($from)) {
                $resources = self::getResources($moduleFilename, $app['dynimage_manager.cache_dir']);
                $file = $resources[$from][$key]['fullname'];

            } else {
                $moduleContent = file_get_contents($moduleFilename);
                $file = $moduleFilename;
            }
        } catch (\Exception $e) {

            $error = $e->getMessage();
        }


        file_put_contents($file, $request->request->get('contents'));
        try {

            $app['module.service']->cacheDir = $app['dynimage_manager.cache_dir'].'/'.$app['env'];
            $app['module.service']->compile($moduleFilename,$package);
        } catch (\Exception $e) {
            $msg = $e->getMessage();

            return new \Symfony\Component\HttpFoundation\Response($msg, 500);
        }

        return new \Symfony\Component\HttpFoundation\Response($msg);
    }

    /**
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @param  \Silex\Application                         $app
     * @param  type                                       $package
     * @param  type                                       $module
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function moduleDeployAction(Request $request, Application $app, $package, $module) {
        $msg = 'Response: success';
        //$moduleLoaded = array();

        $moduleFilename = $app['package.service']->getModuleFilename($package, $module);
        

        try {
            //touch($moduleFilename);
            //$app['module.service']->cacheDir = $app['dynimage.cache_dir'].'/'.$app['env'];
            //$app['module.service']->debug = true;
            $app['module.service']->compile($moduleFilename,$package);
        } catch (\Exception $e) {
            $msg = $e->getMessage();

            return new \Symfony\Component\HttpFoundation\Response($msg, 500);
        }

        return new \Symfony\Component\HttpFoundation\Response($msg);
    }

   

}
