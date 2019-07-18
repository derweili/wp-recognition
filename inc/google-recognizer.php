<?php

namespace Derweili\WP_Recognition;

use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Google\Cloud\Vision\Image;
use Google\Cloud\Vision\VisionClient;

use \Gumlet\ImageResize;
use \Gumlet\ImageResizeException;

/**
 *
 */
class Google_Vision_Recognizer
{

  /**
   * RecognitionClient
   */
  private $client;


  function __construct()
  {

    error_log( 'Usin Google Vision') ;
    $this->init_recognition_client();

  }

  function init_recognition_client(){
    $this->client = new VisionClient([
        // 'keyFilePath' => __DIR__ . '/instabot-7e0f6b188c9b.json'
        // 'keyFile' => json_decode(file_get_contents(__DIR__ . '/instabot-7e0f6b188c9b.json'), true)
        'keyFile' => json_decode(WP_RECOGNITION_GOOGLE_CONFIG, true)
    ]);
  }

  function recognize_media( $attachment_id ){

    $mimetype = get_post_mime_type($attachment_id);
    if('image/jpeg' !== $mimetype && 'image/png' !== $mimetype ) return;

    $image = $this->get_image($attachment_id);

    if( ! $image ) return;

    // $fp_image = fopen($path, 'r');
    //   $image = fread($fp_image, filesize($path));
    // fclose($fp_image);

    $result = $this->detectImage( $image );
    $this->assignLabels($result->labels(), $attachment_id);
    $this->assignEntities($result->web()->info(), $attachment_id);
    $this->assignLandmarks($result->landmarks(), $attachment_id);

    update_post_meta( $attachment_id, '_wp_recognition_media_recognized', true );
    update_post_meta( $attachment_id, '_wp_recognition_media_recognized_time', time( ));

  }

  function get_image( $attachment_id ){
    $path = get_attached_file( $attachment_id );
    try {

      $image = new ImageResize($path);
      $image->resizeToWidth(2000);
      $image_string = $image->getImageAsString();


    }catch (ImageResizeException $e) {
      return false;
    }

    return $image_string;

    // return $this->scaled_image_path( $attachment_id, 'large');
  }

  function get_image_path($attachment_id){
    $file = get_attached_file($attachment_id, true);
    return realpath($file);
  }

  function scaled_image_path($attachment_id, $size = 'thumbnail') {
      $file = get_attached_file($attachment_id, true);
      if (empty($size) || $size === 'full') {
          // for the original size get_attached_file is fine
          return realpath($file);
      }
      if (! wp_attachment_is_image($attachment_id) ) {
          return false; // the id is not referring to a media
      }
      $info = image_get_intermediate_size($attachment_id, $size);
      if (!is_array($info) || ! isset($info['file'])) {
          return false; // probably a bad size argument
      }

      return realpath(str_replace(wp_basename($file), $info['file'], $file));
  }


  function detectImage( $image ){
    try {

      $imageFile = $image;

      $image = $this->client->image($imageFile, [
          // 'FACE_DETECTION',
          'LANDMARK_DETECTION',
          // 'LOGO_DETECTION',
          'LABEL_DETECTION',
          // 'TEXT_DETECTION',
          // 'DOCUMENT_TEXT_DETECTION',
          // 'SAFE_SEARCH_DETECTION',
          // 'IMAGE_PROPERTIES',
          // 'CROP_HINTS',
          'WEB_DETECTION'
      ]);

      $result = $this->client->annotate($image);

    }catch (\Exception $e) {
      // output error message if fails
      echo "\n";
      echo 'Error' . PHP_EOL;
      echo $e->getMessage();
      echo "\n";
      die();
    }

    return $result;
  }


  function assignLabels($labels, $attachment_id){

    $labels_helper = new Labels();
    $labels_helper->assign_google_labels_to_attachment( $labels, $attachment_id );

  }


  function assignEntities($entities, $attachment_id){
    $entity_names = [];
    $entity_label_ids = [];

    $labels_helper = new Labels();

    foreach ($entities["webEntities"] as $entity) {
      if(isset($entity['description'])){
        $name = $entity['description'];
        $entity_names[] = $name;
        $entity_label_ids[] = $labels_helper->get_label_term_id($name);
      }
    }

    wp_set_object_terms( $attachment_id, $entity_label_ids, $labels_helper->taxonomy, true );

  }


  function assignLandmarks( $landmarks, $attachment_id ){
    $landmark_helper = new Landmarks();

    $landmark_ids = [];

    // check if we have landmarks
    if( ! $landmarks ) return;
    foreach ($landmarks as $landmark_obj) {
      $landmark = $landmark_obj->info();
      $name = $landmark["description"];

      $coordinates = [];
      if($landmark["locations"]){
        $coordinates = $landmark["locations"][0]["latLng"];
      }

      $new_landmark = array(
        'name' => $name,
        'coordinates' => $coordinates,
      );

      $landmark_id = $landmark_helper->get_landmark($new_landmark["name"]);
        if(!$landmark_id) $landmark_id = $landmark_helper->create_landmark($new_landmark["name"], $new_landmark);

      $landmark_ids[] = $landmark_id;

    }

    wp_set_object_terms( $attachment_id, $landmark_ids, $landmark_helper->taxonomy );

  }

}
