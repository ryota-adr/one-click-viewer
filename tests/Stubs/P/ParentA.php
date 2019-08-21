<?php
namespace OneClickViewer\P;

use OneClickViewer\Contract\ParentContract;
use OneClickViewer\P\Abs\AbstractParent;

class ParentA extends AbstractParent implements ParentContract
{
    /**
     * The name of this class.
     * 
     * @var string
     */
    const NAME = 'ParentA';

    /**
     * Static function.
     * 
     * return void
     */
    public function staticFunc()
    {
        return;
    }
}
