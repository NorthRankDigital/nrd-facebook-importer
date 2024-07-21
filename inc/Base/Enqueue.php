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

  public function enqueue()
  {
    wp_enqueue_script('jquery');
    wp_enqueue_style('nrdPluginCSS', $this->plugin_url . 'assets/css/nrd-fi-style.css');
    wp_enqueue_script('nrdPluginJS', $this->plugin_url . 'assets/js/nrd-fi-script.js');
    wp_localize_script('nrdPluginJS', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
      )
    );
  }
}