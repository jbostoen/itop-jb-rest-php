<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitcde32ae9a1df670d8eef4586cb197297
{
    public static $prefixesPsr0 = array (
        'A' => 
        array (
            'AppName' => 
            array (
                0 => __DIR__ . '/../..' . '/src',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'JeffreyBostoen\\iTopRestService\\RestException' => __DIR__ . '/../..' . '/src/JeffreyBostoen/iTopRestService/RestException.php',
        'JeffreyBostoen\\iTopRestService\\Service' => __DIR__ . '/../..' . '/src/JeffreyBostoen/iTopRestService/Service.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInitcde32ae9a1df670d8eef4586cb197297::$prefixesPsr0;
            $loader->classMap = ComposerStaticInitcde32ae9a1df670d8eef4586cb197297::$classMap;

        }, null, ClassLoader::class);
    }
}
