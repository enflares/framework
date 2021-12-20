<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit173689fe1034d17025d9dff79644acce
{
    public static $prefixLengthsPsr4 = array (
        'e' => 
        array (
            'enflares\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'enflares\\' => 
        array (
            0 => __DIR__ . '/..' . '/enflares/framework/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit173689fe1034d17025d9dff79644acce::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit173689fe1034d17025d9dff79644acce::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit173689fe1034d17025d9dff79644acce::$classMap;

        }, null, ClassLoader::class);
    }
}