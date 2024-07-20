<?php

/**
 * @package NRDFacebookImporter
 */

namespace Inc\Api;

use \WP_Query;

class CreatePostApi
{

  public function syncPosts(array $api_data, $menu_id)
  {
    
  }

  public function unpublishPost($post_id)
  {
    
  }

  public function updatePost(array $post, $existing_post_id)
  {
   
  }
  
  public function createPost(array $post)
  {
    
  }

  private function addTermsToPost($post_id, $terms, $taxonomy)
  {
    // Ensure terms are an array
    if (!is_array($terms)) {
      $terms = array($terms);
    }

    // Set the terms for the post
    wp_set_object_terms($post_id, $terms, $taxonomy);
    
  }


  function getPostIdByFacebookEventId($facebook_event_id, $post_type)
  {
    $args = array(
        'post_type' => $post_type,
        'meta_query' => array(
            array(
                'key' => 'facebook_event_id',
                'value' => $facebook_event_id,
                'compare' => '='
            )
        ),
        'fields' => 'ids',
        'posts_per_page' => 1
    );

    $query = new WP_Query($args);

    // Check if there are posts
    if ($query->have_posts()) {
        // Get the first post ID
        $post_id = $query->posts[0];

        // Restore original post data
        wp_reset_postdata();

        return $post_id;
    } else {
        return null;
    }
  }

  public function getPostIdsOfPostType($post_type)
  {
    $args = array(
      'post_type' => $post_type,
      'posts_per_page' => -1,
      'fields' => 'ids'
    );

    $query = new WP_Query($args);

    // Check if there are posts
    if ($query->have_posts()) {
      return $query->posts; 
    } else {
      return array();
    }
  }

  public function removePostIdFromArray($post_ids, $remove_id)
  {
    if (($key = array_search($remove_id, $post_ids)) !== false) {
      unset($post_ids[$key]);
    }
    // Reindex array to avoid gaps in the keys
    $post_ids = array_values($post_ids);
    return $post_ids;
  }



}