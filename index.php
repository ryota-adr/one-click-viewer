<?php
$autoloader = "";
require("OneClickViewer.php");

$viewer = new OneClickViewer($autoloader);
$viewer->html()->output();
?>