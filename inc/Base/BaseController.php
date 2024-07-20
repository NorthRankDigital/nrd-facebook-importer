<?php

/**
 * @package NRDFacebookImporter
 */

namespace Inc\Base;

class BaseController
{
  public $plugin_path;
  public $plugin_url;
  public $plugin_name;
  public $api_settings = array();

  public function __construct()
  {
     $this->plugin_path = plugin_dir_path( dirname( __FILE__, 2 ) );
     $this->plugin_url = plugin_dir_url( dirname( __FILE__, 2 ) );
     $this->plugin_name = plugin_basename( dirname( __FILE__, 3 ) ) . '/nrd-untappd-importer.php';

     $this->api_settings = array(
      'nrdui_untappd_email' => 'Untappd Email',
      'nrdui_untappd_api' => 'Untappd Read Only API Key',
     );
  }

}