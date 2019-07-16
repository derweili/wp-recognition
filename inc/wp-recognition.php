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

    if( defined('WP_RECOGNITION_PROCESS_ON_UPLOAD') && WP_RECOGNITION_PROCESS_ON_UPLOAD ){

      // add_action('wp_generate_attachment_metadata', array(&$this, 'recognize_on_upload' ), 20, 2);
      add_action('add_attachment', array(&$this, 'recognize_on_upload' ), 20);
    }

    $altTags = new AltTags();
    add_filter( 'wp_get_attachment_image_attributes', array( $altTags, 'alt_tag_fallback' ), 20, 3);

  }

  /**
   *
   */
  function recognize_on_upload( $attachment_id ){

    $already_recognized = get_post_meta($attachment_id, '_wp_recognition_media_recognized', true);

    if( ! $already_recognized ){
      $recognizer = new Recognizer();

      $recognizer->recognize_media($attachment_id);

      do_action('after_image_recognition_process');

    }

  }

}
