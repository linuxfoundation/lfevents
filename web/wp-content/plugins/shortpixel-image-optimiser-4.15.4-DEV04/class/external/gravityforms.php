<?php
namespace ShortPixel;

// Gravity Forms integrations.
class gravityForms
{

  public function __construct()
  {
    add_filter( 'gform_save_field_value', array($this,'shortPixelGravityForms'), 10, 5 );
  }

  function shortPixelGravityForms( $value, $lead, $field, $form ) {
      if($field->type == 'post_image') {
          $this->handleGravityFormsImageField($value);
      }
      return $value;
  }

  public function handleGravityFormsImageField($value) {

      $shortPixelObj = wpSPIO()->getShortPixel();

      if(!($folder = $shortPixelObj->getSpMetaDao()->getFolder(SHORTPIXEL_UPLOADS_BASE . '/gravity_forms'))) {
          return;
      }
      if(strpos($value , '|:|')) {
          $cleanup = explode('|:|', $value);
          $value = $cleanup[0];
      }
      //ShortPixel is monitoring the gravity forms folder, add the image to queue
      $uploadDir   = wp_upload_dir();
      $localPath = str_replace($uploadDir['baseurl'], SHORTPIXEL_UPLOADS_BASE, $value);

      return $shortPixelObj->addPathToCustomFolder($localPath, $folder->getId(), 0);
  }

} // class

$g = new gravityForms();
