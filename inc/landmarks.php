<?php

namespace Derweili\WP_Recognition;

/**
 *
 */
class Landmarks
{

  public $taxonomy = 'wp_rec_landmarks';

  public $landmark_term_meta_key = '_wp_recognition_landmarks_name';
  public $landmark_coordinates_term_meta_key = '_wp_recognition_landmark_coordinates';

  public static $cached_labels = [];


  public function register_taxonomy(){
    // $args = array(
    //     'label'        => __( 'Label', 'wp-recognition' ),
    //     // 'public'       => true,
    //     'rewrite'      => true,
    //     'hierarchical' => true,
    //     // 'show_admin_column' => true,
    // );
    //
    // register_taxonomy( $this->taxonomy, 'attachment', $args );

    $labels = array(
  		'name'              => _x( 'Landmarks', 'taxonomy general name', 'textdomain' ),
  		'singular_name'     => _x( 'Landmark', 'taxonomy singular name', 'textdomain' ),
  		'search_items'      => __( 'Search Landmarks', 'textdomain' ),
  		'all_items'         => __( 'All Landmarks', 'textdomain' ),
  		'parent_item'       => __( 'Parent Landmark', 'textdomain' ),
  		'parent_item_colon' => __( 'Parent Landmark:', 'textdomain' ),
  		'edit_item'         => __( 'Edit Landmark', 'textdomain' ),
  		'update_item'       => __( 'Update Landmark', 'textdomain' ),
  		'add_new_item'      => __( 'Add New Landmark', 'textdomain' ),
  		'new_item_name'     => __( 'New Landmark Name', 'textdomain' ),
  		'menu_name'         => __( 'Landmarks', 'textdomain' ),
  	);

  	$args = array(
  		'hierarchical'      => true,
  		'labels'            => $labels,
  		'show_ui'           => true,
  		'show_admin_column' => true,
  		'query_var'         => true,
  		'rewrite'           => array( 'slug' => 'landmark' ),
  	);

  	register_taxonomy( $this->taxonomy, array( 'attachment' ), $args );

  }


  public function get_landmark( $name ){

    $args = array(
      'hide_empty' => false, // also retrieve terms which are not used yet
      'meta_query' => array(
          array(
             'key'       => $this->landmark_term_meta_key,
             'value'     => $name,
             'compare'   => 'LIKE'
          )
      ),
      'taxonomy'  => $this->taxonomy,
      'number' => 1
    );

    $terms = get_terms( $args );

    if ($terms) {
      return $terms[0]->term_id;
    }
  }

  public function create_landmark( $name, $args = [] ){

    $inserted_term = wp_insert_term(
      $name, // the term
      $this->taxonomy // the taxonomy,
    );

    $new_term_id = $inserted_term["term_id"];

    // save original name to meta
    add_term_meta($new_term_id, $this->landmark_term_meta_key, $name, true);

    if($args['coordinates']){
      // save coordinates to meta
      add_term_meta($new_term_id, $this->landmark_coordinates_term_meta_key, $args['coordinates'], true);
    }

    return $new_term_id;

  }

}
