<?php

/**
 * @package NRDFacebookImporter
 */

namespace NrdFacebookImporter\Inc\Api;

class CustomTaxonomyApi
{

  public $custom_taxonomies = array();

  public function register()
  {
    if (!empty($this->custom_taxonomies)) {
      add_action('init', array($this, 'registerTaxonomies'));
    }
  }

  public function addCustomTaxonomy(array $taxonomies)
  {
    $this->custom_taxonomies = $taxonomies;

    return $this;
  }

  public function registerTaxonomies()
  {
    foreach ($this->custom_taxonomies as $taxonomies) {
      
      $taxonomy_name = $taxonomies['singular_name'] . '_category';

      register_taxonomy(
        $taxonomy_name, 
        array($taxonomies['post_type']), 
        array(
          'hierarchical' => true,
          'labels' => array(
            'name' => $taxonomies['title'] . ' Categories',
            'singular_name' => $taxonomies['title'] . ' Category',
            'search_items' => 'Search ' . $taxonomies['title'] . ' Categories',
            'all_items' => 'All ' . $taxonomies['title'] . ' Categories',
            'parent_item' => 'Parent ' . $taxonomies['title'] . ' Category',
            'parent_item_colon' => 'Parent ' . $taxonomies['title'] . ' Category:',
            'edit_item' => 'Edit ' . $taxonomies['title'] . ' Category',
            'update_item' => 'Update ' . $taxonomies['title'] . ' Category',
            'add_new_item' => 'Add New ' . $taxonomies['title'] . ' Category',
            'new_item_name' => 'New ' . $taxonomies['title'] . ' Category Name',
            'menu_name' => $taxonomies['title'] . ' Categories',
          ),
          'show_ui' => true,
          'show_admin_column' => true,
          'query_var' => true,
          'rewrite' => array('slug' => $taxonomy_name),
        )
      );
    }
  }

  
}