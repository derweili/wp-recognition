<?php
namespace Derweili\WP_Recognition;

if(class_exists('\WP_CLI')){
  /**
   * Implements example command.
   */
  class CLI_Command {

      /**
       * Re
       *
       * ## OPTIONS
       *
       * [<id>...]
       * : The name of the person to greet.
       *
       * [--all]
       * : Whether or not to greet the person with success or error.
       * ---
       * default: success
       * options:
       *   - success
       *   - error
       * ---
       *
       * ## EXAMPLES
       *
       *     wp recognition recognize 20
       *
       * @when after_wp_load
       */
      function recognize( $args, $assoc_args ) {

          if ($args) {
            $images_to_process = $args;
            \WP_CLI::success( "process specific images" );
          }elseif( isset( $assoc_args['all'] ) ){
            // process all images
            \WP_CLI::success( "process all images" );
            $attachments = get_posts( array(
                'posts_per_page' => isset( $assoc_args['number'] ) ? $assoc_args['number'] : 20,
                'post_type' => 'attachment',
                'meta_query' => array(
                  array(
                     'key' => '_wp_recognition_media_recognized',
                     'compare' => 'NOT EXISTS'
                  ),
                )
            ) );

            $images_to_process = [];
            foreach ($attachments as $attachment) {
              $images_to_process[] = $attachment->ID;
            }

          }

          if ($images_to_process) {
            // code...
            $recognizer = new Recognizer();

            foreach ($images_to_process as $id) {
              $recognizer->recognize_media($id);
              \WP_CLI::log( "Image $id processed" );
            }
          }


          do_action('after_image_recognition_process');

          \WP_CLI::success( count($images_to_process) . " Images processed" );
      }
  }

  \WP_CLI::add_command( 'recognition', __NAMESPACE__ . '\CLI_Command' );
}
