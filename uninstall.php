<?php

/**
 * Trigger this file on Plugin uninstall
 * 
 * @package NRDFacebookImporter
 */

if (defined('WP_UNINSTALL_PLUGIN')) {

  $custom_post_type = 'nrd-facebook-event';

  // Delete all posts
  $posts = get_posts([
    'post_type' => $custom_post_type,
    'numberposts' => -1,
    'post_status' => 'any'
  ]);

  foreach ($posts as $post) {
    // Delete attached media
    $attachment_id = get_post_meta($post->ID, 'nrdfi_event_attachment_id', true);
    if (!empty($attachment_id)) {
      wp_delete_attachment($attachment_id, true);
    }
    wp_delete_post($post->ID, true);
  }

  $options = [
    'nrd_facebook_access_token',
    'nrd_facebook_importer',
    'nrd_facebook_importer_schedule',
    'nrd_facebook_importer_options',
    'nrd_facebook_pages',
    'nrd_facebook_token_created',
    'nrd_facebook_token_expires',
    'nrdfi_last_expiry_notice',
  ];

  foreach ($options as $option) {
    delete_option($option);
  }

  // Drop import log table
  global $wpdb;
  $table_name = $wpdb->prefix . 'nrdfi_import_log';
  $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
}