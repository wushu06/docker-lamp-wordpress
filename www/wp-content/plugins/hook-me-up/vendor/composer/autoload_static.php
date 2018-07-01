<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit986140e768cdc55835b4b7a9ae5778af
{
    public static $prefixLengthsPsr4 = array (
        'I' => 
        array (
            'Inc\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Inc\\' => 
        array (
            0 => __DIR__ . '/../..' . '/inc',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit986140e768cdc55835b4b7a9ae5778af::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit986140e768cdc55835b4b7a9ae5778af::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
