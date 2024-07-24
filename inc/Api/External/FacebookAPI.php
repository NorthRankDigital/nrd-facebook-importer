<?php

/**
 * @package NRDFacebookImporter
 */

namespace NrdFacebookImporter\Inc\Api\External;

use DateTime;

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
   * @return array
   */
  public function fetchPageEvents()
  {
    $events = [];
    $user_access_token = $this->getUserToken();
    $options = get_option('nrd_facebook_importer_schedule', array());
    $page_id = isset($options['selected_page']) ? $options['selected_page'] : '';

    if($page_id == '')
    {
      return array('error' => 'no page selected');
    }
    $endpoint_url = "https://graph.facebook.com/{$page_id}/events?access_token={$user_access_token}?fields=name,description,id,cover,start_time,end_time,event_times";
    $response = wp_remote_get( $endpoint_url );
    $body = wp_remote_retrieve_body( $response );
    $data = json_decode($body, true);

    $events = [];
    foreach($data['data'] as $item)
    {
      if(isset($item['event_times']))
      {
        foreach($item['event_times'] as $recuring_event)
        {
          $events[] = [
            'id' => $recuring_event['id'],
            'start_time' => $dateTime = DateTime::createFromFormat('Y-m-d\TH:i:sO', $recuring_event['start_time'])->format('Y-m-d\TH:i'),
            'end_time' => isset($recuring_event['end_time']) ? $dateTime = DateTime::createFromFormat('Y-m-d\TH:i:sO', $recuring_event['end_time'])->format('Y-m-d\TH:i') : '',
            'name' => $item['name'],
            'description' => $item['description'],
            'image_url' => isset($item['cover']['source']) ? $item['cover']['source'] : 'https://fictional-university.local/wp-content/uploads/2024/06/DrBarksalot.jpg',
            'event_url' => 'https://facebook.com/events/' . $recuring_event['id']
          ];
        }
      }
      else
      {
        $events[] = [
          'id' => $item['id'],
          'start_time' => $dateTime = DateTime::createFromFormat('Y-m-d\TH:i:sO',$item['start_time'])->format('Y-m-d\TH:i'),
          'end_time' => isset($item['end_time']) ? $dateTime = DateTime::createFromFormat('Y-m-d\TH:i:sO', $item['end_time'])->format('Y-m-d\TH:i') : '',
          'name' => $item['name'],
          'description' => $item['description'],
          'image_url' => isset($item['cover']['source']) ? $item['cover']['source'] : 'https://fictional-university.local/wp-content/uploads/2024/06/DrBarksalot.jpg',
          'event_url' => 'https://facebook.com/events/' . $item['id']
        ];
      }
      
    }

    return $events;
  }

}