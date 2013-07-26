## Introduction
<b>Bootstrap CSS Sprite</b> is a PHP library, which provides displaying of multiple images as a signle sprite in a Twitter Bootstrap style. 
E.g. you have a set of images one of which is named <b>kitty.png</b>. 
To display this image you can just use tag <b>&lt;i&gt;</b> with CSS class <b>img-kitty</b> as Twitter Bootstrap do: <code>&lt;i class="img-kitty"&gt;&lt;/i&gt;</code>.
A nice bonus: the image's height and width are set automatically in CSS file.

<p align="center">
    <img src="https://raw.github.com/uaoleg/bootstrap-css-sprite/master/VIEWME.png" />
</p>

## Benefits
<ul>
    <li>One image file instead one multiple: one request to server and less traffic.</li>
    <li>Image hover first time without blinking and "jumping".</li>
    <li>No need to define size for each image in HTML templates - library will do it for you in generated CSS file.</li>
    <li>Less HTML code: <code>&lt;i class="img-kitty"&gt;&lt;/i&gt;</code> instead of <pre><code>&lt;img src="&lt;?=$this->theme->baseUrl?&gt;/images/kitty.png" style="width: 64px; height: 64px;" /&gt;</code></pre>
    It really saves your time!</li>
</ul>

## Usage
Here is most simple example how to use the library.
This code sample takes all images (jpg, jpeg, gif, png) from <i>./images/source/</i> directory.
Than it merges all these images into one - <b>sprite.png</b> and generates CSS file - <b>sprite.css</b>.
The CSS file contains classes for all merged files. These classes define source of image and it's size.
<pre><code>$sprite = new BootstrapCssSprite(array(
    'imgSourcePath' => './images/source',
    'imgSourceExt'  => 'jpg,jpeg,gif,png',
    'imgDestPath'   => './images/sprite.png',
    'cssPath'       => './css/sprite.css',
    'cssNamespace'  => 'img-',
    'cssImgUrl'     => '../images/sprite.png',
));
$sprite->generate();
</code></pre>

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
