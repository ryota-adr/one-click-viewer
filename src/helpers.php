<?php

function outHtml($input)
{
    $root = dirname(__DIR__);

    file_put_contents($root . '/out.html', $input);
}