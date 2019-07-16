<?php

namespace Derweili\WP_Recognition;

/**
 *
 */
class WP_Recognition
{

  function __construct()
  {
    // code...
    $this->register_hooks();

  }

  function register_hooks(){

    $labels = new Labels();

    add_action('init', array($labels, 'register_taxonomy'));

    if( defined('WP_RECOGNITION_TARGET_LANGUAGE') ){

      $translator = new Translator();
      add_filter( 'wp_recognition_label_name', array($translator, 'translate_text' ) );

    }

    add_action('wp_generate_attachment_metadata', array(&$this, 'recognize_on_upload' ), 20, 2);

  }

  /**
   *
   */
  function recognize_on_upload( $metadata, $attachment_id ){
    error_log('inserted post type: ');

    error_log(print_r($attachment_id, true));

    $already_recognized = get_post_meta($attachment_id, '_wp_recognition_media_recognized', true);
    error_log(print_r($already_recognized, true));
    if( ! $already_recognized ){
      $recognizer = new Recognizer();

      $recognizer->recognize_media($attachment_id);

    }

  }

}
