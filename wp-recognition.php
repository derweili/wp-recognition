<?php
/**
 * Plugin Name:     WP Recognition
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     Image recognition for your wordpress media
 * Author:          derweili
 * Author URI:      https://derweili.de
 * Text Domain:     wp-recognition
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         WP_Recognition
 */

// Your code starts here.
namespace Derweili\WP_Recognition;

require __DIR__ . '/vendor/autoload.php';

if( defined('WP_RECOGNITION_KEY') && defined('WP_RECOGNITION_SECRET') ){

  require __DIR__ . '/inc/labels.php';
  require __DIR__ . '/inc/recognizer.php';
  require __DIR__ . '/inc/cli.php';
  require __DIR__ . '/inc/translator.php';

  require __DIR__ . '/inc/wp-recognition.php';


  new WP_Recognition();

}
