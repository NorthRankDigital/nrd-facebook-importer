<?php

/**
 * @package NRDFacebookImporter
 */

namespace NrdFacebookImporter\Inc\Base;

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
     $this->plugin_name = plugin_basename( dirname( __FILE__, 3 ) ) . '/nrd-facebook-importer.php';

     $this->api_settings = array(
      'nrdfi_notification_email' => 'Email',
      'nrdfi_facebook_app_id' => 'Facebook App ID',
      'nrdfi_facebook_app_secret' => 'Facebook App Secret',
     );
  }

}