<?php

use PHPUnit\Framework\TestCase;

class OneClickViewerTest extends TestCase
{
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

    public static function setUpBeforeClass(): void
    {
        define('ROOT', dirname(dirname(__DIR__)));

        require(ROOT . '/defines.php');
        require(ROOT . '/src/OneClickViewer.php');
        require(ROOT . '/tests/vendor/autoload.php');
    }

    public function setUp(): void
    {
        $this->viewer = new OneClickViewer(ROOT . '/.env', 'OneClickViewer\Tests\TestClass');
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
}
?>