<?php

require_once '../lib/BootstrapCssSprite.php';

$sprite = new BootstrapCssSprite(array(
    'imgSourcePath' => __DIR__ . '/images/source',
    'imgSourceExt'  => 'jpg,jpeg,gif,png',
    'imgDestPath'   => __DIR__ . '/images/sprite.png',
    'cssPath'       => __DIR__ . '/css/sprite.css',
    'cssNamespace'  => 'img',
    'cssImgUrl'     => '../images/sprite.png',
));
$sprite->generate();

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Bootstrap CSS Sprite Demo Page</title>
        <link rel="stylesheet" type="text/css" href="css/styles.css" />
        <link rel="stylesheet" type="text/css" href="css/sprite.css" />
    </head>
    <body>
        <?php if (count($sprite->getErrors()) > 0): ?>
            <h2>Errors occured:</h2>
            <pre><?php print_r($sprite->getErrors()); ?></pre>
        <?php else: ?>
            <h2>Success!</h2>
            <?php foreach ($sprite->getTagList() as $tag): ?>
                <div class="hover-img">
                    <?php echo $tag; ?>
                    <?php echo htmlentities($tag); ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </body>
</html>