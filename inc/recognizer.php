<?php

namespace Derweili\WP_Recognition;

use Aws\Rekognition\RekognitionClient;
use Aws\Rekognition\Exception\RekognitionException;
use \Gumlet\ImageResize;

/**
 *
 */
class Recognizer
{

  /**
   * RecognitionClient
   */
  private $client;


  function __construct()
  {

    $this->init_recognition_client();

  }

  function init_recognition_client(){
    $options = [
      'region' => 'us-west-2',
      'version' => 'latest',
      'credentials' => array(
          'key' => WP_RECOGNITION_KEY,
          'secret'  => WP_RECOGNITION_SECRET,
        )
      ];
    $this->client = new RekognitionClient($options);
  }

  function recognize_media( $attachment_id ){

    $image = $this->get_image($attachment_id);

    // $fp_image = fopen($path, 'r');
    //   $image = fread($fp_image, filesize($path));
    // fclose($fp_image);

    $labels = $this->detectLabels( $image );
    $this->assignLabels($labels, $attachment_id);

    update_post_meta( $attachment_id, '_wp_recognition_media_recognized', true );
    update_post_meta( $attachment_id, '_wp_recognition_media_recognized_time', time( ));

  }

  function get_image( $attachment_id ){
    $path = get_attached_file( $attachment_id );


    $image = new ImageResize($path);
    $image->resizeToWidth(2000);
    $image_string = $image->getImageAsString();
    return $image_string;
    // return $this->scaled_image_path( $attachment_id, 'large');
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


  function detectLabels( $image ){
    try {

      $result = $this->client->detectLabels([
        'Image' => [ // REQUIRED
            'Bytes' => $image,
            // 'S3Object' => [
            //     'Bucket' => '<string>',
            //     'Name' => '<string>',
            //     'Version' => '<string>',
            // ],
        ],
        'MaxLabels' => 50,
        'MinConfidence' => 80,
      ]);
    }catch (RekognitionException $e) {
      // output error message if fails
      echo "\n";
      echo 'Error' . PHP_EOL;
      echo $e->getMessage();
      echo "\n";
      die();
    }

    $labels = $result->get("Labels");
    return $labels;
  }


  function assignLabels($labels, $attachment_id){

    $labels_helper = new Labels();
    $labels_helper->assign_labels_to_attachment( $labels, $attachment_id );


  }

}
