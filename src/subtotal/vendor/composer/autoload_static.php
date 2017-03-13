<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit035fcfa7020e206f798667e48528f9fe
{
    public static $files = array (
        '27a18a69a49780ef534023cacfb5b0ce' => __DIR__ . '/../..' . '/core/custom_helper.php',
    );

    public static $prefixLengthsPsr4 = array (
        'L' => 
        array (
            'League\\Csv\\' => 11,
        ),
        'A' => 
        array (
            'App\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'League\\Csv\\' => 
        array (
            0 => __DIR__ . '/..' . '/league/csv/src',
        ),
        'App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit035fcfa7020e206f798667e48528f9fe::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit035fcfa7020e206f798667e48528f9fe::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
