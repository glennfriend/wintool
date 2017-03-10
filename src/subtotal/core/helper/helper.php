<?php
use App\Utility\Log;

/**
 *
 */
function isCli()
{
    return PHP_SAPI === 'cli';
}

/**
 *
 */
function conf($key)
{
    return Config::get($key);
}

/**
 *
 */
function pr($data, $writeLog=false)
{
    echo '<pre style="background-color:#def;color:#000;text-align:left;font-size:10px;font-family:Hack,dina,GulimChe;">';
    if (is_object($data) || is_array($data)) {
        print_r($data);
    }
    else {
        echo $data;
        echo PHP_EOL;
    }
    echo "</pre>" . PHP_EOL;

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
