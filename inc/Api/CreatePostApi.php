<?php

/**
 * @package NRDFacebookImporter
 */

namespace NrdFacebookImporter\Inc\Api;

use \WP_Query;

class CreatePostApi
{

  public function syncPosts(array $api_data, $post_type)
  {
    $existing_post_ids = $this->getPostIdsOfPostType($post_type);

    foreach ($api_data as $data) {
      // Create post object
      $post = [
        'post_title' => $data['name'],
        'post_content' => $data['description'],
        'post_type' => $post_type,
        'custom_fields' => [
            'nrdfi_event_id' => $data['id'],
            'nrdfi_event_start_time' => $data['start_time'],
            'nrdfi_event_end_time' => $data['end_time'],
            'nrdfi_event_img_url' => $data['image_url'],
            'nrdfi_event_url' => $data['event_url']
          ]
      ];

      // Check if the post already exists
      $existing_post_id = $this->getPostIdByFacebookEventId($post['custom_fields']['nrdfi_event_id'], $post['post_type']);

      // Date
      $timezone = new \DateTimeZone('America/Chicago');
      $date = new \DateTime($post['custom_fields']['nrdfi_event_end_time']);
      $now = new \DateTime('now', $timezone);

      if (isset($existing_post_id)) {
        
        if ($date->format('Y-m-d') < $now->format('Y-m-d')) {
          $this->deletePost($existing_post_id);
        } else {
          // Post exists, and date is in the future update it
          $post_id = $this->updatePost($post, $existing_post_id);
          $existing_post_ids = $this->removePostIdFromArray($existing_post_ids, $post_id);
        }
        
      } else if($date->format('Y-m-d') >= $now->format('Y-m-d')) {
        // Post does not exist, create it
        $post_id = $this->createPost($post);
        $existing_post_ids = $this->removePostIdFromArray($existing_post_ids, $post_id);
      }
    }
  }

  public function updatePost(array $post, $existing_post_id)
  {
    $new_post = [
      'ID' => $existing_post_id,
      'post_title' => wp_strip_all_tags($post['post_title']),
      'post_content' => $post['post_content'],
      'post_status' => 'publish',
      'post_author' => 1,
      'post_type' => $post['post_type']
    ];
    $post_id = wp_update_post($new_post);

    if (is_wp_error($post_id)) {
      return $post_id->get_error_message();
    } else {
      $custom_fields = $post['custom_fields'];

      // Add or update custom fields if provided
      if (!empty($custom_fields)) {
        foreach ($custom_fields as $key => $value) {
          update_post_meta($post_id, $key, $value);
        }
      }
    }
    return $post_id;
  }
  
  public function createPost(array $post)
  {
    $new_post = [
      'post_title' => wp_strip_all_tags($post['post_title']),
      'post_content' => $post['post_content'],
      'post_status' => 'publish',
      'post_author' => 1,
      'post_type' => $post['post_type']
    ];
    $post_id = wp_insert_post($new_post);

    if (is_wp_error($post_id)) {
      return $post_id->get_error_message();
    } else {
      $custom_fields = $post['custom_fields'];

      // Add or update custom fields if provided
      if (!empty($custom_fields)) {
        foreach ($custom_fields as $key => $value) {
          update_post_meta($post_id, $key, $value);
        }
      }
    }
    return $post_id;
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
                'key' => 'nrdfi_event_id',
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

  public function deletePost($post_id)
  {
    // Delete the post and its meta fields
    $result = wp_delete_post($post_id, true); // The second parameter 'true' forces deletion

    if ($result) {
      return 'Post deleted successfully.';
    } else {
      return new WP_Error('delete_failed', 'Failed to delete the post.');
    }
  }



}