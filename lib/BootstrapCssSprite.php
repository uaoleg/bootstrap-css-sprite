<?php

/**
 * BootstrapCssSprite
 *
 * Displays multiple images as a sprite in a Twitter Bootstrap style: <i class="img-kitty"></i>
 *
 * To merges all images from a given directory to one image and
 * to creates CSS file call generate() method.
 *
 * @author Oleg Poludnenko <oleg@poludnenko.info>
 * @version 0.6.0
 */
class BootstrapCssSprite
{

    /**
     * List of errors
     */
    const ERROR_NO_SOURCE_IMAGES    = 'no-source-images';
    const ERROR_WRONG_IMAGE_FORMAT  = 'wrong-image-format';
    const ERROR_UNKNOWN_IMAGE_EXT   = 'unknown-image-ext';

    /**
     * Hover word (file suffix and CSS prefix)
     */
    const HOVER_WORD = 'hover';

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

        // Get list of images
        $fillImgList = function($dir) use(&$self, &$xOffset, &$imgList, &$imgWidth, &$imgHeight, &$fillImgList) {
            $imageList = glob($dir . DIRECTORY_SEPARATOR . '*.{' . $self->imgSourceExt . '}', GLOB_BRACE);
            foreach ($imageList as $imagePath) {
                $imageSize = @getimagesize($imagePath);
                if ($imageSize === false) {
                    $self->addError($self::ERROR_WRONG_IMAGE_FORMAT, $imagePath);
                    continue;
                } else {
                    list($itemWidth, $itemHeight, $itemType) = $imageSize;
                }
                $imgWidth += $itemWidth;
                if ($itemHeight > $imgHeight) {
                    $imgHeight = $itemHeight;
                }

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
        $tagList = array();
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

            // Append CSS (if not a hover)
            $sourcePathLeng = mb_strlen($this->imgSourcePath);
            $class = '.' . $this->cssNamespace . '-' . mb_substr($imgPath, $sourcePathLeng + 1);
            $class = mb_substr($class, 0, mb_strlen($class) - mb_strlen($imgData['ext']) - 1);
            $class = str_replace(DIRECTORY_SEPARATOR, '-', $class);
            $isHover = (mb_substr($class, -mb_strlen('.' . static::HOVER_WORD)) === '.' . static::HOVER_WORD);
            if (!$isHover) {
                $cssList[] = array(
                    'selectors' => array($class),
                    'styles' => array(
                        'background-position'   => '-' . $imgData['x'] . 'px 0',
                        'height'                => $imgData['height'] . 'px',
                        'width'                 => $imgData['width'] . 'px',
                    ),
                );
            }

            // Check if image has hover
            if (!$isHover) {
                $extPos = mb_strrpos($imgPath, $imgData['ext']);
                if ($extPos !== false) {
                    $hoverPath = substr_replace($imgPath, static::HOVER_WORD . '.' . $imgData['ext'], $extPos, strlen($imgData['ext']));
                    $hasHover = isset($imgList[$hoverPath]);
                } else {
                    $hasHover = false;
                }
                if ($hasHover) {
                    $hoverData = $imgList[$hoverPath];
                    $cssList[] = array(
                        'selectors' => array(
                            "{$class}:hover",
                            "{$class}.hover",
                            ".hover-{$this->cssNamespace}:hover {$class}",
                        ),
                        'styles' => array(
                            'background-position'   => '-' . $hoverData['x'] . 'px 0',
                            'height'                => $hoverData['height'] . 'px',
                            'width'                 => $hoverData['width'] . 'px',
                        ),
                    );
                }
            }

            // Append tag
            if (!$isHover) {
                $tagList[] = '<i class="' . mb_substr($class, 1) . '"></i>';
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

        // Return list of tags
        return $tagList;
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