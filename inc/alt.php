<?php

namespace Derweili\WP_Recognition;

/**
 *
 */
class AltTags
{

  function alt_tag_fallback( $attr, $attachment, $size ){
    // don't overwrite alt tag when already set
    if ( '' != $attr['alt']) {
      return $attr;
    }
    $terms = get_the_terms($attachment, 'wp_rec_label');
    if ($terms) {
      $newAlt = 'Dieses Bild könnte enhalten: ';
      $first = true;
      foreach ($terms as $term) {
        if ( ! $first) $newAlt .= ', ';
        $newAlt .= $term->name;
        $first = false;
      }
      $attr['alt'] = $newAlt;
    }

    return $attr;

  }
}
