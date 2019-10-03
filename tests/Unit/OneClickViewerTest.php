<?php

use PHPUnit\Framework\TestCase;

class OneClickViewerTest extends TestCase
{
    protected $viewer;
    protected $class = 'OneClickViewer\Tests\TestClass';
    protected $parentClass = 'OneClickViewer\Tests\P\ParentA';
    protected $code;

    protected $fullyQualifiedClassNames = [
        'OneClickViewer\Tests\Int\InterfaceA',
        'OneClickViewer\Tests\Int\InterfaceB',
        'OneClickViewer\Tests\P\ParentA',
        'OneClickViewer\Tests\Some\SomeClass',
        'OneClickViewer\Tests\Ex\ExceptionA',
    ];

    protected $classArrs = [
        [
            'fullyQualifiedClassName' => 'OneClickViewer\Tests\Int\InterfaceA',
            'end' => 'InterfaceA',
            'alias' => ''
        ], 
        [
            'fullyQualifiedClassName' => 'OneClickViewer\Tests\Int\InterfaceB',
            'end' => 'InterfaceB',
            'alias' => ''
        ],
        [
            'fullyQualifiedClassName' => 'OneClickViewer\Tests\P\ParentA',
            'end' => 'ParentA',
            'alias' => ''
        ],
        [
            'fullyQualifiedClassName' => 'OneClickViewer\Tests\Some\SomeClass',
            'end' => 'SomeClass',
            'alias' => ''
        ],
        [
            'fullyQualifiedClassName' => 'OneClickViewer\Tests\Ex\ExceptionA',
            'end' => 'ExceptionA',
            'alias' => ''
        ],
        [
            'fullyQualifiedClassName' => 'OneClickViewer\Tests\Some\Foo\SomeClass',
            'end' => 'SomeClass',
            'alias' => 'FooClass'
        ],
    ];

    protected $properties = [
        'publicProp', 'protectedProp', 'privateProp', 'someClass'
    ];

    protected $methods = [
        'getSomeClass',
        'setSomeClass'
    ];

    protected $constants = [
        'NAME'
    ];

    protected $extendedProperties = [
        'parentProp'
    ];

    protected $extendedMethods = [
        'getRandomInt'
    ];

    protected $extendedConstants = [
        'PARENTA_NAME'
    ];

    protected $parentProperties = [
        'parentProp'
    ];

    protected $parentMethods = [
        'getRandomInt'
    ];

    protected $parentConstants = [
        'PARENTA_NAME'
    ];

    protected $internalFunctions = [
        'random_int', 'array_merge'
    ];

    protected $internalClasses = [
        'ReflectionClass', 'DateTime'
    ];

    public static function setUpBeforeClass(): void
    {
        define('ROOT', dirname(dirname(__DIR__)));

        require(ROOT . '/defines.php');
        require(ROOT . '/src/OneClickViewer.php');
        require(ROOT . '/src/helpers.php');
        require(ROOT . '/tests/vendor/autoload.php');
    }

    public function setUp(): void
    {
        $this->viewer = new OneClickViewer(ROOT . '/.env', $this->class);
        $this->viewer->setHtml();
        $this->code = $this->viewer->getHtml();
    }

    public function testReplaceFullyQualifiedClassName()
    {
        $classArrs = array_filter($this->classArrs, function ($classArr) {
            return empty($classArr['alias']);
        });
        foreach ($classArrs as $classArr) {
            preg_match_all('/' . preg_quote($classArr['fullyQualifiedClassName']) . '" role="link">' . preg_quote($classArr['fullyQualifiedClassName']) . '</', $this->code, $matches);
            $this->assertTrue(!empty($matches[0]));
        }
    }

    public function testReplaceAliasClasses()
    {
        $classArrs = array_filter($this->classArrs, function ($classArr) {
            return !empty($classArr['alias']);
        });

        foreach ($classArrs as $classArr) {
            preg_match_all(
                '/' . preg_quote($classArr['fullyQualifiedClassName']) . '" role="link">' . preg_quote($classArr['fullyQualifiedClassName']) . ' as ' . $classArr['alias'] .'</',
                $this->code,
                $matchesAsAlias
            );

            $this->assertTrue(!empty($matchesAsAlias[0]));

            preg_match_all(
                '/' . preg_quote($classArr['fullyQualifiedClassName']) . '" role="link">' . $classArr['alias'] . '</',
                $this->code,
                $matchesAlias
            );

            $this->assertTrue(!empty($matchesAlias[0]));
        }
    }

    public function testReplaceEndClass()
    {
        $classArrs = array_filter($this->classArrs, function($classArr) {
            return !empty($classArr['end']) && empty($classArr['alias']);
        });

        foreach ($classArrs as $classArr) {
            preg_match_all(
                '/' . preg_quote($classArr['fullyQualifiedClassName']) . '" role="link">' . $classArr['end'] . '</',
                $this->code,
                $matches
            );

            $this->assertTrue(!empty($matches[0]));
        }
    }

    public function testReplaceNotFullyQualifiedClassName()
    {
        $namespace = 'OneClickViewer\Tests\Tr';
        $notFullyQualifiedClassNames = ['Tr\TraitA', 'Tr\TraitB'];

        foreach ($notFullyQualifiedClassNames as $notFullyQualifiedClassName) {
            preg_match_all(
                '/' . preg_quote(str_replace('Tr', $notFullyQualifiedClassName, $namespace)) . '" role="link">' . preg_quote($notFullyQualifiedClassName) . '</',
                $this->code,
                $matches
            );

            $this->assertTrue(!empty($matches[0]));
        }
    }

    public function testAddSpanIds()
    {
        foreach ($this->properties as $property) {
            preg_match('/<span id="' . $property . '"> <\/span>/', $this->code, $matchSpan);
            $this->assertTrue(!empty([$matchSpan[0]]));
        }
    }

    public function testReplaceCalledProps()
    {
        foreach ($this->properties as $property) {
            preg_match_all(
                '/' . preg_quote($this->class) . '#' . $property . '" role="link">' . $property . '</',
                $this->code,
                $matchPropeties);
            
            $this->assertTrue(!empty($matchPropeties[0]));
        }
    }

    public function testReplaceCalledMethods()
    {
        foreach ($this->methods as $method) {
            preg_match_all(
                '/' . preg_quote($this->class) . '#' . $method . '" role="link">' . $method . '</',
                $this->code,
                $matchMethods
            );

            $this->assertTrue(!empty($matchMethods[0]));
        }
    }

    public function testReplaceCalledConsts()
    {
        foreach ($this->constants as $constant) {
            preg_match_all(
                '/role="link">' . $constant . '</',
                $this->code,
                $matchConstants
            );
            
            $this->assertTrue(!empty($matchConstants[0]));
        }
    }

    public function testReplaceExtendedProps()
    {
        foreach ($this->extendedProperties as $extendedProperty) {
            preg_match_all(
                '/' . preg_quote($this->parentClass) . '#' . $extendedProperty . '" role="link">' . $extendedProperty . '</',
                $this->code,
                $matchExtendedProperties
            );

            $this->assertTrue(!empty($matchExtendedProperties[0]));
        }
    }

    public function testReplaceExtendedMethods()
    {
        foreach ($this->extendedMethods as $extendedMethod) {
            preg_match_all(
                '/' . preg_quote($this->parentClass) . '#' . $extendedMethod . '" role="link">' . $extendedMethod . '</',
                $this->code,
                $matchExtendedMethods
            );

            $this->assertTrue(!empty($matchExtendedMethods[0]));
        }
    }

    public function testReplaceExtendedConsts()
    {
        foreach ($this->extendedConstants as $extendedConstant) {
            preg_match_all(
                '/' . preg_quote($this->parentClass) . '#' . $extendedConstant . '" role="link">' . $extendedConstant . '</',
                $this->code,
                $matchExtendedConstants
            );

            $this->assertTrue(!empty($matchExtendedConstants[0]));
        }
    }

    public function testReplaceParentPropsMethodsConsts()
    {
        $parentPropsMethodsConsts = array_merge($this->parentProperties, $this->parentMethods, $this->parentConstants);

        foreach ($parentPropsMethodsConsts as $parentPropMethodConst) {
            preg_match_all(
                '/' . preg_quote($this->parentClass) . '#' . $parentPropMethodConst . '" role="link">' . $parentPropMethodConst . '</',
                $this->code,
                $matchPropsMethodsConsts
            );

            $this->assertTrue(!empty($matchPropsMethodsConsts[0]));
        }
    }

    public function testReplaceInternalFunctions()
    {
        foreach ($this->internalFunctions as $internalFunction) {
            preg_match_all(
                '/' . preg_quote('https://www.php.net/manual/ja/function.', '/') . str_replace('_', '-', $internalFunction) . '\.php" role="link">' . $internalFunction . '</',
                $this->code,
                $matchFunctions
            );

            $this->assertTrue(!empty($matchFunctions[0]));
        }
    }

    public function testReplaceInternalClasses()
    {
        foreach ($this->internalClasses as $internalClass) {
            preg_match_all(
                '/' . preg_quote('https://www.php.net/manual/ja/class.', '/') . strtolower($internalClass) . '\.php" role="link">' . $internalClass . '</',
                $this->code,
                $matchInternalClasses
            );
            
            $this->assertTrue(!empty($matchInternalClasses[0]));
        }
    }

    public function testGetDirUri()
    {
        $this->assertSame($this->viewer->getDirUri(), 'C:/xampp/htdocs/TestPHP/MyApp/one-click-viewer/tests/Stubs');
    }

    public function testInputInvalidClassName()
    {
        $viewer = new OneClickViewer(ROOT . '/.env', 'invalid\class');
        $viewer->setHtml();
        $code = $viewer->getHtml();

        $message = 'Invalid class name or path.';

        preg_match('/' . preg_quote($message, '/') . '/', $code, $matchMessage);

        $this->assertSame($matchMessage[0], $message);
    }

    public function testInputNullClassName()
    {
        $viewer = new OneClickViewer(ROOT . '/.env', null);
        $viewer->setHtml();
        $code = $viewer->getHtml();

        $message = 'Invalid class name or path.';

        preg_match('/' . preg_quote($message, '/') . '/', $code, $matchMessage);

        $this->assertSame($matchMessage[0], $message);
    }

    public function testInputPath()
    {
        $viewer = new OneClickViewer(str_replace('\\', '/', ROOT) . '/.env', ROOT . '/tests/Stubs/TestClass.php');
        $viewer->setHtml();
        $code = $viewer->getHtml();
        
        preg_match(
            '/' . preg_quote($this->parentClass) . '" role="link">' . preg_quote($this->parentClass) . '</',
            $code,
            $matchReplacedString
        );

        $this->assertSame(
            $matchReplacedString[0],
            $this->parentClass . '" role="link">' . $this->parentClass . '<'
        );
    }

    public function testInvalidAutoloaderPath()
    {
        $viewer = new OneClickViewer(__DIR__ . '/.invalid-env', $this->class);
        $viewer->setHtml();
        $code = $viewer->getHtml();
        $message = 'invalid autoloader file path: ';

        preg_match('/' . preg_quote($message, '/') . '/', $code, $matchMessage);

        $this->assertSame($matchMessage[0], $message);
    }
}
?>