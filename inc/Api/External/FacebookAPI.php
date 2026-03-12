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
    $permissions = ['pages_show_list', 'pages_manage_metadata', 'pages_read_engagement', 'pages_read_user_content', 'page_events', 'business_management'];
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
    if (!isset($_GET['code']) || !isset($_GET['state']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['state'])), 'facebook_oauth_state')) {
      wp_die(esc_html__('Invalid state parameter', 'NRDFacebookImporter'), 403);
    }

    $code = sanitize_text_field(wp_unslash($_GET['code']));
    $token_url = "https://graph.facebook.com/{$this->api_version}/oauth/access_token?client_id={$this->app_id}&redirect_uri={$this->redirect_uri}&client_secret={$this->app_secret}&code={$code}";

    $response = wp_remote_get($token_url);

    if (is_wp_error($response)) {
      wp_die(esc_html__('Error retrieving access token', 'NRDFacebookImporter'));
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (isset($data['access_token'])) {
      $short_lived_token = $data['access_token'];
      $long_lived_token = $this->getLongLivedToken($short_lived_token);

      if ($long_lived_token) {
        update_option('nrd_facebook_access_token', $long_lived_token);
        update_option('nrd_facebook_token_created', time());
        update_option('nrd_facebook_token_expires', time() + (60 * DAY_IN_SECONDS));

        wp_safe_redirect(admin_url('admin.php?page=nrd_facebook_importer'));
        exit;
      } else {
        wp_die(esc_html__('Error exchanging access token', 'NRDFacebookImporter'));
      }
    } else {
      wp_die(esc_html__('Error retrieving access token', 'NRDFacebookImporter'));
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
    $endpoint_url = "https://graph.facebook.com/{$this->api_version}/me/accounts?access_token={$user_access_token}";

    if (!empty($this->app_secret)) {
      $appsecret_proof = hash_hmac('sha256', $user_access_token, $this->app_secret);
      $endpoint_url .= "&appsecret_proof={$appsecret_proof}";
    }

    $response = wp_remote_get($endpoint_url);

    if (is_wp_error($response)) {
      if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('NRDFI: Failed to fetch Facebook pages - ' . $response->get_error_message());
      }
      return;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!isset($data['data']) || !is_array($data['data'])) {
      $error_msg = $this->extractApiError($data, $response);
      if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('NRDFI: Failed to fetch Facebook pages - ' . $error_msg);
      }
      return;
    }

    if (empty($data['data'])) {
      if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('NRDFI: /me/accounts returned empty pages list — keeping previously saved pages');
      }
      return;
    }

    foreach ($data['data'] as $page) {
      $pages[] = [
        'access_token' => $page['access_token'],
        'id' => $page['id'],
        'name' => $page['name']
      ];
    }

    if (defined('WP_DEBUG') && WP_DEBUG) {
      error_log('NRDFI: Fetched ' . count($pages) . ' page(s)');
    }
    update_option('nrd_facebook_pages', $pages);
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
    $options = get_option('nrd_facebook_importer_schedule', array());
    $default_img = isset($options['default_event_image']) ? $options['default_event_image'] : '';
    $page_id = isset($options['selected_page']) ? $options['selected_page'] : '';

    if (empty($page_id)) {
      return array('error' => 'no page selected');
    }

    $date_range = isset($options['date_range_months']) ? (int) $options['date_range_months'] : 0;
    $data = $this->requestPageEvents($page_id, $date_range);

    if (is_array($data) && isset($data['wp_error'])) {
      return array('error' => $data['wp_error']);
    }

    if (is_array($data) && isset($data['api_error'])) {
      return array('error' => $data['api_error']);
    }

    // If page token returned empty results, retry with user token
    if (empty($data['data'])) {
      $user_token = $this->getUserToken();
      $page_token = $this->getPageToken($page_id);

      if ($page_token && $user_token && $page_token !== $user_token) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
          error_log('NRDFI: Page token returned empty events, retrying with user token');
        }
        $data = $this->requestPageEvents($page_id, $date_range, $user_token);

        if (is_array($data) && isset($data['wp_error'])) {
          return array('error' => $data['wp_error']);
        }
        if (is_array($data) && isset($data['api_error'])) {
          return array('error' => $data['api_error']);
        }
      }
    }

    if (empty($data['data'])) {
      if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('NRDFI: Facebook returned empty events array for page ' . $page_id);
      }
    }

    foreach ($data['data'] as $item) {
      $description = isset($item['description']) ? $item['description'] : '';

      if (isset($item['event_times'])) {
        foreach ($item['event_times'] as $recurring_event) {
          $start = DateTime::createFromFormat('Y-m-d\TH:i:sO', $recurring_event['start_time']);
          $end = isset($recurring_event['end_time']) ? DateTime::createFromFormat('Y-m-d\TH:i:sO', $recurring_event['end_time']) : null;

          $events[] = [
            'id' => $recurring_event['id'],
            'start_time' => $start ? $start->format('Y-m-d\TH:i') : '',
            'end_time' => $end ? $end->format('Y-m-d\TH:i') : '',
            'name' => $item['name'],
            'description' => $description,
            'image_url' => isset($item['cover']['source']) ? $item['cover']['source'] : $default_img,
            'event_url' => 'https://facebook.com/events/' . $recurring_event['id']
          ];
        }
      } else {
        $start = DateTime::createFromFormat('Y-m-d\TH:i:sO', $item['start_time']);
        $end = isset($item['end_time']) ? DateTime::createFromFormat('Y-m-d\TH:i:sO', $item['end_time']) : null;

        $events[] = [
          'id' => $item['id'],
          'start_time' => $start ? $start->format('Y-m-d\TH:i') : '',
          'end_time' => $end ? $end->format('Y-m-d\TH:i') : '',
          'name' => $item['name'],
          'description' => $description,
          'image_url' => isset($item['cover']['source']) ? $item['cover']['source'] : $default_img,
          'event_url' => 'https://facebook.com/events/' . $item['id']
        ];
      }
    }

    return $events;
  }

  /**
   * Make a single request to the Facebook events endpoint.
   * Returns the decoded JSON response array, or an error array.
   */
  private function requestPageEvents($page_id, $date_range, $token_override = null)
  {
    $access_token = $token_override;
    if (!$access_token) {
      $access_token = $this->getPageToken($page_id);
      if (!$access_token) {
        $access_token = $this->getUserToken();
      }
    }

    $endpoint_url = "https://graph.facebook.com/{$this->api_version}/{$page_id}/events?fields=name,description,id,cover,start_time,end_time,event_times&access_token={$access_token}&limit=50";

    // Add appsecret_proof for server-to-server calls (required by some endpoints with system user tokens)
    if (!empty($this->app_secret)) {
      $appsecret_proof = hash_hmac('sha256', $access_token, $this->app_secret);
      $endpoint_url .= "&appsecret_proof={$appsecret_proof}";
    }

    if ($date_range > 0) {
      $since = time();
      $until = strtotime("+{$date_range} months");
      $endpoint_url .= "&since={$since}&until={$until}";
    }

    $response = wp_remote_get($endpoint_url);

    if (is_wp_error($response)) {
      if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('NRDFI: Failed to fetch Facebook events - ' . $response->get_error_message());
      }
      return array('wp_error' => $response->get_error_message());
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!isset($data['data']) || !is_array($data['data'])) {
      $error_msg = $this->extractApiError($data, $response);
      if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('NRDFI: Failed to fetch Facebook events - ' . $error_msg);
      }
      return array('api_error' => $error_msg);
    }

    return $data;
  }

  /**
   * Extract a human-readable error from a Facebook API response.
   */
  private function extractApiError($data, $response)
  {
    $status_code = wp_remote_retrieve_response_code($response);
    $parts = array();

    if ($status_code) {
      $parts[] = "HTTP {$status_code}";
    }

    // Facebook returns errors in { "error": { "message": "...", "type": "...", "code": 123 } }
    if (isset($data['error']['message'])) {
      $msg = $data['error']['message'];
      if (isset($data['error']['type'])) {
        $msg = $data['error']['type'] . ': ' . $msg;
      }
      if (isset($data['error']['code'])) {
        $msg .= ' (code ' . $data['error']['code'] . ')';
      }
      $parts[] = $msg;
    } elseif (is_null($data)) {
      $parts[] = 'Empty or invalid JSON response';
    } else {
      $parts[] = 'Unexpected response format';
    }

    return implode(' - ', $parts);
  }

  public function getPageToken($page_id)
  {
    $pages = get_option('nrd_facebook_pages', array());
    foreach ($pages as $page) {
      if ($page['id'] === $page_id) {
        return isset($page['access_token']) ? $page['access_token'] : false;
      }
    }
    return false;
  }

}