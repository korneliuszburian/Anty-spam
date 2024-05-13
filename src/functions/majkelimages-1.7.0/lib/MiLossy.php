<?php

class MiLossy extends MiBase
{
    /** @var string Srcset attribute of the image */
    private $srcset;

    /** @var bool Determines whether the lightbox should be used */
    private $useLightbox;

    /** @var string URL to the lightbox image. By default it is set to the original image */
    private $lightboxImage;

    /** @var string Title for lightbox */
    private $lightboxTitle;

    /** @var string Descrption for lightbox */
    private $lightboxDescription;

    /** @var bool Determines whether to use auto sizes or not */
    private $useAutoSizes;

    /** @var string Custom sizes attribute */
    private $sizes;

    /** @var ?string Custom aspect ratio for the image */
    private $customAspectRatio = null;

    /** @var array Contains allowed extenstions for the file */
    private static $allowedExtensions = ['jpg', 'jpeg', 'png'];

    /** @var bool Determines whether the figcaption should be used */
    protected $useCaption;

    /** @var bool Determines whether to use loader background with gif */
    protected $useLoader;

    /** @var bool Determines whether to use lazy loading */
    protected $lazyloadEnabled = true;

    /** @var string Caption for the image. Default value is equal to the image's title. See UseCaption($caption) for the usage. */
    protected $caption;

    /**
     * Initialize object
     *
     * @param string $url The URL to the image
     * @param string $class 'class' attribute value for the image
     * @param string $style 'style' attribute value for the image
     * @param string $id 'id' attribute value for the image
     * @return void
     */
    public function __construct(string $url, ?string $class = "", ?string $style = "", ?string $id = "")
    {
        $url = $this->ValidateFile($url, self::$allowedExtensions);
        if (!$url || empty($url)) return;

        parent::__construct($url, $class, $style, $id);

        $this->useAutoSizes = true;
        $this->lightboxImage = $this->url;
        $this->GenerateScrsets();
    }

    /**
     * Method that forces the usage of figcaption 
     *
     * @param  string $caption Assign your custom caption
     * @return MiLossy Returns $this
     */
    public function UseCaption(?string $caption = null)
    {
        $this->caption    = $caption ?? $this->caption;
        $this->useCaption = true;

        return $this;
    }

    /**
     * Method that sets image size to fill the parent.
     *
     * @param  string $sizes Sizes attribute for picture
     * @return MiLossy Returns $this
     */
    public function UseSizes(string $sizes)
    {
        $this->useAutoSizes    = false;
        $this->sizes           = $sizes;

        return $this;
    }

    /**
     * Method that forces the image to fit size of its parent
     *
     * @return MiLossy Returns $this
     */
    public function UseAutoSize(): MiLossy
    {
        $this->isAutoWidth = true;
        return $this;
    }

    /**
     * Method that disables lazyload
     *
     * @return MiLossy Returns $this
     */
    public function DisableLazyLoad()
    {
        $this->lazyloadEnabled = false;
        return $this;
    }

    /**
     * Method that ensures the image is using loader background and loading gif
     *
     * @return MiLossy Returns $this
     */
    public function UseLoader()
    {
        $this->useLoader = true;
        return $this;
    }

    /**
     * Method responsible for generating an srcset strings from thumbnails
     *
     * @return void
     */
    private function GenerateScrsets()
    {
        $srcset = str_replace(get_site_url(), '', wp_get_attachment_image_srcset($this->id));

        if (empty($srcset)) {
            $info   = wp_get_attachment_image_src($this->id);
            $srcset = $this->url . ' ' . $info[1] . 'w';
        }

        $this->srcset['default'] = $srcset;
        $this->srcset['avif']    = str_replace(self::$allowedExtensions, 'avif', $this->srcset['default']);
        $this->srcset['webp']    = str_replace(self::$allowedExtensions, 'webp', $this->srcset['default']);
    }

    /**
     * Method that forces the image to use different aspect ratio
     *
     * @param ?string $aspectRatio Overrides the default "16/10" aspect ratio
     * @return MiLossy
     */
    public function UseCustomAspectRatio(?string $aspectRatio = null)
    {
        $this->customAspectRatio = $aspectRatio ?? "16/10";
        return $this;
    }

    /**
     * Method that forces the usage of lightbox. $image lets you assign your custom lightbox image
     *
     * @param ?string $image Custom image url
     * @param ?string $title Custom image title
     * @param ?string $description Custom image description
     * @return MiLossy
     */
    public function UseLightbox(?string $image = null, ?string $title = null, ?string $description = null)
    {
        $this->lightboxImage       = $image ?? $this->lightboxImage;
        $this->lightboxTitle       = $title ?? $this->lightboxTitle;
        $this->lightboxDescription = $description ?? $this->lightboxDescription;
        $this->useLightbox         = true;

        return $this;
    }

    /**
     * New method for drawing images.
     *
     * @return string
     */
    public function Draw()
    {
        $output = "";
        $id = $this->attributes['id'] ? " id=\"{$this->attributes['id']}\"" : null;

        $autoWidthClass = $this->isAutoWidth ? "w-100 occ nodrag" : null;
        $aspectRatio = $this->customAspectRatio ?? "{$this->attributes['width']}/{$this->attributes['height']}";
        $aspectRatioStyle = !$this->isAutoWidth ? " style=\"aspect-ratio: {$aspectRatio}\"" : null;

        $style = " style=\"";
        if (!$this->isAutoWidth) {
            $style .= "aspect-ratio:{$aspectRatio};";
        }

        if ($this->useLoader && $this->extension == 'png') {
            $style .= "background-color:#f6f6f6";

            if ($this->attributes['style']) {
                $style .= ";{$this->attributes['style']}\"";
            }
        } else {
            if ($this->attributes['style']) {
                $style .= $this->attributes['style'];
            }
        }
        $style .= "\"";

        if ($this->useCaption)
            $output .= $this->useCaption ? "<figure class=\"ar-figure\">" : null;

        if ($this->useLightbox) {
            $dataTitle = $this->lightboxTitle ? "data-title=\"{$this->lightboxTitle}\"" : null;
            $dataDescription = $this->lightboxDescription ? "data-description=\"{$this->lightboxDescription}\"" : null;

            $output .= "<a href=\"{$this->lightboxImage}\" class=\"l\" $dataTitle $dataDescription>";
        }

        if (!$this->isAutoWidth) {
            $this->attributes['class'] .= ' m-img--ar';
        }

        if ($this->useLoader) {
            $this->attributes['class'] .= ' lazyload-fade';
        }

        if (!$this->lazyloadEnabled) {
            $output .=
                "<picture class=\"m-img d-inline-flex\"{$aspectRatioStyle}>
                <source
                    srcset=\"{$this->srcset['avif']}\"
                    sizes=\"{$this->sizes}\"
                    type=\"image/avif\">

                <source
                    srcset=\"{$this->srcset['webp']}\"
                    sizes=\"{$this->sizes}\"
                    type=\"image/webp\">

                <source
                    srcset=\"{$this->srcset['default']}\"
                    sizes=\"{$this->sizes}\"
                    type=\"image/{$this->extension}\">
                    
                <img 
                    {$id}
                    decoding=\"async\"
                    width=\"{$this->attributes['width']}\" 
                    height=\"{$this->attributes['height']}\"
                    class=\"m-img__i {$autoWidthClass} {$this->attributes['class']}\"
                    src=\"{$this->url}\"
                    alt=\"{$this->attributes['alt']}\"
                    {$style}>
            </picture>";
        } else {
            $output .=
                "<picture class=\"m-img d-inline-flex" . ($this->useLoader ? ' m-img--loader' : null) . "\"{$aspectRatioStyle}>
                <source
                    data-srcset=\"{$this->srcset['avif']}\"
                    srcset=\"data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw== 1w\"
                    sizes=\"" . ($this->useAutoSizes ? '1px' : $this->sizes) . "\"
                    type=\"image/avif\">

                <source
                    data-srcset=\"{$this->srcset['webp']}\"
                    srcset=\"data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw== 1w\"
                    sizes=\"" . ($this->useAutoSizes ? '1px' : $this->sizes) . "\"
                    type=\"image/webp\">

                <source
                    data-srcset=\"{$this->srcset['default']}\"
                    srcset=\"data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw== 1w\"
                    sizes=\"" . ($this->useAutoSizes ? '1px' : $this->sizes) . "\"
                    type=\"image/{$this->extension}\">
                    
                <img 
                    {$id}
                    decoding=\"async\"
                    loading=\"lazy\"
                    width=\"{$this->attributes['width']}\" 
                    height=\"{$this->attributes['height']}\"
                    class=\"m-img__i {$autoWidthClass} lazyload {$this->attributes['class']}\"
                    " . ($this->useAutoSizes ? 'data-sizes="auto"' : null) . "
                    data-optimumx=\"auto\"
                    data-src=\"{$this->url}\"
                    src=\"#\"
                    alt=\"{$this->attributes['alt']}\"
                    {$style}>
            </picture>";
        }

        if ($this->useLightbox)
            $output .= '</a>';

        if ($this->useCaption) {
            $output .= "<figcaption class=\"ar-img__caption\">{$this->caption}</figcaption>";
            $output .= "</figure>";
        }

        echo $output;
    }
}