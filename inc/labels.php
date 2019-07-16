<?php

namespace Derweili\WP_Recognition;

/**
 *
 */
class Labels
{

  public $taxonomy = 'wp_recognition_label';

  public $label_term_meta_key = '_wp_recognition_aws_label_name';

  function __construct()
  {
    // code...
  }

  function register_taxonomy(){
    $args = array(
        'label'        => __( 'Label', 'wp-recognition' ),
        'public'       => true,
        'rewrite'      => true,
        'hierarchical' => true,
        'show_admin_column' => true,
    );

    register_taxonomy( $this->taxonomy, 'attachment', $args );
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

  /**
   * Get Labels by Label Object (containing Labels and all parents)
   *
   * return term_id
   */
  function get_label_terms( $label_object ){

    $labels = array_reverse( $label_object["Parents"] );
    $labels[] = ["Name" => $label_object["Name"]];

    var_dump('new labels_array', $labels); echo PHP_EOL;

    $parent_term_id = false;
    $label_terms = [];
    foreach ($labels as $label) {
      $term_id = $this->get_label_term($label["Name"]);
      if($term_id){
        $label_terms[] = $term_id;
        $parent_term_id = $term_id;
      }else{
        $inserted_term_id = $this->create_label_term($label["Name"], $parent_term_id);
        $label_terms[] = $inserted_term_id;
        $parent_term_id = $inserted_term_id;
      }

    }

    return $label_terms;

  }


  function create_label_term( $name, $parent_term_id = false ){

    $args = [];
    $args['parent'] = $parent_term_id ? $parent_term_id : 0;

    $name = apply_filters( 'wp_recognition_label_name', $name );

    $inserted_term = wp_insert_term(
      $name, // the term
      $this->taxonomy, // the taxonomy,
      $args
    );

    $new_term_id = $inserted_term["term_id"];

    add_term_meta($new_term_id, $this->label_term_meta_key, $name, true);

    return $new_term_id;

  }

  function get_label_term( $name){
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
      return $terms[0]->term_id;
    }
  }


}
