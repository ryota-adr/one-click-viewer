<?php
require('defines.php');
require("src/OneClickViewer.php");

$classNameOrPath = isset($_GET['q']) ? $_GET['q'] : null;
$viewer = new OneClickViewer('.env', $classNameOrPath);
$viewer->setHtml();
?>
<html>
<head>
    <meta charset="utf-8">
    <title>One Click Viewer</title>
    <link rel="stylesheet" href="src/css/style.css">
    <link rel="stylesheet" href="src/css/icomoon/style.css">
</head>
<body>
    <div class="search_bar">
        <form action="/" method="GET">
            <div class="flex" role="input-text">
                <div class="flex width100" role="input_text_and_button">
                    <div class="width100">
                        <input type="text" name="q" value="<?php echo $viewer->getInputValue(); ?>">
                    </div>
                    <div>
                        <button type="submit" class="show_button">SHOW</button>
                    </div>
                </div>
                <button type="button" class="toggle_button icon-chevron-left" role="toggle_input_text">
                </button>
            </div>
        </form>
    </div>
    <div class="container">
        <span class="dir" data-dir="<?php echo $viewer->getDirUri(); ?>">
            <?php echo $viewer->getDirUri(); ?>
        </span>
        <?php $viewer->output(); ?>
    </div>
    <div>
        <script type="text/javascript" src="src/js/script.js"></script>
    </div>
</body>
</html>