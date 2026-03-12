<?php

/**
 * @package NRDFacebookImporter
 */

namespace NrdFacebookImporter\Inc\Api;

class CustomPostTypeApi
{

  public $custom_post_types = array();

  public function register()
  {
    if (!empty($this->custom_post_types)) {
      add_action('init', array($this, 'registerCustomPostTypes'));
    }
  }

  public function addCustomPostType(array $custom_post_types)
  {
    $this->custom_post_types = $custom_post_types;

    return $this;
  }

  public function registerCustomPostTypes()
  {
    $plugin_options = get_option('nrd_facebook_importer_options', array());
    $is_public = isset($plugin_options['public_events']) && $plugin_options['public_events'] === '1';

    foreach ($this->custom_post_types as $post_type) {
      register_post_type(
        $post_type['post_type'],
        array(
          'labels' => array(
            'name' => $post_type['name'],
            'singular_name' => $post_type['singular_name'],
            'add_new' => 'Add New',
            'add_new_item' => 'Add New ' . $post_type['title'],
            'edit_item' => 'Edit ' . $post_type['title'],
            'new_item' => 'New ' . $post_type['title'],
            'all_items' => $post_type['name'],
            'view_item' => 'View ' . $post_type['title'],
            'search_items' => 'Search ' . $post_type['plural_title'],
            'not_found' => 'No ' . $post_type['title'] . ' Found',
            'not_found_in_trash' => 'No ' . $post_type['title'] . ' Found in Trash',
            'menu_name' => $post_type['name']
          ),
          'menu_icon' => 'dashicons-calendar',
          'public' => $is_public,
          'show_ui' => true,
          'show_in_menu' => true,
          'rewrite' => $is_public ? array('slug' => 'events') : false,
          'has_archive' => $is_public,
          'publicly_queryable' => $is_public,
          'show_in_rest' => $is_public,
          'supports' => array(
            'title',
            'editor',
            'thumbnail',
            'custom-fields',
            'page-attributes',
            'post-formats',
          ),
          'taxonomies' => array($post_type['singular_name'] . '_category')
        )
      );
    }
  }
}