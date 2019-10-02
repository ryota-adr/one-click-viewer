<?php
namespace OneClickViewer\Tests\P;

use OneClickViewer\Tests\Contract\ParentContract;
use OneClickViewer\Tests\P\Abs\AbstractParent;

class ParentA extends AbstractParent implements ParentContract
{
    /**
     * The name of this class.
     * 
     * @var string
     */
    const NAME = __CLASS__;

    const PARENTA_NAME = __CLASS__;

    protected $parentProp;

    /**
     * Get this class name.
     * 
     * return string
     */
    public static function staticGetClassName()
    {
        return __CLASS__;
    }

    /**
     * Get this class name.
     * 
     * return string
     */
    public function getClassName()
    {
        return __CLASS__;;
    }

    /**
     * Get 0 or 1 randomly.
     * 
     * return int
     */
    public function getRandomInt()
    {
        return random_int(0, 1);
    }
}
