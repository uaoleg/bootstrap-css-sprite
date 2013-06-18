<?php

require_once '../lib/BootstrapCssSprite.php';

$sprite = new BootstrapCssSprite(array(
    'imgSourcePath' => __DIR__ . '/images/source',
    'imgSourceExt'  => 'jpg,jpeg,gif,png',
    'imgDestPath'   => __DIR__ . '/images/sprite.png',
    'cssPath'       => __DIR__ . '/css/sprite.css',
    'cssNamespace'  => 'img-',
    'cssImgUrl'     => '../images/sprite.png',
));
$tagList = $sprite->generate();

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Bootstrap CSS Sprite Demo Page</title>
        <link rel="stylesheet" type="text/css" href="css/sprite.css" />
        <style type="text/css">
            [class^="img-"],
            [class*=" img-"] {
                border: 1px solid gray;
                border-radius: 5px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                margin: 5px;
            }
        </style>
    </head>
    <body>
        <?php if (count($sprite->getErrors()) > 0): ?>
            <h2>Errors occured:</h2>
            <pre><?php print_r($sprite->getErrors()); ?></pre>
        <?php else: ?>
            <h2>Success!</h2>
            <?php foreach ($tagList as $tag): ?>
                <?php echo $tag; ?>
                <?php echo htmlentities($tag); ?>
                <br />
            <?php endforeach; ?>
        <?php endif; ?>
    </body>
</html>