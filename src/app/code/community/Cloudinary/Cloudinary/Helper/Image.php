<?php

use CloudinaryExtension\Cloud;
use CloudinaryExtension\CloudinaryImageProvider;
use CloudinaryExtension\Image;
use CloudinaryExtension\Image\Dimensions;
use CloudinaryExtension\ImageManager;

class Cloudinary_Cloudinary_Helper_Image extends Mage_Catalog_Helper_Image
{
    private $_imageManager;

    private $_dimensions;

    private $_attributeName;

    public function init(Mage_Catalog_Model_Product $product, $attributeName, $imageFile = null)
    {
        $this->_attributeName = $attributeName;
        $config = Mage::helper('cloudinary_cloudinary/configuration');

        $this->_imageManager = new ImageManager(new CloudinaryImageProvider(
            $config->buildCredentials(),
            Cloud::fromName($config->getCloudName())
        ));

        parent::init($product, $attributeName, $imageFile);

        return $this;
    }

    public function resize($width, $height = null)
    {
        $this->_dimensions = Dimensions::fromWidthAndHeight($width, $height ?: $width);

        return $this;
    }

    public function __toString()
    {
        $imageFile = $this->getRequestedImageFile();

        if ($this->isImageSpecified($imageFile)) {
            $image = Image::fromPath($imageFile);

            if ($this->_dimensions) {
                $transformation = Image\Transformation::toDimensions($this->_dimensions);
                return $this->_imageManager->getUrlForImageWithTransformation($image, $transformation);
            } else {
                return $this->_imageManager->getUrlForImage($image);
            }

        }

        return Mage::getDesign()->getSkinUrl($this->getPlaceholder());
    }

    private function isImageSpecified($imageFile)
    {
        return $imageFile && $imageFile !== 'no_selection';
    }

    /**
     * @return mixed
     */
    private function getRequestedImageFile()
    {
        return $this->getImageFile() ?: $this->getProduct()->getData($this->_attributeName);
    }
}