<?php
/**
 * Plugin name: Timber Tools
 * Plugin author: David Hewitson (shstkvch)
 * Description: Common helper functions for working with Timber
 * Version: 0.2
 */

class TimberTools {

  /**
   * Take an array of post IDs or objects and return an array of TimberPosts
   * @param  $posts an array of WP_Post objects or IDs
   * @param  $field (optional) the field within the array which contains the
   *                object.
   * @param  $thumbnail_size the size of thumbnail to use
   * @return array  of TimberPosts
   */
  static function prepare_posts( $posts, $field = '', $thumbnail_size = '' ) {
    return self::prepare_objects( $posts, 'TimberPost', $field, $thumbnail_size );
  }

  /**
   * Take an array of term IDs or objects and return an array of TimberTerms
   * @param  $terms an array of WP_Term objects or IDs
   * @param  $field (optional) the field within the array which contains the
   *                object.
   * @param  $thumbnail_size the size of thumbnail to use
   * @return array  of TimberTerms
   */
  static function prepare_terms( $terms, $field = '', $thumbnail_size = '' ) {
    return self::prepare_objects( $terms, 'TimberTerm', $field, $thumbnail_size );
  }

  /**
   * Take an array of image IDs or objects and return an array of TimberImages
   * @param  $terms an array of attachment objects or IDs
   * @param  $field (optional) the field within the array which contains the
   *                object.
   * @param  $thumbnail_size the size of thumbnail to use
   * @return array  of TimberImages
   */
  static function prepare_images( $images, $field = '', $thumbnail_size = '' ) {
    return self::prepare_objects( $images, 'TimberImage', $field, $thumbnail_size );
  }

  /**
   * Given an array of object IDs or objects, return the objects instantiated
   * with the given class
   * @param $objects an array of objects
   * @param $class   the class to instantiate
   * @param $field   if given, get the value of this array key as the object
   * @param  $thumbnail_size the size of thumbnail to use
   * @return array of objects
   */
  static function prepare_objects( $objects, $class = 'TimberPost', $field = '', $thumbnail_size = '' ) {
    $objects_out = array();

    if ( ! is_array( $objects ) ) {
      return $objects_out;
    }

    foreach ( $objects as $obj ) {
      if ( $field ) {
        $obj = $obj[$field];
      }

      // make sure we can handle the object
      if ( is_object( $obj ) ) {
        $id = ! $obj->ID ? $obj->term_id : $obj->ID;
      } else if ( is_array( $obj ) ) {
        $id = $obj['ID'];
      } else if ( is_numeric( $obj ) ) {
        $id = $obj;
      } else {
        continue;
      }

      // Timber will take care of dealing with post/int conversion
      $obj = new $class( $id );

      // if this is a timberimage, also add srcset
      if ( $class == 'TimberImage' ) {
        self::add_srcset_to_image( $obj, $thumbnail_size );
      }

      // if this is a TimberPost, add srcset to its thumbnail
      if ( $class == 'TimberPost' ) {
        self::add_srcset_to_image( $obj->thumbnail, $thumbnail_size );
      }

      $objects_out[] = $obj;
    }

    return $objects_out;
  }

  /**
   * Add a srcset attribute to a TimberImage. The passed TimberImage
   * will be updated by reference.
   *
   * @param  $image the TimberImage to add srcset to
   * @return void
   */
  function add_srcset_to_image( &$image, $size = '' ) {
    if ( !$image ) return;

    $image->srcset = wp_get_attachment_image_srcset( $image->id, $size );
    $image->srcset_sizes = wp_get_attachment_image_sizes( $image->id, $size );
  }

}
