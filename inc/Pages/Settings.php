<?php

/**
 * @package NRDFacebookImporter
 */

namespace NrdFacebookImporter\Inc\Pages;

use NrdFacebookImporter\Inc\Base\BaseController;

use NrdFacebookImporter\Inc\Api\SettingsApi;
use NrdFacebookImporter\Inc\Api\CustomFieldApi;
use NrdFacebookImporter\Inc\Api\CustomPostTypeApi;
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
    $this->customFields = new CustomFieldApi();

    $this->setPages();

    $this->setSettings();
    $this->setSections();
    $this->setFields();

    $this->storeCustomPostTypes();
    $this->storeCustomFields();

    $this->customPostTypes->register();
    $this->customFields->register();
    $this->settings->addPages($this->pages)->withSubPage('Settings')->register();

    add_filter('manage_nrd-facebook-event_posts_columns', [$this, 'reorder_columns']);
    add_action('manage_nrd-facebook-event_posts_custom_column', [$this, 'custom_column_content'], 10, 2);
    add_filter('manage_edit_nrd-facebook-event_sortable_columns', [$this, 'custom_column_sortable']);
    add_action('pre_get_posts', [$this, 'custom_orderby']);
  }

  public function reorder_columns($columns)
  {
    // Create a new array to store the reordered columns
    $new_columns = array();

    // Add the title column
    $new_columns['cb'] = $columns['cb'];
    $new_columns['title'] = $columns['title'];

    // Add the custom column as the second column
    $new_columns['event_start_time'] = __('Event Start Time', 'textdomain');

    // Add the rest of the columns
    foreach ($columns as $key => $value) {
      if ($key !== 'cb' && $key !== 'title' && $key !== 'event_start_time') {
        $new_columns[$key] = $value;
      }
    }

    return $new_columns;
  }

  public function custom_column_content($column, $post_id)
  {
    if ($column == 'event_start_time') {
      $custom_field_value = get_post_meta($post_id, 'nrdfi_event_start_time', true);
      if ($custom_field_value) {
        $date = new \DateTime($custom_field_value);
        echo $date->format('m/d/Y \a\t h:i a');
      } else {
        echo 'N/A';
      }
    }
  }

  public function custom_column_sortable($columns)
  {
    $columns['event_start_time'] = 'event_start_time';
    return $columns;
  }

  public function custom_orderby($query)
  {
    if (!is_admin() || !$query->is_main_query()) {
      return;
    }

    $orderby = $query->get('orderby');

    if ('event_start_time' === $orderby) {
      $query->set('meta_key', 'nrdfi_event_start_time');
      $query->set('orderby', 'meta_value');
    }
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
  }

  public function storeCustomFields()
  {
    $this->custom_fields =
      [
        [
          'id' => 'nrdfi_event_start_time',
          'title' => 'Event Start Date & Time',
          'type' => 'datetime',
          'post_type' => 'nrd-facebook-event'
        ],
        [
          'id' => 'nrdfi_event_end_time',
          'title' => 'Event End Date & Time',
          'type' => 'datetime',
          'post_type' => 'nrd-facebook-event'
        ],
        [
          'id' => 'nrdfi_event_img_url',
          'title' => 'Event Img Url',
          'type' => 'text',
          'post_type' => 'nrd-facebook-event'
        ],
        [
          'id' => 'nrdfi_event_url',
          'title' => 'Event URL',
          'type' => 'text',
          'post_type' => 'nrd-facebook-event'
        ],
        [
          'id' => 'nrdfi_event_id',
          'title' => 'Event Facebook ID',
          'type' => 'text',
          'post_type' => 'nrd-facebook-event'
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