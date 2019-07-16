=== WP Recognition ===
Contributors: derweili
Donate link: https://derweili.de/
Tags: media
Requires at least: 5.0
Tested up to: 5.2.2
Stable tag: 0.1.0
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Image recognition for WordPress media based on AWS Rekognition

== Description ==

Following configuration is required:

define( 'WP_RECOGNITION_KEY', 'AWS_API_Key' );
define( 'WP_RECOGNITION_SECRET', 'AWS_API_SECRET' );

Following configuration is optional:

define( 'WP_RECOGNITION_TARGET_LANGUAGE', 'de' ); // language code if you want to translate the labels into your own language (based on AWS Translation. Permissions required for the service are required)
define( 'WP_RECOGNITION_PROCESS_ON_UPLOAD', true ); // set to true if you want to auto recognize images on upload. Set to false if you want to do it manually (via CLI)

== Installation ==

1. Upload the plugin to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place the config in you wp-config.php


== Changelog ==
