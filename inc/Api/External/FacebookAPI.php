<?php

/**
 * @package NRDFacebookImporter
 */

namespace NrdFacebookImporter\Inc\Api\External;

class FacebookAPI
{
  private $app_id;
  private $app_secret;
  private $api_version;
  private $redirect_uri;

  public function __construct()
  {
    $this->api_version = 'v20.0';
    $this->redirect_uri = admin_url('admin-post.php?action=nrdfi_facebook_authorize_callback');

    $app_options = get_option('nrd_facebook_importer', array());
    if(isset($app_options['nrdfi_facebook_app_id']) && isset($app_options['nrdfi_facebook_app_secret']))
    {
      $this->app_id = $app_options['nrdfi_facebook_app_id'];
      $this->app_secret = $app_options['nrdfi_facebook_app_secret'];
    }
  }

  /**
   * Summary of getLoginUrl
   * @return string
   */
  public function getLoginUrl()
  {
    $permissions = ['pages_show_list', 'pages_manage_metadata', 'pages_read_engagement', 'pages_read_user_content','page_events'];
    $state = wp_create_nonce('facebook_oauth_state');
    $login_url = "https://www.facebook.com/{$this->api_version}/dialog/oauth?client_id={$this->app_id}&redirect_uri={$this->redirect_uri}&scope=" . implode(',', $permissions) . "&state={$state}";
    return $login_url;
  }

  /**
   * Summary of getUserToken
   * @return mixed
   */
  public function getUserToken()
  {    
    return get_option('nrd_facebook_access_token', '');
  }

  /**
   * Summary of handleCallback
   * @return never
   */
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
      $long_lived_token = $this->getLongLivedToken($short_lived_token);

      if ($long_lived_token) {
        // Store the token and its expiry time
        update_option('nrd_facebook_access_token', $long_lived_token);
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

  /**
   * Summary of getLongLivedToken
   * @param mixed $short_lived_token
   * @return mixed
   */
  public function getLongLivedToken($short_lived_token)
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

  /**
   * Summary of fetchUserPages
   * @return void
   */
  public function fetchUserPages()
  {
    $pages = [];
    $user_access_token = $this->getUserToken();
    $endpoint_url = "https://graph.facebook.com/me/accounts?access_token={$user_access_token}";
    
    $response = wp_remote_get( $endpoint_url );
    $body = wp_remote_retrieve_body( $response );
    $data = json_decode($body, true);
    foreach($data['data'] as $page)
    {
      $page_data = [
        'access_token' => $page['access_token'],
        'id' => $page['id'],
        'name' => $page['name']
      ];
      $pages[] = $page_data;
    }
    update_option( 'nrd_facebook_pages', $pages);
  }

  /**
   * Summary of fetchPageEvents
   * @param mixed $page_id
   * @param mixed $accessToken
   * @return void
   */
  public function fetchPageEvents($page_id, $accessToken)
  {
    
  }

}