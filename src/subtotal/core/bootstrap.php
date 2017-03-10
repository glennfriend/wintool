<?php
use App\Utility\Log;

function initialize($basePath)
{
    error_reporting(E_ALL);
    ini_set('html_errors','Off');
    ini_set('display_errors','On');

    require_once  $basePath . '/core/Config.php';
    Config::init( $basePath . '/config');

    if ( conf('public.base.path') !== $basePath ) {
       show('base path setting error!');
       exit;
    }

    date_default_timezone_set(conf('app.timezone'));

    require_once $basePath . '/vendor/autoload.php';
    Log::init(   $basePath . '/var');

}

function conf($key)
{
    return Config::get($key);
}

function show($data, $writeLog=false )
{
    if (is_object($data) || is_array($data)) {
        print_r($data);
    }
    else {
        echo $data;
        echo "\n";
    }

    // write to log
    if (!$writeLog) {
        return;
    }

    if (is_object($data) || is_array($data)) {
        Log::record(
            print_r($data, true)
        );
    }
    else {
        Log::record($data);
    }
}

/**
 *  get command line param or get web param
 *
 *  @dependency isCli()
 *  @dependency getWebParam()
 *  @dependency getCliParam()
 */
function getParam($key)
{
    if (isCli()) {
        return getCliParam($key);
    }
    else {
        return getWebParam($key);
    }
}

function getWebParam($key)
{
    if (isset($_POST[$key])) {
        return $_POST[$key];
    }
    elseif (isset($_GET[$key])) {
        return $_GET[$key];
    }
    return null;
}

/**
 *  get command line value
 *
 *  @return string|int or null
 */
function getCliParam($key)
{
    global $argv;
    $allParams = $argv;
    array_shift($allParams);

    if (in_array($key, $allParams)) {
        return true;
    }

    foreach ($allParams as $param) {

        $tmp = explode('=', $param);
        $name = $tmp[0];
        array_shift($tmp);
        $value = join('=', $tmp);

        if ($name===$key) {
            return $value;
        }
    }

    return null;
}

function isCli()
{
    return PHP_SAPI === 'cli';
}

// --------------------------------------------------------------------------------
//
// --------------------------------------------------------------------------------

/**
 *  Clean invisible control characters and unused code points
 *
 *  \p{C} or \p{Other}: invisible control characters and unused code points.
 *      \p{Cc} or \p{Control}: an ASCII 0x00–0x1F or Latin-1 0x80–0x9F control character.
 *      \p{Cf} or \p{Format}: invisible formatting indicator.
 *      \p{Co} or \p{Private_Use}: any code point reserved for private use.
 *      \p{Cs} or \p{Surrogate}: one half of a surrogate pair in UTF-16 encoding.
 *      \p{Cn} or \p{Unassigned}: any code point to which no character has been assigned.
 *
 *  該程式可以清除 RIGHT-TO-LEFT MARK (200F)
 *
 *  @see http://www.regular-expressions.info/unicode.html
 *
 */
function filterUnusedCode($row)
{
    foreach ( $row as $index => $value ) {
        $row[$index] = preg_replace('/\p{C}+/u', "", $value );
    }
    return $row;
}

/**
 *  create csv content to file
 */
function writeCsvFile($pathFile, $content)
{
    DownloadHelper::contentToCsv($content, $pathFile);
}
