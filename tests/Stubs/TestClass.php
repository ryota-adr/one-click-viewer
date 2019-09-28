<?php
namespace OneClickViewer;

use OneClickViewer\Int\InterfaceA;
use OneClickViewer\Int\InterfaceB;
use OneClickViewer\P\ParentA;
use OneClickViewer\Some\SomeClass;
use OneClickViewer\Tr;

class TestClass extends ParentA implements InterfaceA, InterfaceB
{
    use Tr\TraitA, Tr\TraitB;

    /**
     * The name of this class.
     *
     * @var string
     */
    const NAME = 'Test';

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
     * Someone.
     *
     * @var \OneClickViewer\Some\Someone
     */
    protected $someone;

    /**
     * Contructor.
     */
    public function contruct()
    {
        $this->setSomeone($someone);
    }

    /**
     * Get someone.
     *
     * @return \OneClickViewer\Some\Someone
     */
    public function getSomeone(): Someone
    {
        return $this->someone;
    }

    /**
     * Set someone.
     *
     * @param \OneClickViewer\Some\Someone
     * @return void
     */
    public function setSomeone(Someone $someone): Someone
    {
        $this->someone = $someone;
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
     * Soemone does something.
     *
     * return mixed
     */
    public function func()
    {
        return $this->someone->doSomething();
    }

    /**
     * The public function.
     *
     * @return string
     */
    public function publicFunc(): string
    {
        return $this->publicProp;
    }

    /**
     * Protected function.
     *
     * return int
     */
    protected function protectedFunc(): int
    {
        return $this->protectedProp;
    }

    /**
     * Private functin.
     *
     * return bool
     */
    private function privateFunc()
    {
        return $this->privateProp;
    }

    /**
     * Static function.
     *
     * return void
     */
    public static function staticFunc()
    {
        return;
    }

    /**
     * Run functions of the instance.
     *
     * return array
     */
    public function runFuncs(): array
    {
        return [
            $this->func(),
            $this->publicFunc(),
            $this->protectedFunc(),
            $this->privateFunc(),
        ];
    }

    /**
     * Run static functions of this class.
     *
     * return array
     */
    public static function runStaticFuncs(): array
    {
        return [
            static::staticFunc(),
            parent::staticFunc(),
            self::staticFunc(),
        ];
    }

    public function doIntA()
    {
        return;
    }

    public function doIntB()
    {
        return;
    }

    public function doIntP()
    {
        return;
    }

}
