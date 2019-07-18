<?php

namespace Derweili\WP_Recognition;

/**
 *
 */
class Labels
{

  public $taxonomy = 'wp_rec_label';

  public $label_term_meta_key = '_wp_recognition_aws_label_name';

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
  		'name'              => _x( 'Labels', 'taxonomy general name', 'textdomain' ),
  		'singular_name'     => _x( 'Label', 'taxonomy singular name', 'textdomain' ),
  		'search_items'      => __( 'Search Labels', 'textdomain' ),
  		'all_items'         => __( 'All Labels', 'textdomain' ),
  		'parent_item'       => __( 'Parent Label', 'textdomain' ),
  		'parent_item_colon' => __( 'Parent Label:', 'textdomain' ),
  		'edit_item'         => __( 'Edit Label', 'textdomain' ),
  		'update_item'       => __( 'Update Label', 'textdomain' ),
  		'add_new_item'      => __( 'Add New Label', 'textdomain' ),
  		'new_item_name'     => __( 'New Label Name', 'textdomain' ),
  		'menu_name'         => __( 'Labels', 'textdomain' ),
  	);

  	$args = array(
  		'hierarchical'      => true,
  		'labels'            => $labels,
  		'show_ui'           => true,
  		'show_admin_column' => true,
  		'query_var'         => true,
  		'rewrite'           => array( 'slug' => 'label' ),
  	);

  	register_taxonomy( $this->taxonomy, array( 'attachment' ), $args );
  }


  function assign_labels_to_attachment( $labels, $attachment_id ){

    $label_term_ids = [];

    foreach ($labels as $label) {

      // $label_name = $label["Name"];

      $new_label_term_ids = $this->get_label_terms($label);
      $label_term_ids = array_merge($label_term_ids, $new_label_term_ids);

    }

    $label_term_ids = array_unique( $label_term_ids );

    wp_set_object_terms( $attachment_id, $label_term_ids, $this->taxonomy );

  }

  function assign_google_labels_to_attachment( $labels, $attachment_id ){

    $label_term_ids = [];

    $label_names = [];

    foreach ($labels as $label) { $label_names[] = $label->description(); $label_term_ids[] = $this->get_label_term_id($label->description()); }


    $label_term_ids = array_unique( $label_term_ids );

    wp_set_object_terms( $attachment_id, $label_term_ids, $this->taxonomy );

  }

  /**
   * Get Labels by Label Object (containing Labels and all parents)
   *
   * return term_id
   */
  function get_label_terms( $label_object ){

    $labels = array_reverse( $label_object["Parents"] );
    $labels[] = ["Name" => $label_object["Name"]];

    $parent_term_id = false;
    $label_terms = [];
    foreach ($labels as $label) {

      $name = apply_filters( 'wp_recognition_label_name', $label["Name"] );

      $term_id = $this->get_label_term($name);

      if($term_id){
        $label_terms[] = $term_id;
        $parent_term_id = $term_id;
      }else{
        $inserted_term_id = $this->create_label_term($name, $parent_term_id);
        $label_terms[] = $inserted_term_id;
        $parent_term_id = $inserted_term_id;
      }

    }

    return $label_terms;

  }

  /**
   * Get Label ID by Name
   */
  function get_label_term_id( $name ){
    $name = apply_filters( 'wp_recognition_label_name', $name );

    $term_id = $this->get_label_term($name);
    if($term_id){
      return $term_id;
    }else{
      $inserted_term_id = $this->create_label_term($name, false);
      return $inserted_term_id;
    }

  }


  function create_label_term( $name, $parent_term_id = false ){

    $args = [];
    $args['parent'] = $parent_term_id ? $parent_term_id : 0;

    // $original_name = $name;
    // $name = apply_filters( 'wp_recognition_label_name', $name );

    $inserted_term = wp_insert_term(
      $name, // the term
      $this->taxonomy, // the taxonomy,
      $args
    );
    if( is_wp_error( $inserted_term ) ) {
      error_log('term insert error');
      error_log($inserted_term->get_error_message());
    }

    $new_term_id = $inserted_term["term_id"];

    Labels::$cached_labels[$name] = $new_term_id;

    add_term_meta($new_term_id, $this->label_term_meta_key, $name, true);

    return $new_term_id;

  }

  function get_label_term( $name ){

    // get label term from cache
    if(isset(Labels::$cached_labels[$name])) return Labels::$cached_labels[$name];

    $args = array(
      'hide_empty' => false, // also retrieve terms which are not used yet
      'meta_query' => array(
          array(
             'key'       => $this->label_term_meta_key,
             'value'     => $name,
             'compare'   => 'LIKE'
          )
      ),
      'taxonomy'  => $this->taxonomy,
      'number' => 1
    );

    $terms = get_terms( $args );

    if ($terms) {
      Labels::$cached_labels[$name] = $terms[0]->term_id; // store in cache
      return $terms[0]->term_id;
    }
  }


}
