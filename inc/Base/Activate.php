<?php

/**
 * @package NRDFacebookImporter
 */

namespace NrdFacebookImporter\Inc\Base;

class Activate
{
  public static function activate()
  {
    flush_rewrite_rules();

    $default = array();

    if (!get_option('nrd_facebook_importer_schedule')) {
      update_option('nrd_facebook_importer_schedule', array('import_schedule'=>'never'));
    }

    if (!get_option('nrd_facebook_importer')) {
      update_option('nrd_facebook_importer', $default);
    }

    if (!get_option('nrd_facebook_access_token')) {
      update_option('nrd_facebook_access_token', '');
    }

    if (!get_option('nrd_facebook_pages')) {
      update_option('nrd_facebook_pages', $default);
    }
  }
}