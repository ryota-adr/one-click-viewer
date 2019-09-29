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
}
