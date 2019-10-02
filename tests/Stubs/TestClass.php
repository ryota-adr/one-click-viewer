<?php
namespace OneClickViewer\Tests;

use OneClickViewer\Tests\Int\InterfaceA;
use OneClickViewer\Tests\Int\InterfaceB;
use OneClickViewer\Tests\P\ParentA;
use OneClickViewer\Tests\Some\SomeClass;
use OneClickViewer\Tests\Some\Foo\SomeClass as FooClass;
use OneClickViewer\Tests\Tr;
use OneClickViewer\Tests\Ex\ExceptionA;

class TestClass extends ParentA implements InterfaceA, InterfaceB
{
    use Tr\TraitA, Tr\TraitB;

    /**
     * The name of this class.
     *
     * @var string
     */
    const NAME = __CLASS__;

    /**
     * The public property.
     *
     * @var string
     */
    public $publicProp = 'public';

    /**
     * The protected property.
     *
     * @var int
     */
    protected $protectedProp = 1;

    /**
     * The private property.
     *
     * @var bool
     */
    private $privateProp = true;

    /**
     * SomeClass.
     *
     * @var \OneClickViewer\Tests\Some\SomeClass
     */
    protected $someClass;

    /**
     * Contructor.
     */
    public function __construct(SomeClass $someClass)
    {
        $this->setSomeClass($someClass);
        $reflect = new \ReflectionClass($this);
        $className = get_class($reflect);
        $foo = new FooClass();
        $parentProp = $this->parentProp;
        $randomInt = $this->getRandomInt();
        $parentAName = static::PARENTA_NAME;
        $int = random_int(0, 1);
        $array = array_merge([], []);
        $datatime = new \DateTime('2000-01-01');
    }

    /**
     * Get SomeClass.
     *
     * @return \OneClickViewer\Tests\Some\SomeClass
     */
    public function getSomeClass(): SomeClass
    {
        return $this->someClass;
    }

    /**
     * Set someone.
     *
     * @param \OneClickViewer\Tests\Some\SomeClass
     * @return void
     */
    public function setSomeClass(SomeClass $someClass)
    {
        $this->someClass = $someClass;
    }

    /**
     * get name of SomeClass.
     *
     * return string
     */
    public function getSomeClassName()
    {
        return $this->someClass->getSomeClassName();
    }

    /**
     * Get constants.
     *
     * @return array
     */
    public function constants(): array
    {
        return [static::NAME, self::NAME, parent::NAME];
    }

    /**
     * The public function.
     *
     * @return string
     */
    public function getPublicProp(): string
    {
        return $this->publicProp;
    }

    /**
     * Protected function.
     *
     * return int
     */
    protected function getProtectedProp(): int
    {
        return $this->protectedProp;
    }

    /**
     * Private functin.
     *
     * return bool
     */
    private function getPrivateProp()
    {
        return $this->privateProp;
    }

    /**
     * Get class name.
     *
     * return string
     */
    public static function staticGetClassName()
    {
        return __CLASS__;
    }

    /**
     * Run methods of this class.
     * 
     * return array
     */
    public function runMethods()
    {
        return [
            $this->getSomeClass(),
            $this->setSomeClass()
        ];
    }

    /**
     * Run static functions of this class.
     *
     * return array
     */
    public static function runStaticGetClassNames(): array
    {
        return [
            static::staticGetClassName(),
            parent::staticGetClassName(),
            self::staticGetClassName(),
        ];
    }

    public function getInterfaceAName()
    {
        return 'OneClickViewer\Tests\Int\interfaceA';
    }

    public function getInterfaceBName()
    {
        return 'OneClickViewer\Tests\Int\interfaceB';
    }

    public function getParentInterfaceName()
    {
        return array_values(class_implements(new parent, true))[0];
    }

    /**
     * @throws \OneClickViewer\Tests\Ex\ExceptionA
     */
    public function throwExceptionA()
    {
        throw new ExceptionA;
    }

}
