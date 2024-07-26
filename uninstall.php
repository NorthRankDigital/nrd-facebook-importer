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
    wp_delete_post($post->ID, true);
  }

  $options = [
    'nrd_facebook_access_token',
    'nrd_facebook_importer',
    'nrd_facebook_importer_schedule',
    'nrd_facebook_pages',
  ];

  foreach ($options as $option) {
    delete_option($option);
  }
}