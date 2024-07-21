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
            'add_new' => 'Add New ' . $post_type['singular_name'],
            'add_new_item' => 'Add New ' . $post_type['singular_name'],
            'edit_item' => 'Edit ' . $post_type['singular_name'],
            'new_item' => 'New ' . $post_type['singular_name'],
            'all_items' => 'View ' . $post_type['name'],
            'view_item' => 'View ' . $post_type['singular_name'],
            'search_items' => 'Search ' . $post_type['name'],
            'not_found' => 'No ' . $post_type['name'] . ' Found',
            'not_found_in_trash' => 'No ' . $post_type['name'] . ' Found in Trash',
            'menu_name' => $post_type['name']
          ),
          'menu_icon' => 'dashicons-star-filled',
          'public' => true,
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