<?php

/**
 * @package NRDFacebookImporter
 */

namespace NrdFacebookImporter\Inc\Pages;

use NrdFacebookImporter\Inc\Base\BaseController;

use NrdFacebookImporter\Inc\Api\SettingsApi;
use NrdFacebookImporter\Inc\Api\CustomFieldApi;
use NrdFacebookImporter\Inc\Api\CustomPostTypeApi;
use NrdFacebookImporter\Inc\Api\CustomTaxonomyApi;
use NrdFacebookImporter\Inc\Api\Callbacks\AdminCallbacks;
use NrdFacebookImporter\Inc\Api\Callbacks\ManagerCallbacks;

/**
 * Summary of Settings
 */
class Settings extends BaseController
{
  public $settings;
  public $callbacks;
  public $callbacks_mgr;
  public $pages = array();
  public $subpages = array();
  public $customPostTypes;
  public $customTaxonomies;
  public $customFields;
  public $custom_post_types;
  public $custom_fields;

  /**
   * Initializes the Admin Class
   */
  public function register()
  {
    $this->settings = new SettingsApi();

    $this->callbacks = new AdminCallbacks();
    $this->callbacks_mgr = new ManagerCallbacks();
    $this->customPostTypes = new CustomPostTypeApi();
    $this->customTaxonomies = new CustomTaxonomyApi();
    $this->customFields = new CustomFieldApi();

    $this->setPages();

    $this->setSettings();
    $this->setSections();
    $this->setFields();

    $this->storeCustomPostTypes();
    $this->storeCustomFields();

    $this->customPostTypes->register();
    $this->customTaxonomies->register();
    $this->customFields->register();
    $this->settings->addPages($this->pages)->withSubPage('Settings')->register();
  }

  public function storeCustomPostTypes()
  {
    $this->custom_post_types = 
      [
        [
          'post_type' => 'nrd-facebook-event',
          'name' => 'Facebook Events',
          'singular_name' => 'Facebook Event',
          'title' => 'Event',
          'plural_title' => 'Events'

        ]
      ];
    $this->customPostTypes->addCustomPostType($this->custom_post_types);
    $this->customTaxonomies->addCustomTaxonomy($this->custom_post_types);
  }

  public function storeCustomFields()
  {
    $this->custom_fields =
      [
        [
          'post_type' => 'nrd-facebook-event',
          'id' => 'nrdfi_event_img_url',
          'title' => 'Event Img Url',
          'callback' => array($this->callbacks_mgr, 'renderCustomFields'),
          'args' => array(
            'label_for' => 'nrdfi_event_img_url',
            'place_holder' => 'Event Image Url'
          )
        ],
        [
          'post_type' => 'nrd-facebook-event',
          'id' => 'nrdfi_event_start_time',
          'title' => 'Event Start Date & Time',
          'callback' => array($this->callbacks_mgr, 'renderCustomFields'),
          'args' => array(
            'label_for' => 'nrdfi_event_start_time',
            'place_holder' => 'Event Start Date & Time'
          )
        ],
        [
          'post_type' => 'nrd-facebook-event',
          'id' => 'nrdfi_event_end_time',
          'title' => 'Event End Date & Time',
          'callback' => array($this->callbacks_mgr, 'renderCustomFields'),
          'args' => array(
            'label_for' => 'nrdfi_event_end_time',
            'place_holder' => 'Event End Date & Time'
          )
        ],
        [
          'post_type' => 'nrd-facebook-event',
          'id' => 'nrdfi_event_url',
          'title' => 'Event URL',
          'callback' => array($this->callbacks_mgr, 'renderCustomFields'),
          'args' => array(
            'label_for' => 'nrdfi_event_url',
            'place_holder' => 'Event URL'
          )
        ],
        [
          'post_type' => 'nrd-facebook-event',
          'id' => 'nrdfi_event_id',
          'title' => 'Event Facebook ID',
          'callback' => array($this->callbacks_mgr, 'renderCustomFields'),
          'args' => array(
            'label_for' => 'nrdfi_event_id',
            'place_holder' => 'Event Facebook ID'
          )
        ],
      ];

    $this->customFields->setFields($this->custom_fields);

  }

  public function setPages()
  {
    $this->pages = [
      [
        'page_title' => 'Facebook Importer',
        'menu_title' => 'Facebook Import',
        'capability' => 'manage_options',
        'menu_slug' => 'nrd_facebook_importer',
        'callback' => array($this->callbacks, 'dashboardTemplate'),
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

    $this->settings->setSettings($args);
  }

  public function setSections()
  {
    $args = [
      [
        'id' => 'nrd_untapped_importer_settings_mgr',
        'title' => 'Settings Manager',
        'callback' => array($this->callbacks_mgr, 'adminSectionManager'),
        'page' => 'nrd_facebook_importer'
      ]
    ];

    $this->settings->setSections($args);
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