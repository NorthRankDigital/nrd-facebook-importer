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
          'public' => true,
          'rewrite' => false,
          'has_archive' => false,
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