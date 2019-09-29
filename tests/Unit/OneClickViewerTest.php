<?php
require_once('../../defines.php');
require_once('../../src/OneClickViewer.php');
require_once('../../tests/vendor/autoload.php');

use PHPUnit\Framework\TestCase;

class OneClickViewerTest extends TestCase
{
    public function testReplace()
    {
        $viewer = new OneClickViewer('../../.env');
    }
}
?>