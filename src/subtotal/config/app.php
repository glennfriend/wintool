<?php

/**
 *  app config
 *  example:
 *      echo Config::get('app.env');
 *
 */
return [

    /**
     *  Environment
     *
     *      training    - 開發者環境
     *      production  - 正式環境
     */
    'env' => 'training',

    /**
     *  timezone
     *
     *      +0 => UTC
     *      -7 => America/Los_Angeles
     *      +8 => Asia/Taipei
     */
    'timezone' => 'Asia/Taipei',

];
