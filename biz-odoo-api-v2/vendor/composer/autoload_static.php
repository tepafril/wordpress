<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitc2eee259ebbc0373f1c967e7e4eef1d6
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'Automattic\\WooCommerce\\' => 23,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Automattic\\WooCommerce\\' => 
        array (
            0 => __DIR__ . '/..' . '/automattic/woocommerce/src/WooCommerce',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitc2eee259ebbc0373f1c967e7e4eef1d6::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitc2eee259ebbc0373f1c967e7e4eef1d6::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
