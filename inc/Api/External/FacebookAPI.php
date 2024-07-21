<?php

/**
 * @package NRDFacebookImporter
 */

namespace NrdFacebookImporter\Inc\Api\External;

class FacebookAPI
{
  private $api_base_url;
  private $app_id;
  private $app_secret;
  private $redirect_uri;
  private $api_version;

  public function __construct()
  {
    $this->api_version = 'v20.0';
    $this->api_base_url = 'https://business.untappd.com/api/v1/';
    $this->redirect_uri = admin_url('admin-post.php?action=nrdfi_facebook_authorize_callback');

    $options = get_option('nrd_facebook_importer', array());
    if(isset($options['nrdfi_facebook_app_id']) && isset($options['nrdfi_facebook_app_secret']))
    {
      $this->app_id = $options['nrdfi_facebook_app_id'];
      $this->app_secret = $options['nrdfi_facebook_app_secret'];
    }
  }

  public function getLoginUrl()
  {
    $permissions = ['pages_show_list', 'pages_manage_metadata', 'pages_read_engagement', 'pages_read_user_content','page_events'];
    $state = wp_create_nonce('facebook_oauth_state');
    $login_url = "https://www.facebook.com/{$this->api_version}/dialog/oauth?client_id={$this->app_id}&redirect_uri={$this->redirect_uri}&scope=" . implode(',', $permissions) . "&state={$state}";
    return $login_url;
  }

  public function handleCallback()
  {
    if (!isset($_GET['code']) || !isset($_GET['state']) || !wp_verify_nonce($_GET['state'], 'facebook_oauth_state')) {
      echo 'Invalid state parameter';
      exit;
    }

    $code = $_GET['code'];
    $token_url = "https://graph.facebook.com/v12.0/oauth/access_token?client_id={$this->app_id}&redirect_uri={$this->redirect_uri}&client_secret={$this->app_secret}&code={$code}";

    $response = wp_remote_get($token_url);

    if (is_wp_error($response)) {
      echo 'Error retrieving access token';
      exit;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (isset($data['access_token'])) {
      $short_lived_token = $data['access_token'];
      $long_lived_token = $this->get_long_lived_token($short_lived_token);

      if ($long_lived_token) {
        // Store the token and its expiry time
        update_option('nrd_facebook_access_token', [
          'token' => $long_lived_token,
          'expires_at' => time() + 60 * 24 * 60 * 60 // 60 days
        ]);
        wp_redirect(admin_url('admin.php?page=nrd_facebook_importer'));
        exit;
      } else {
        echo 'Error exchanging access token';
        exit;
      }
    } else {
      echo 'Error retrieving access token';
      exit;
    }
  }

  public function get_long_lived_token($short_lived_token)
  {
    $token_url = "https://graph.facebook.com/{$this->api_version}/oauth/access_token?grant_type=fb_exchange_token&client_id={$this->app_id}&client_secret={$this->app_secret}&fb_exchange_token={$short_lived_token}";

    $response = wp_remote_get($token_url);

    if (is_wp_error($response)) {
      return false;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    return isset($data['access_token']) ? $data['access_token'] : false;
  }

}