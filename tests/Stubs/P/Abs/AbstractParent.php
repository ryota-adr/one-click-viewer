<?php

namespace OneClickViewer\P\Abs;

abstract class AbstractParent
{
    public function getAbstractParentName() {
        return __CLASS__;
    }

    public static function staticGetAbstractParentName() {
        return __CLASS__;
    }
}
