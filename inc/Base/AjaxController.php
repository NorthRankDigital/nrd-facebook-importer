<?php

/**
 * @package NRDFacebookImporter
 */

namespace NrdFacebookImporter\Inc\Base;
use NrdFacebookImporter\Inc\Api\External\FacebookAPI;

class AjaxController extends BaseController
{
  private $facebook_api;

  public function register()
  {
    $this->facebook_api = new FacebookAPI();
    add_action('wp_ajax_nrdfi_facebook_auth', array($this, 'facebookAuth'));
    add_action('admin_post_nrdfi_facebook_authorize_callback', array($this, 'facebook_oauth_callback_ajax'));
  }

  public function facebookAuth()
  {
    if (!current_user_can('manage_options')) {
      wp_send_json_error('Unauthorized', 403);
    }

    $login_url = $this->facebook_api->getLoginUrl();
    wp_send_json_success(array('login_url' => $login_url));
  }

  public function facebook_oauth_callback_ajax()
  {
    $this->facebook_api->handleCallback();
    wp_send_json_success('Successfully authenticated with Facebook.');
  }

}