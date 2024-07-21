<?php

/**
 * @package NRDFacebookImporter
 */

namespace NrdFacebookImporter\Inc\Pages;

use NrdFacebookImporter\Inc\Api\SettingsApi;
use NrdFacebookImporter\Inc\Base\BaseController;
use NrdFacebookImporter\Inc\Api\Callbacks\AdminCallbacks;
use NrdFacebookImporter\Inc\Api\Callbacks\ManagerCallbacks;

/**
 * 
 */
class Settings extends BaseController
{
  public $settings;
  public $callbacks;
  public $callbacks_mgr;
  public $pages = array();
  public $subpages = array();

  /**
   * Initializes the Admin Class
   */
  public function register()
  {
    $this->settings = new SettingsApi();

    $this->callbacks = new AdminCallbacks();
    $this->callbacks_mgr = new ManagerCallbacks();

    $this->setPages();
    // $this->setSubpages();    

    $this->setSettings();
    $this->setSections();
    $this->setFields();

    $this->settings->addPages( $this->pages )->withSubPage('API Settings')->register();
  }

  public function setPages()
  {
    $this->pages = [
      [
        'page_title' => 'Facebook Importer',
        'menu_title' => 'Facebook Import',
        'capability' => 'manage_options',
        'menu_slug' => 'nrd_facebook_importer',
        'callback' => array( $this->callbacks, 'dashboardTemplate'),
        'icon_url' => 'dashicons-calendar',
        'position' => 100
      ]
    ];
  }

  public function setSettings()
  {
    $args = array(
      array(
        'option_group' => 'nrd_facebook_importer_settings',
        'option_name' => 'nrd_facebook_importer',
        'callback' => array($this->callbacks_mgr, 'textBoxSanitize')
      )
    );

    $this->settings->setSettings( $args );
  }

  public function setSections()
  {
    $args = [
      [
        'id' => 'nrd_untapped_importer_settings_mgr',
        'title' => 'API Settings Manager',
        'callback' => array($this->callbacks_mgr, 'adminSectionManager'),
        'page' => 'nrd_facebook_importer'
      ]
    ];

    $this->settings->setSections( $args );
  }

  public function setFields()
  {
    $args = [];

    foreach ($this->api_settings as $key => $value) {
      $args[] = [
        'id' => $key,
        'title' => $value,
        'callback' => array($this->callbacks_mgr, 'textBoxField'),
        'page' => 'nrd_facebook_importer',
        'section' => 'nrd_untapped_importer_settings_mgr',
        'args' => array(
          'option_name' => 'nrd_facebook_importer',
          'label_for' => $key,
          'classes' => 'example-class',
          'title' => $value
        )
      ];
    }

    $this->settings->setFields($args);
  }
}