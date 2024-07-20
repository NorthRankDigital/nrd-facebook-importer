<?php

/**
 * @package NRDFacebookImporter
 */

namespace Inc\Pages;

use Inc\Api\SettingsApi;
use Inc\Base\BaseController;
use Inc\Api\Callbacks\AdminCallbacks;
use Inc\Api\Callbacks\ManagerCallbacks;

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
        'page_title' => 'Untappd Importer',
        'menu_title' => 'Untappd Import',
        'capability' => 'manage_options',
        'menu_slug' => 'nrd_untappd_importer',
        'callback' => array( $this->callbacks, 'dashboardTemplate'),
        'icon_url' => $this->plugin_url . 'untappd_icon.svg',
        'position' => 100
      ]
    ];
  }

  public function setSettings()
  {
    $args = array(
      array(
        'option_group' => 'nrd_untappd_importer_settings',
        'option_name' => 'nrd_untappd_importer',
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
        'page' => 'nrd_untappd_importer'
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
        'page' => 'nrd_untappd_importer',
        'section' => 'nrd_untapped_importer_settings_mgr',
        'args' => array(
          'option_name' => 'nrd_untappd_importer',
          'label_for' => $key,
          'classes' => 'example-class',
          'title' => $value
        )
      ];
    }

    $this->settings->setFields($args);
  }
}