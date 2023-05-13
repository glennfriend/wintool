<?php
use App\Utility\Log;

/**
 *
 */
function initialize()
{
    error_reporting(E_ALL);
    ini_set('html_errors','On');
    ini_set('display_errors','On');

    //
    $basePath = getProjectPath();

    //
    require_once $basePath . '/vendor/autoload.php';

    //
    require_once $basePath . '/core/helper.php';

    //
    require_once $basePath . '/core/Config.php';
    Config::init($basePath . '/config');

    //
    date_default_timezone_set(conf('app.timezone'));

    //
    if (conf('public.base.path') !== $basePath) {
       show('base path setting error!');
       exit;
    }

    //
    Log::init($basePath . '/var');
}

/**
 *
 */
function getProjectPath($appendPath = '')
{
    $path = dirname(__DIR__);
    if ($appendPath) {
        if (mb_substr($appendPath, 0, 1) !== '/') {
            throw new Exception('Error: getProjectPath() error!');
        }
        $path .= $appendPath;
    }
    return $path;
}
