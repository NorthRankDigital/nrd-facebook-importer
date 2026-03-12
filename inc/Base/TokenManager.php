<?php

/**
 * @package NRDFacebookImporter
 */

namespace NrdFacebookImporter\Inc\Base;

class TokenManager
{
  public function getUserTokenExpiry()
  {
    return (int) get_option('nrd_facebook_token_expires', 0);
  }

  public function getUserTokenCreated()
  {
    return (int) get_option('nrd_facebook_token_created', 0);
  }

  public function isUserTokenExpired()
  {
    $expires = $this->getUserTokenExpiry();
    if ($expires === 0) {
      return true;
    }
    return time() > $expires;
  }

  public function isUserTokenSet()
  {
    $token = get_option('nrd_facebook_access_token', '');
    return !empty($token);
  }

  public function getDaysUntilExpiry()
  {
    $expires = $this->getUserTokenExpiry();
    if ($expires === 0) {
      return 0;
    }
    $diff = $expires - time();
    if ($diff <= 0) {
      return 0;
    }
    return (int) ceil($diff / DAY_IN_SECONDS);
  }

  public function isExpiringWithinDays($days = 7)
  {
    $days_left = $this->getDaysUntilExpiry();
    return $this->isUserTokenSet() && !$this->isUserTokenExpired() && $days_left <= $days;
  }

  public function getTokenStatus()
  {
    if (!$this->isUserTokenSet()) {
      return 'none';
    }
    if ($this->isUserTokenExpired()) {
      return 'expired';
    }
    if ($this->isExpiringWithinDays(7)) {
      return 'expiring';
    }
    return 'active';
  }

  public function hasValidPageToken()
  {
    $options = get_option('nrd_facebook_importer_schedule', array());
    $page_id = isset($options['selected_page']) ? $options['selected_page'] : '';

    if (empty($page_id)) {
      return false;
    }

    $pages = get_option('nrd_facebook_pages', array());
    foreach ($pages as $page) {
      if ($page['id'] === $page_id && !empty($page['access_token'])) {
        return true;
      }
    }
    return false;
  }

  public function getSelectedPageName()
  {
    $options = get_option('nrd_facebook_importer_schedule', array());
    $page_id = isset($options['selected_page']) ? $options['selected_page'] : '';

    if (empty($page_id)) {
      return '';
    }

    $pages = get_option('nrd_facebook_pages', array());
    foreach ($pages as $page) {
      if ($page['id'] === $page_id) {
        return $page['name'];
      }
    }
    return '';
  }

  /**
   * Send an email to the site admin when the token is expiring or expired.
   * Only sends once per status change to avoid spamming.
   */
  public function maybeNotifyExpiry()
  {
    $plugin_options = get_option('nrd_facebook_importer_options', array());
    if (empty($plugin_options['email_expiry_alert']) || $plugin_options['email_expiry_alert'] !== '1') {
      return;
    }

    $status = $this->getTokenStatus();

    // Only notify for expiring or expired
    if (!in_array($status, array('expiring', 'expired'), true)) {
      delete_option('nrdfi_last_expiry_notice');
      return;
    }

    $last_notice = get_option('nrdfi_last_expiry_notice', '');

    // Already sent a notice for this status
    if ($last_notice === $status) {
      return;
    }

    $admin_email = get_option('admin_email');
    $site_name = get_bloginfo('name');
    $settings_url = admin_url('admin.php?page=nrd_facebook_importer');

    if ($status === 'expiring') {
      $days_left = $this->getDaysUntilExpiry();
      $subject = sprintf('[%s] Facebook Importer token expires in %d %s', $site_name, $days_left, $days_left === 1 ? 'day' : 'days');
      $message = sprintf(
        "The Facebook access token for the NRD Facebook Importer plugin on %s will expire in %d %s.\n\n" .
        "Please re-authenticate to keep your event imports running.\n\n" .
        "Re-authenticate here: %s\n",
        $site_name,
        $days_left,
        $days_left === 1 ? 'day' : 'days',
        $settings_url
      );
    } else {
      $subject = sprintf('[%s] Facebook Importer token has expired', $site_name);
      $message = sprintf(
        "The Facebook access token for the NRD Facebook Importer plugin on %s has expired.\n\n" .
        "Event imports will not run until you re-authenticate.\n\n" .
        "Re-authenticate here: %s\n",
        $site_name,
        $settings_url
      );
    }

    wp_mail($admin_email, $subject, $message);
    update_option('nrdfi_last_expiry_notice', $status, false);
  }
}
