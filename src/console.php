<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\ProgressBar;

$console = new Application('Silex - Kitchen Edition', '0.1');

$app->boot();


$console
        ->register('package:list')
        ->setDescription('Listing packages')
        ->setCode(function (InputInterface $input, OutputInterface $output) use ($app, $console) {

            $container = $app['package.service']->getContainer();
            $outFiles = array();

            foreach ($container->getParameter('dynimage.packages_loaded') as $key => $file) {
                $outFiles[] = array($file, '<info>✓</info>');
            }

            foreach ($container->getParameter('dynimage.packages_unloaded') as $file => $msg) {
                $outFiles[] = array($file, '<fg=red>✗</fg=red>', $msg);
            }

            $tableFiles = $console->getHelperSet()->get('table');


            $tableFiles
            ->setHeaders(array('File', 'Compile', 'Message'))
            ->setRows($outFiles)
            ->render($output);

            $packages = $app['package.service']->getPackages();
            $ids = $container->getParameter('dynimage.packages_ids');

            $outPackages = array();
            foreach ($packages as $keyPackage => $package) {
                $modules = $package->getModules();
                $mod = '';
                foreach ($modules as $keyMod => $module) {
                    $mod .= ' [' . $keyMod . ']';
                }

                $outPackages[] = array($keyPackage, $ids[$keyPackage], $package->isEnabled() ? '<info>✓</info>' : '<fg=red>✗</fg=red>', '(' . count($package->getModules()) . ') ' . $mod);
            }

            $tablePackages = $console->getHelperSet()->get('table');


            $tablePackages
            ->setHeaders(array('Package', 'File', 'Enabled', 'Module'))
            ->setRows($outPackages)
            ->render($output);
        })
;


$console
        ->register('package:dump')
        ->setDescription('Add a package')
        ->addArgument(
                'filename', InputArgument::REQUIRED, 'filename'
        )
        ->setCode(function (InputInterface $input, OutputInterface $output) use ($app, $console) {


            $formatter = $console->getHelperSet()->get('formatter');


            $app['package.service']->compile($app['dynimage.cache_dir'] . '/' . $app['env'], null, $input->getArgument('filename'));
            $container = $app['package.service']->getContainer();
            $outFiles = array();

            if ($container->hasParameter('dynimage.packages_loaded')) {
                foreach ($container->getParameter('dynimage.packages_loaded') as $key => $file) {
                    $outFiles[] = array($file, '<info>✓</info>');
                }
            }
            if ($container->hasParameter('dynimage.packages_unloaded')) {
                foreach ($container->getParameter('dynimage.packages_unloaded') as $file => $msg) {
                    $outFiles[] = array($file, '<fg=red>✗</fg=red>', $msg);
                }
            }
            $tableFiles = $console->getHelperSet()->get('table');


            $tableFiles
            ->setHeaders(array('File', 'Compile', 'Message'))
            ->setRows($outFiles)
            ->render($output);

            $packages = $app['package.service']->getPackages();

            $ids = $container->getParameter('dynimage.packages_ids');

            $outPackages = array();
            foreach ($packages as $keyPackage => $package) {
                $modules = $package->getModules();
                $displayPackage = '[<fg=red>✭</fg=red>] ' . $keyPackage;
                if ($package->isEnabled()) {
                    $displayPackage = '[<info>✭</info>] ' . $keyPackage;
                }
                foreach ($modules as $keyMod => $modulefilename) {

                    $displayModule = '[<fg=yellow>?</fg=yellow>] ' . $keyMod;
                    try {
                        $app['module.service']->compile($modulefilename, $keyPackage);
                        $module = $app['module.service']->getModule($modulefilename, $package);

                        if ($module->hasParameter('enabled') && !$module->getParameter('enabled')) {
                            $displayModule = '[<fg=red>✭</fg=red>] ' . $keyMod;
                        } else {
                            $displayModule = '[<info>✭</info>] ' . $keyMod;
                        }



                        $outPackages[] = array($displayPackage, $ids[$keyPackage], $displayModule, '<info>✓</info>');
                    } catch (\Exception $e) {
                        $outPackages[] = array($displayPackage, $ids[$keyPackage], $displayModule, '<fg=red>✗</fg=red>', $e->getMessage());
                    }
                }
            }
            $tablePackages = $console->getHelperSet()->get('table');

            $tablePackages
            ->setHeaders(array('Package', 'File', 'sModule', 'Compile', 'Message'))
            ->setRows($outPackages)
            ->render($output);
            $output->writeln('<info>Dump finished</info>');

            $output->writeln('<comment>Legend:</comment>');
            $output->writeln('[<fg=red>✭</fg=red>] = disabled');
            $output->writeln('[<info>✭</info>] = enabled');
            $output->writeln('[<fg=yellow>?</fg=yellow>] = unknow');

            $output->writeln('[<info>✓</info>] = success');
            $output->writeln('[<fg=red>✗</fg=red>] = error');
        })
;

$console
        ->register('module:deploy')
        ->setDescription('Deploy module')
        ->addArgument(
                'package', InputArgument::REQUIRED, 'Package name'
        )
        ->addArgument(
                'module', InputArgument::REQUIRED, 'Module name'
        )
        ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
            $moduleFilename = $app['package.service']->getModuleFilename($input->getArgument('package'), $input->getArgument('module'));


            try {

                $app['module.service']->compile($moduleFilename, $input->getArgument('package'));

                $moduleLoaded = $app['module.service']->getModule($moduleFilename);

                $dynimage = $moduleLoaded->get('dynimage');


                foreach ($dynimage->getFilters() as $filter) {

                    $filters[get_class($filter)] = $filter->getArguments();
                }
            } catch (\Exception $e) {
                $msg = $e->getMessage();

                $output->writeln('<error>Deploy error</error>');
                $output->writeln('<error>' . $msg . '</error>');
            }
            $output->writeln('<info>Deploy finished</info>');
        })
;

return $console;
