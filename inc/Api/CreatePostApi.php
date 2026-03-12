<?php

/**
 * @package NRDFacebookImporter
 */

namespace NrdFacebookImporter\Inc\Api;

use \WP_Query;
use NrdFacebookImporter\Inc\Base\ImportLogger;

class CreatePostApi
{
  private $logger;
  private $counts = array('created' => 0, 'updated' => 0, 'deleted' => 0, 'errors' => 0);
  private $default_image_url = '';

  public function setLogger(ImportLogger $logger)
  {
    $this->logger = $logger;
  }

  public function syncPosts(array $api_data, $post_type)
  {
    if (isset($api_data['error'])) {
      if ($this->logger) {
        $this->logger->apiError('Sync aborted: ' . $api_data['error']);
      }
      return;
    }

    $this->counts = array('created' => 0, 'updated' => 0, 'deleted' => 0, 'errors' => 0);

    $options = get_option('nrd_facebook_importer_schedule', array());
    $this->default_image_url = isset($options['default_event_image']) ? $options['default_event_image'] : '';

    if ($this->logger) {
      $this->logger->syncStart();
    }

    $existing_post_ids = $this->getPostIdsOfPostType($post_type);
    $timezone = wp_timezone();
    $now = new \DateTime('now', $timezone);

    $api_data = $this->deduplicateEvents($api_data);

    foreach ($api_data as $data) {
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

      $existing_post_id = $this->getPostIdByFacebookEventId($post['custom_fields']['nrdfi_event_id'], $post['post_type']);

      $is_past = $this->isEventPast($post['custom_fields'], $timezone, $now);

      if (isset($existing_post_id)) {
        if ($is_past) {
          $this->deletePost($existing_post_id);
        } else {
          $post_id = $this->updatePost($post, $existing_post_id);
          $existing_post_ids = $this->removePostIdFromArray($existing_post_ids, $post_id);
        }
      } else if (!$is_past) {
        $post_id = $this->createPost($post);
        $existing_post_ids = $this->removePostIdFromArray($existing_post_ids, $post_id);
      }
    }

    // Delete events that are in the database but no longer in the API response (canceled/removed)
    foreach ($existing_post_ids as $orphan_id) {
      $title = get_the_title($orphan_id);
      if ($this->logger) {
        $this->logger->log('post_deleted', sprintf('Removed canceled/missing event: %s', $title), array('post_id' => $orphan_id));
      }
      $this->deletePost($orphan_id);
    }

    if ($this->logger) {
      $this->logger->syncComplete(
        $this->counts['created'],
        $this->counts['updated'],
        $this->counts['deleted'],
        $this->counts['errors']
      );
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
      $this->counts['errors']++;
      if ($this->logger) {
        $this->logger->apiError('Failed to update post: ' . $post_id->get_error_message());
      }
      return $post_id->get_error_message();
    }

    $custom_fields = $post['custom_fields'];
    if (!empty($custom_fields)) {
      foreach ($custom_fields as $key => $value) {
        update_post_meta($post_id, $key, $value);
      }
    }

    // Re-download featured image if the source URL changed or if it's missing
    $image_url = $post['custom_fields']['nrdfi_event_img_url'];
    $current_img_url = get_post_meta($post_id, 'nrdfi_event_img_url', true);
    $has_thumbnail = has_post_thumbnail($post_id);

    if ($this->isDownloadableImage($image_url)) {
      if ($image_url !== $current_img_url) {
        $this->deleteAttachment($post_id);
        $this->downloadAndAttachImage($image_url, $post_id);
      } elseif (!$has_thumbnail) {
        $this->deleteAttachment($post_id);
        $this->downloadAndAttachImage($image_url, $post_id);
      }
    }

    $this->counts['updated']++;
    if ($this->logger) {
      $this->logger->postUpdated($post_id, $post['post_title']);
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
      $this->counts['errors']++;
      if ($this->logger) {
        $this->logger->apiError('Failed to create post: ' . $post_id->get_error_message());
      }
      return $post_id->get_error_message();
    }

    $custom_fields = $post['custom_fields'];
    if (!empty($custom_fields)) {
      foreach ($custom_fields as $key => $value) {
        update_post_meta($post_id, $key, $value);
      }
    }

    // Download and set featured image
    $image_url = $post['custom_fields']['nrdfi_event_img_url'];
    if ($this->isDownloadableImage($image_url)) {
      $this->downloadAndAttachImage($image_url, $post_id);
    }

    $this->counts['created']++;
    if ($this->logger) {
      $this->logger->postCreated($post_id, $post['post_title']);
    }

    return $post_id;
  }

  /**
   * Download an image from a URL and attach it to a post as the featured image.
   */
  private function downloadAndAttachImage($image_url, $post_id)
  {
    if (!function_exists('download_url')) {
      require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    if (!function_exists('media_handle_sideload')) {
      require_once ABSPATH . 'wp-admin/includes/media.php';
      require_once ABSPATH . 'wp-admin/includes/image.php';
    }

    $tmp_file = download_url($image_url);

    if (is_wp_error($tmp_file)) {
      if ($this->logger) {
        $this->logger->apiError('Failed to download image: ' . $tmp_file->get_error_message(), array('url' => $image_url, 'post_id' => $post_id));
      }
      return false;
    }

    // Extract a filename from the URL
    $url_path = wp_parse_url($image_url, PHP_URL_PATH);
    $filename = basename($url_path);

    // Fallback filename if parsing fails
    if (empty($filename) || strpos($filename, '.') === false) {
      $filename = 'facebook-event-' . $post_id . '.jpg';
    }

    $file_array = array(
      'name'     => sanitize_file_name($filename),
      'tmp_name' => $tmp_file,
    );

    $attachment_id = media_handle_sideload($file_array, $post_id);

    if (is_wp_error($attachment_id)) {
      @unlink($tmp_file);
      if ($this->logger) {
        $this->logger->apiError('Failed to sideload image: ' . $attachment_id->get_error_message(), array('url' => $image_url, 'post_id' => $post_id));
      }
      return false;
    }

    // Set as featured image and store attachment ID for cleanup
    set_post_thumbnail($post_id, $attachment_id);
    update_post_meta($post_id, 'nrdfi_event_attachment_id', $attachment_id);

    // Set alt text to the event name
    $event_title = get_the_title($post_id);
    if (!empty($event_title)) {
      update_post_meta($attachment_id, '_wp_attachment_image_alt', $event_title);
    }

    if ($this->logger) {
      $this->logger->log('media_downloaded', sprintf('Downloaded image for: %s', get_the_title($post_id)), array('attachment_id' => $attachment_id, 'post_id' => $post_id));
    }

    return $attachment_id;
  }

  /**
   * Check if an image URL should be downloaded (skip default/empty URLs).
   */
  private function isDownloadableImage($image_url)
  {
    if (empty($image_url)) {
      return false;
    }

    // Don't download the default fallback image (would create duplicates)
    if (!empty($this->default_image_url) && $image_url === $this->default_image_url) {
      return false;
    }

    return true;
  }

  /**
   * Delete the media attachment associated with a post.
   */
  private function deleteAttachment($post_id)
  {
    $attachment_id = get_post_meta($post_id, 'nrdfi_event_attachment_id', true);

    if (!empty($attachment_id)) {
      wp_delete_attachment($attachment_id, true);
      delete_post_meta($post_id, 'nrdfi_event_attachment_id');
    }
  }

  /**
   * Deduplicate events with the same start_time and end_time.
   * Keeps the event with the longer description or a cover image.
   */
  private function deduplicateEvents(array $events)
  {
    $grouped = array();

    foreach ($events as $event) {
      $key = $event['start_time'] . '|' . $event['end_time'];

      if (!isset($grouped[$key])) {
        $grouped[$key] = $event;
      } else {
        $existing = $grouped[$key];

        // Score each event: prefer longer description, then having a cover image
        $existing_score = strlen($existing['description']);
        $new_score = strlen($event['description']);

        if (!empty($existing['image_url'])) {
          $existing_score += 100;
        }
        if (!empty($event['image_url'])) {
          $new_score += 100;
        }

        if ($new_score > $existing_score) {
          // New event is better, replace
          if ($this->logger) {
            $this->logger->log('duplicate_skipped', sprintf(
              'Duplicate skipped: "%s" (same time as "%s")',
              $existing['name'],
              $event['name']
            ));
          }
          $grouped[$key] = $event;
        } else {
          // Existing is better, skip new
          if ($this->logger) {
            $this->logger->log('duplicate_skipped', sprintf(
              'Duplicate skipped: "%s" (same time as "%s")',
              $event['name'],
              $existing['name']
            ));
          }
        }
      }
    }

    return array_values($grouped);
  }

  /**
   * Check if an event is past based on end time, or start time + 1 hour if no end time.
   */
  private function isEventPast($fields, $timezone, $now)
  {
    $end_time = isset($fields['nrdfi_event_end_time']) ? $fields['nrdfi_event_end_time'] : '';
    $start_time = isset($fields['nrdfi_event_start_time']) ? $fields['nrdfi_event_start_time'] : '';

    if (!empty($end_time)) {
      $event_end = new \DateTime($end_time, $timezone);
      return $now > $event_end;
    }

    if (!empty($start_time)) {
      $event_start = new \DateTime($start_time, $timezone);
      $event_start->modify('+1 hour');
      return $now > $event_start;
    }

    return false;
  }

  private function addTermsToPost($post_id, $terms, $taxonomy)
  {
    if (!is_array($terms)) {
      $terms = array($terms);
    }
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

    if ($query->have_posts()) {
        $post_id = $query->posts[0];
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
    $post_ids = array_values($post_ids);
    return $post_ids;
  }

  public function deletePost($post_id)
  {
    $title = get_the_title($post_id);

    // Delete attached media first
    $this->deleteAttachment($post_id);

    $result = wp_delete_post($post_id, true);

    if ($result) {
      $this->counts['deleted']++;
      if ($this->logger) {
        $this->logger->postDeleted($post_id, $title);
      }
      return true;
    } else {
      $this->counts['errors']++;
      if ($this->logger) {
        $this->logger->apiError(sprintf('Failed to delete post ID: %d', $post_id));
      }
      return false;
    }
  }

  /**
   * Find and delete all events whose date has passed, including their media.
   */
  public function cleanupPastEvents($post_type)
  {
    $timezone = wp_timezone();
    $now = new \DateTime('now', $timezone);
    $cleaned = 0;

    // Query all event posts
    $args = array(
      'post_type' => $post_type,
      'posts_per_page' => -1,
      'fields' => 'ids',
    );

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
      return;
    }

    foreach ($query->posts as $post_id) {
      $fields = array(
        'nrdfi_event_start_time' => get_post_meta($post_id, 'nrdfi_event_start_time', true),
        'nrdfi_event_end_time'   => get_post_meta($post_id, 'nrdfi_event_end_time', true),
      );

      if (empty($fields['nrdfi_event_start_time'])) {
        continue;
      }

      if ($this->isEventPast($fields, $timezone, $now)) {
        $this->deletePost($post_id);
        $cleaned++;
      }
    }

    wp_reset_postdata();

    if ($cleaned > 0 && $this->logger) {
      $this->logger->log('cleanup', sprintf('Cleaned up %d past %s', $cleaned, $cleaned === 1 ? 'event' : 'events'));
    }
  }

}
