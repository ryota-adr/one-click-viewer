<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitedc49623a70f1457b705d62c1b93b914
{
    public static $prefixLengthsPsr4 = array (
        'O' => 
        array (
            'OneClickViewer\\Tests\\' => 21,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'OneClickViewer\\Tests\\' => 
        array (
            0 => __DIR__ . '/../..' . '/Stubs',
        ),
    );

    public static $prefixesPsr0 = array (
        'N' => 
        array (
            'No_Namespace_' => 
            array (
                0 => __DIR__ . '/../..' . '/NoNamespaceStubs',
            ),
        ),
    );

    public static $classMap = array (
        'No_Namespace_TestClass' => __DIR__ . '/../..' . '/NoNamespaceStubs/TestClass.php',
        'OneClickViewer\\Tests\\Contract\\ParentContract' => __DIR__ . '/../..' . '/Stubs/Contract/ParentContract.php',
        'OneClickViewer\\Tests\\Ex\\ExceptionA' => __DIR__ . '/../..' . '/Stubs/Ex/ExceptionA.php',
        'OneClickViewer\\Tests\\Int\\P\\ParentInterface' => __DIR__ . '/../..' . '/Stubs/Int/P/ParentInterface.php',
        'OneClickViewer\\Tests\\Int\\interfaceA' => __DIR__ . '/../..' . '/Stubs/Int/InterfaceA.php',
        'OneClickViewer\\Tests\\Int\\interfaceB' => __DIR__ . '/../..' . '/Stubs/Int/InterfaceB.php',
        'OneClickViewer\\Tests\\Invalid\\InvalidClass' => __DIR__ . '/../..' . '/Stubs/Invalid/InvalidClass.php',
        'OneClickViewer\\Tests\\P\\Abs\\AbstractParent' => __DIR__ . '/../..' . '/Stubs/P/Abs/AbstractParent.php',
        'OneClickViewer\\Tests\\P\\ParentA' => __DIR__ . '/../..' . '/Stubs/P/ParentA.php',
        'OneClickViewer\\Tests\\Some\\Foo\\SomeClass' => __DIR__ . '/../..' . '/Stubs/Some/Foo/SomeClass.php',
        'OneClickViewer\\Tests\\Some\\SomeClass' => __DIR__ . '/../..' . '/Stubs/Some/SomeClass.php',
        'OneClickViewer\\Tests\\TestClass' => __DIR__ . '/../..' . '/Stubs/TestClass.php',
        'OneClickViewer\\Tests\\Tr\\ParentTrait' => __DIR__ . '/../..' . '/Stubs/Tr/ParentTrait.php',
        'OneClickViewer\\Tests\\Tr\\TraitA' => __DIR__ . '/../..' . '/Stubs/Tr/TraitA.php',
        'OneClickViewer\\Tests\\Tr\\TraitB' => __DIR__ . '/../..' . '/Stubs/Tr/TraitB.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitedc49623a70f1457b705d62c1b93b914::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitedc49623a70f1457b705d62c1b93b914::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitedc49623a70f1457b705d62c1b93b914::$prefixesPsr0;
            $loader->classMap = ComposerStaticInitedc49623a70f1457b705d62c1b93b914::$classMap;

        }, null, ClassLoader::class);
    }
}