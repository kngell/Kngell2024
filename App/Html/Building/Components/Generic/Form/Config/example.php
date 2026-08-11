<?php

declare(strict_types=1);

// Complete usage example:
$formConfig = FormConfig::create('product_form')
    ->setHeaderTitle('Product Management')
    ->setSubmitText('Save Product')
    ->setFormHandlerClass(ProductFormHandler::class)
    ->setValidatorClass(ProductValidator::class);

// Add dropzone configs
$heroDropzone = DropzoneConfig::create('hero_dropzone')
    ->setFieldName('hero_image')
    ->setDragText('Drop hero image here')
    ->setMultiple(false);

$galleryDropzone = DropzoneConfig::create('gallery_dropzone')
    ->setFieldName('gallery_images')
    ->setMultiple(true)
    ->setDragText('Drop gallery images here');

$formConfig->addDropzone($heroDropzone);
$formConfig->addDropzone($galleryDropzone);

// Add regular section with fields
$detailsSection = RegularSectionConfig::create('details', 'Product Details')
    ->addField(FormFieldConfig::create('name', 'text')->setLabel('Product Name')->setRequired(true))
    ->addField(FormFieldConfig::create('price', 'number')->setLabel('Price')->setRequired(true))
    ->addField(FormFieldConfig::create('description', 'textarea')->setLabel('Description')->setRows(5));

// Add media section with dropzone
$heroSection = MediaSectionConfig::create('hero', 'Hero Image', $heroDropzone)
    ->setHasAltText(true)
    ->setAltTextLabel('Hero Alt Text')
    ->setShowRequired(true);

$gallerySection = MediaSectionConfig::create('gallery', 'Product Gallery', $galleryDropzone)
    ->setHasAltText(true)
    ->setAltTextLabel('Image Description')
    ->setMultiple(true);

$formConfig->addSection($detailsSection);
$formConfig->addSection($heroSection);
$formConfig->addSection($gallerySection);