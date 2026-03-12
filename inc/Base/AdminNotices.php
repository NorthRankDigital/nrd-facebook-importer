<?php

/**
 * @package NRDFacebookImporter
 */

namespace NrdFacebookImporter\Inc\Base;

class AdminNotices
{
  private $token_manager;

  public function register()
  {
    $this->token_manager = new TokenManager();
    add_action('admin_notices', array($this, 'tokenExpiryNotice'));
  }

  public function tokenExpiryNotice()
  {
    if (!current_user_can('manage_options')) {
      return;
    }

    if (!$this->token_manager->isUserTokenSet()) {
      return;
    }

    $status = $this->token_manager->getTokenStatus();
    $settings_url = admin_url('admin.php?page=nrd_facebook_importer');

    if ($status === 'expired') {
      $has_page_token = $this->token_manager->hasValidPageToken();
      if ($has_page_token) {
        $message = sprintf(
          'Facebook Importer: Your user token has expired, but scheduled imports will continue using the permanent page token. <a href="%s">Re-authenticate</a> if you need to update your page list.',
          esc_url($settings_url)
        );
        $class = 'notice-warning';
      } else {
        $message = sprintf(
          'Facebook Importer: Your Facebook token has expired. Scheduled imports will fail. <a href="%s">Re-authenticate now</a>.',
          esc_url($settings_url)
        );
        $class = 'notice-error';
      }
    } elseif ($status === 'expiring') {
      $days = $this->token_manager->getDaysUntilExpiry();
      $message = sprintf(
        'Facebook Importer: Your Facebook user token expires in %d %s. <a href="%s">Re-authenticate</a> to renew it.',
        $days,
        $days === 1 ? 'day' : 'days',
        esc_url($settings_url)
      );
      $class = 'notice-warning';
    } else {
      return;
    }

    printf(
      '<div class="notice %s is-dismissible"><p>%s</p></div>',
      esc_attr($class),
      wp_kses($message, array('a' => array('href' => array())))
    );
  }
}
