<?php

namespace Aught\SpaceBundle\Services;

class ImageUtils
{
    private $image = null;

    /**
     * Init utility class with image data
     *
     * @param  String  $blob  Binary image data
     * @return \Aught\SpaceBundle\Services\ImageUtils  Image Util Object
     */
    public function initWithBlob($blob)
    {
        try {
            $this->image = new \Imagick();
            $this->image->readImageBlob($blob);
        } catch (\Exception $e) {
            throw new \Exception('Error when initializing imagick object: ' . $e->getMessage(), 1);
        }

        return $this;
    }


    /**
     * Resize an image
     *
     * @param  int     $max_dimension Max dimension of the resulting image
     * @return String                 Thumbnail binary image data
     */
    public function resize($max_dimension = 960)
    {
        if (!$this->image) {
            throw new \Exception('Need to init image with initWithBlob()', 1);
        }

        try {
            if ($this->image->getImageHeight() <= $max_dimension && $this->image->getImageWidth() <= $max_dimension) {
                return $this->image;
            }

            // Resizes to whichever is larger, width or height
            if($this->image->getImageHeight() <= $this->image->getImageWidth()) {
                // Resize image using the lanczos resampling algorithm based on width
                $this->image->resizeImage($max_dimension,0,\Imagick::FILTER_LANCZOS,1);
            } else {
                // Resize image using the lanczos resampling algorithm based on height
                $this->image->resizeImage(0,$max_dimension,\Imagick::FILTER_LANCZOS,1);
            }
        } catch (\Exception $e) {
            throw new \Exception('Error when resizing image: ' . $e->getMessage(), 1);
        }
        return $this->image;
    }

    /**
     * Create a thumbnail from an image
     *
     * @param  int     $thumb_width  Thumbnail width
     * @param  int     $thumb_height Thumbnail height
     * @param  String  $image_format Image output format
     * @return String                Thumbnail binary image data
     */
    public function create_thumbnail($thumb_width = 80, $thumb_height = 80, $image_format = 'jpeg')
    {
        if (!$this->image) {
            throw new \Exception('Need to init image with initWithBlob()', 1);
        }

        try {
            $this->image->setImageFormat($image_format);
            $this->image->thumbnailImage($thumb_width, $thumb_height, true);
        } catch (\Exception $e) {
            throw new \Exception('Error when creating a thumbnail: ' . $e->getMessage(), 1);
        }
        return $this->image;
    }
}
