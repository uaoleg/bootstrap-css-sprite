## Introduction
<b>Bootstrap CSS Sprite</b> is a PHP library, which provides displaying of multiple images as a signle sprite in a Bootstrap 3 style.
E.g. you have a set of images one of which is named <b>cat.png</b>.
To display this image you can just use tag <b>&lt;span&gt;</b> with CSS class <b>img-cat</b> as Bootstrap 3 does: <code>&lt;span class="img-cat"&gt;&lt;/span&gt;</code>.
A nice bonus: the image's height and width are set automatically in CSS file.

<p align="center">
    <img src="https://raw.github.com/uaoleg/bootstrap-css-sprite/master/VIEWME.png" />
</p>

## Benefits
<ul>
    <li>One image file instead of multiple: one request to server &ndash; less traffic and time.</li>
    <li>Image hover first time without blinking and "jumping".</li>
    <li>No need to define size for each image in HTML templates - library will do it for you in generated CSS file.</li>
    <li>Less HTML code: <code>&lt;span class="img-cat"&gt;&lt;/span&gt;</code> instead of <pre><code>&lt;img src="&lt;?=$this->theme->baseUrl?&gt;/images/cat.png" style="width: 64px; height: 64px;" /&gt;</code></pre>
    It really saves your time!</li>
</ul>

## Usage
Here is most simple example how to use the library.
This code sample takes all images (jpg, jpeg, gif, png) from <i>./images/source/</i> directory.
Than it merges all these images into one - <b>sprite.png</b> and generates CSS file - <b>sprite.css</b>.
The CSS file contains classes for all merged files. These classes define source of image, it's size and hover behavior.
<pre><code>$sprite = new BootstrapCssSprite(array(
    'imgSourcePath' => './images/source',
    'imgSourceExt'  => 'jpg,jpeg,gif,png',
    'imgDestPath'   => './images/sprite.png',
    'cssPath'       => './css/sprite.css',
    'cssImgUrl'     => '../images/sprite.png',
));
$sprite->generate();
</code></pre>

It will look the same way for Yii component. Just copy <b>YiiBootstrapCssSprite.php</b> file to /extensions/ and add this component in /config/main.php
<pre><code>'components' => array(
    ...
    'sprite' => array(
        'class'         => 'ext.YiiBootstrapCssSprite',
        'imgSourcePath' => '/path/to/images/source',
        'imgSourceExt'  => 'jpg,jpeg,gif,png',
        'imgDestPath'   => '/path/to/images/sprite.png',
        'cssPath'       => '/path/to/css/sprite.css',
        'cssImgUrl'     => '/path/to/images/sprite.png',
    ),
    ...
)
</code></pre>
And generate sprite anywhere you want:
<pre><code>abstract class BaseController
{
    public function init()
    {
        ...
        if (APP_ENV === APP_ENV_DEV) {
            Yii::app()->sprite->generate(); // Regenerates sprite only if source dir was changed
        }
        ...
    }
}
</code></pre>

## :hover
If you want your picture to be changed on mouse hover, you just need to put <b>cat.hover.png</b> image file near <b>cat.png</b>.
And that's all!
In case when you need to change picture when it's parent element mouseovered (not picture itself), you should add <b>hover-img</b> CSS-class to the element:
<pre><code>&lt;button class="btn hover-img"&gt;&lt;span class="img-cat"&gt;&lt;/span&gt; My Cat&lt;/button&gt;</code></pre>
Also you can trigger hover event manually by adding <b>hover</b> CSS-class to your picture:
<pre><code>$('.img-cat').addClass('hover')</code></pre>

## Contributors
Oleg Poludnenko <oleg@poludnenko.info>

## Bugs & Issues
Please feel free to report any bugs and issues to me, my email is: <oleg@poludnenko.info>

## Contributing
Somebody who want to contribute to the project, may help us by doing these:
<ul>
    <li>Implement a component for one of PHP frameworks (Yii, Zend, Symfony and others).</li>
    <li>Port the library to other web-related languages (Ruby, .Net, Java, etc.).</li>
</ul>
