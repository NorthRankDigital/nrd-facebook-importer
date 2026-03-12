<?php

/**
 * @package NRDFacebookImporter
 */

namespace NrdFacebookImporter\Inc\Base;

use NrdFacebookImporter\Inc\Base\BaseController;

class Enqueue extends BaseController
{
  public function register()
  {
    add_action('admin_enqueue_scripts', array($this, 'enqueue'));
  }

  public function enqueue($hook_suffix)
  {
    $plugin_pages = array(
      'toplevel_page_nrd_facebook_importer',
      'facebook-import_page_nrd_facebook_importer_schedule_import',
      'facebook-import_page_nrd_facebook_importer_log',
      'facebook-import_page_nrd_facebook_importer_options',
    );

    // Load CSS on plugin pages and CPT edit screens
    $screen = get_current_screen();
    $is_cpt_screen = $screen && $screen->post_type === 'nrd-facebook-event';

    if (!in_array($hook_suffix, $plugin_pages) && !$is_cpt_screen) {
      return;
    }

    wp_enqueue_style('nrdfiPluginCSS', $this->plugin_url . 'assets/css/nrd-fi-style.css');

    if (in_array($hook_suffix, $plugin_pages)) {
      wp_enqueue_script('nrdfiPluginJS', $this->plugin_url . 'assets/js/nrd-fi-script.js', array('jquery'), null, true);
      wp_localize_script('nrdfiPluginJS', 'nrdfi_ajax', array(
          'ajax_url' => admin_url('admin-ajax.php'),
          'nonce'    => wp_create_nonce('nrdfi_nonce'),
        )
      );
    }
  }
}