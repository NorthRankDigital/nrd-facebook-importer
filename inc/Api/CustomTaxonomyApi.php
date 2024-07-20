<?php

/**
 * @package NRDFacebookImporter
 */

namespace Inc\Api;

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
            'name' => $taxonomies['name'] . ' Categories',
            'singular_name' => $taxonomies['singular_name'] . ' Category',
            'search_items' => 'Search ' . $taxonomies['name'] . ' Categories',
            'all_items' => 'All ' . $taxonomies['name'] . ' Categories',
            'parent_item' => 'Parent ' . $taxonomies['singular_name'] . ' Category',
            'parent_item_colon' => 'Parent ' . $taxonomies['singular_name'] . ' Category:',
            'edit_item' => 'Edit ' . $taxonomies['singular_name'] . ' Category',
            'update_item' => 'Update ' . $taxonomies['singular_name'] . ' Category',
            'add_new_item' => 'Add New ' . $taxonomies['singular_name'] . ' Category',
            'new_item_name' => 'New ' . $taxonomies['singular_name'] . ' Category Name',
            'menu_name' => $taxonomies['singular_name'] . ' Category',
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