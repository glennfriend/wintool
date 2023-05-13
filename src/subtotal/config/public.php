<?php

/**
 *  設置規定:
 *
 *      所有路徑最後面都不能包含 "/" 符號
 *
 */

$basePath = dirname(__DIR__);

return [

    /**
     *  base path
     *  base url
     */
    'base' => [
        'path' => $basePath,
        'url'  => null,
    ],

];
