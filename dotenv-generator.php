<?php
//travis ci
if (!file_exists('.env')) {
    file_put_contents('.env', 'AUTOLOADERPATH=' . str_replace('\\', '/', __DIR__) . '/tests/vendor/autoload.php');
    echo 'generated .env.';
}
?>