<?php

abstract class MiBase
{
    /** @var bool True if image exists */
    public $exists = true;

    /** @var array Attributes of the image */
    protected $attributes;

    /** @var int The Wordpress ID of the image */
    protected $id;

    /** @var string The URL of the image */
    protected $url;

    /** @var string The extension of the file */
    protected $extension;

    /** @var int Width of the image */
    protected $width;

    /** @var int Height of the image */
    protected $height;

    /** @var bool Determines whether the image should be adjusting its size to the parent's size */
    protected $isAutoWidth = false;

    /**
     * Method responsible for outputing the image
     */
    abstract public function Draw();

    /**
     * Method that forces the image to fit size of its parent
     *
     * @return MiBase
     */
    public function UseAutoSize(): MiBase
    {
        $this->isAutoWidth = true;
        return $this;
    }

    /**
     * Method that validates given url and allowed extensions. Returns either local version of $url, or error
     *
     * @param string $url Caption of the image
     * @param array $allowedExtensions Array contains allowed extensions as strings
     * @return string $url Local version of $url
     */
    protected function ValidateFile(string $url, array $allowedExtensions)
    {
        $home = home_url();

        if (strpos($url, $home) !== false)
            $url = str_replace($home, '', $url);

        if (!file_exists(ABSPATH . $url)) {
            $this->exists = false;
            trigger_error('Image doesn\'t exist: ' . $url, E_USER_WARNING);
            return null;
        }

        $url       = $home . $url;
        $this->extension = pathinfo($url, PATHINFO_EXTENSION);

        if (!in_array($this->extension, $allowedExtensions))
            trigger_error('File extension not supported: ' . $this->extension, E_USER_ERROR);

        return $url;
    }

    /**
     * Initialize object
     *
     * @param string $url   The URL to the image
     * @param string $class 'class' attribute value for the image
     * @param string $style 'style' attribute value for the image
     * @param string $id    'id' attribute value for the image
     */
    public function __construct(string $url, ?string $class, ?string $style, ?string $id)
    {
        $this->id        = attachment_url_to_postid($url);
        $this->url       = str_replace(get_site_url(), '', $url);

        $img                        = wp_get_attachment_image_src($this->id, 'full');
        $this->attributes['title']  = get_the_title($this->id);
        $this->attributes['class']  = $class;
        $this->attributes['style']  = $style;
        $this->attributes['id']     = $id;
        $this->attributes['width']  = $img[1];
        $this->attributes['height'] = $img[2];

        $alt                     = get_post_meta($this->id, '_wp_attachment_image_alt', true);
        $this->attributes['alt'] = empty($alt) ? '' : $alt;
    }
}