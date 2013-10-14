<?php

/**
 * BootstrapCssSprite as Yii component
 *
 * Displays multiple images as a sprite in a Bootstrap 3 style: <span class="img-kitty"></span>
 *
 * To merges all images from a given directory to one image and
 * to creates CSS file call generate() method.
 *
 * @author Oleg Poludnenko <oleg@poludnenko.info>
 * @version 0.6.4
 */
class YiiBootstrapCssSprite extends CApplicationComponent
{

    /**
     * List of errors
     */
    const ERROR_NO_SOURCE_IMAGES        = 'no-source-images';
    const ERROR_SPRITE_EQUALS_TO_SOURCE = 'sprite-equals-to-source';
    const ERROR_WRONG_IMAGE_FORMAT      = 'wrong-image-format';
    const ERROR_UNKNOWN_IMAGE_EXT       = 'unknown-image-ext';

    /**
     * List of magic actions (file suffix and CSS prefix)
     * @var array
     */
    public static $magicActions = array('hover', 'active', 'target');

    /**
     * Path to source images
     * @var string
     */
    public $imgSourcePath;

    /**
     * List of source image's extensions to process
     * @var string
     */
    public $imgSourceExt = 'jpg,jpeg,gif,png';

    /**
     * Image size (width or height) wich is greater, will be skipped
     * @var int
     */
    public $imgSourceSkipSize;

    /**
     * Path to result image
     * @var string
     */
    public $imgDestPath;

    /**
     * Path to result CSS file
     * @var string
     */
    public $cssPath;

    /**
     * Namespace (prefix) for CSS classes
     * @var string
     */
    public $cssNamespace = 'img';

    /**
     * Result image URL in the CSS file
     * @var string
     */
    public $cssImgUrl;

    /**
     * List of generated tag (can be used for example)
     * @var array
     */
    protected $_tagList = array();

    /**
     * List of errors
     * @var array
     */
    protected $_errors = array();

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        // Initial configuration
        foreach ($config as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Merges images and generates CSS file
     */
    public function generate()
    {
        $self = $this;

        // Clear errors
        $this->_errors = array();

        // Normalize destination image path
        $this->imgDestPath = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $this->imgDestPath);

        // Check modification time
        if ((is_dir($this->imgSourcePath)) && (is_file($this->imgDestPath))) {
            $imgSourceStat = stat($this->imgSourcePath);
            $imgDestStat = stat($this->imgDestPath);
            if ($imgSourceStat['mtime'] <= $imgDestStat['mtime']) {
                $this->addError(static::ERROR_SPRITE_EQUALS_TO_SOURCE);
                return;
            }
        }

        // Get list of images
        $fillImgList = function($dir) use(&$self, &$xOffset, &$imgList, &$imgWidth, &$imgHeight, &$fillImgList) {
            $imageList = glob($dir . DIRECTORY_SEPARATOR . '*.{' . $self->imgSourceExt . '}', GLOB_BRACE);
            foreach ($imageList as $imagePath) {

                // Skip previously generated sprite
                if ($imagePath === $self->imgDestPath) {
                    continue;
                }

                // Get image sizes
                $imageSize = @getimagesize($imagePath);
                if ($imageSize === false) {
                    $self->addError($self::ERROR_WRONG_IMAGE_FORMAT, $imagePath);
                    continue;
                } else {
                    list($itemWidth, $itemHeight, $itemType) = $imageSize;
                }

                // Check size
                if ($self->imgSourceSkipSize) {
                    if (($itemWidth > $self->imgSourceSkipSize) || ($itemHeight > $self->imgSourceSkipSize)) {
                        continue;
                    }
                }

                // Inc sprite size
                $imgWidth += $itemWidth;
                if ($itemHeight > $imgHeight) {
                    $imgHeight = $itemHeight;
                }

                // Push image to the list
                $imgList[$imagePath] = array(
                    'width'     => $itemWidth,
                    'height'    => $itemHeight,
                    'x'         => $xOffset,
                    'ext'       => image_type_to_extension($itemType, false),
                );

                $xOffset += $itemWidth;
            }
            $subdirList = glob($dir . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);
            foreach ($subdirList as $subdir) {
                $fillImgList($subdir);
            }
        };
        $xOffset = 0;
        $imgList = array();
        $imgWidth = $imgHeight = 0;
        $fillImgList($this->imgSourcePath);
        if (count($imgList) === 0) {
            $this->addError(static::ERROR_NO_SOURCE_IMAGES);
            return;
        }

        // Create transparent image
        $dest = imagecreatetruecolor($imgWidth, $imgHeight);
        imagesavealpha($dest, true);
        $trans_colour = imagecolorallocatealpha($dest, 0, 0, 0, 127);
        imagefill($dest, 0, 0, $trans_colour);

        // Init CSS
        $cssList = array();
        $cssList[] = array(
            'selectors' => array(
                '[class^="' . $this->cssNamespace . '-"]',
                '[class*=" ' . $this->cssNamespace . '-"]',
            ),
            'styles' => array(
                'background-image'      => 'url("' . $this->cssImgUrl . '")',
                'background-position'   => '0 0',
                'background-repeat'     => 'no-repeat',
                'display'               => 'inline-block',
                'height'                => '64px',
                'vertical-align'        => 'middle',
                'width'                 => '64px',
            ),
        );

        // Copy all images, create CSS file and list of tags
        $this->_tagList = array();
        foreach ($imgList as $imgPath => $imgData) {

            // Copy image
            $imgCreateFunc = 'imagecreatefrom' . $imgData['ext'];
            if (!function_exists($imgCreateFunc)) {
                continue;
            }
            $src = $imgCreateFunc($imgPath);
            imagealphablending($src, true);
            imagesavealpha($src, true);
            imagecopy($dest, $src, $imgData['x'], 0, 0, 0, $imgData['width'], $imgData['height']);
            imagedestroy($src);

            // Append CSS (if not a magic action)
            $sourcePathLeng = mb_strlen($this->imgSourcePath);
            $class = '.' . $this->cssNamespace . '-' . mb_substr($imgPath, $sourcePathLeng + 1);
            $class = mb_substr($class, 0, mb_strlen($class) - mb_strlen($imgData['ext']) - 1);
            $class = str_replace(DIRECTORY_SEPARATOR, '-', $class);
            $isMagicAction = false;
            foreach (static::$magicActions as $magicAction) {
                $isMagicAction = (mb_substr($class, -mb_strlen('.' . $magicAction)) === '.' . $magicAction);
                if ($isMagicAction) {
                    break;
                }
            }
            if (!$isMagicAction) {
                $cssList[] = array(
                    'selectors' => array($class),
                    'styles' => array(
                        'background-position'   => '-' . $imgData['x'] . 'px 0',
                        'height'                => $imgData['height'] . 'px',
                        'width'                 => $imgData['width'] . 'px',
                    ),
                );
            }

            // Check if image has magic action (active, hover, target)
            if (!$isMagicAction) {
                $extPos = mb_strrpos($imgPath, $imgData['ext']);
                foreach (static::$magicActions as $magicAction) {
                    if ($extPos !== false) {
                        $magicActionPath = substr_replace($imgPath, $magicAction . '.' . $imgData['ext'], $extPos, strlen($imgData['ext']));
                        $hasMagicAction = isset($imgList[$magicActionPath]);
                    } else {
                        $hasMagicAction = false;
                    }
                    if ($hasMagicAction) {
                        $magicActionData = $imgList[$magicActionPath];
                        $cssList[] = array(
                            'selectors' => array(
                                "{$class}:{$magicAction}",
                                "{$class}.{$magicAction}",
                                ".wrap-{$this->cssNamespace}:{$magicAction} {$class}",
                                ".wrap-{$this->cssNamespace}.{$magicAction} {$class}",
                            ),
                            'styles' => array(
                                'background-position'   => '-' . $magicActionData['x'] . 'px 0',
                                'background-position-x' => '-' . $magicActionData['x'] . 'px',
                                'height'                => $magicActionData['height'] . 'px',
                                'width'                 => $magicActionData['width'] . 'px',
                            ),
                        );
                    }
                }
            }

            // Append tag
            if (!$isMagicAction) {
                $this->_tagList[] = '<span class="' . mb_substr($class, 1) . '"></span>';
            }
        }

        // Save image to file
        $imgDestExt = mb_strtolower(mb_substr($this->imgDestPath, mb_strrpos($this->imgDestPath, '.') + 1));
        switch ($imgDestExt) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($dest, $this->imgDestPath);
                break;
            case 'gif':
                imagegif($dest, $this->imgDestPath);
                break;
            case 'png':
                imagepng($dest, $this->imgDestPath);
                break;
            default:
                $this->addError(static::ERROR_UNKNOWN_IMAGE_EXT, $this->imgDestPath);
                return;
                break;
        }
        imagedestroy($dest);

        // Save CSS file
        $cssString = '';
        foreach ($cssList as $css) {
            $cssString .= implode(',', $css['selectors']) . '{';
            foreach ($css['styles'] as $key => $value) {
                $cssString .= $key . ':'  .$value . ';';
            }
            $cssString .= '}';
        }
        file_put_contents($this->cssPath, $cssString);
    }

    /**
     * Returns tags
     *
     * @return array
     */
    public function getTagList()
    {
        return $this->_tagList;
    }

    /**
     * Add an error
     *
     * @param int $type
     * @param string $message
     */
    public function addError($type, $message = '')
    {
        $this->_errors[] = array(
            'type'      => $type,
            'message'   => $message,
        );
    }

    /**
     * Returns errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

}