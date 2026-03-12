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
    $this->settings->addPages($this->pages)->withSubPage('FB Connection')->register();

    // Columns
    add_filter('manage_nrd-facebook-event_posts_columns', [$this, 'setColumns']);
    add_action('manage_nrd-facebook-event_posts_custom_column', [$this, 'renderColumnContent'], 10, 2);
    add_filter('manage_edit-nrd-facebook-event_sortable_columns', [$this, 'setSortableColumns']);

    // Sorting & filtering
    add_action('pre_get_posts', [$this, 'handleQueryMods']);
    add_action('restrict_manage_posts', [$this, 'renderFilters']);

    // Default sort by start time ascending
    add_filter('pre_get_posts', [$this, 'setDefaultSort']);
  }

  public function setColumns($columns)
  {
    return array(
      'cb'               => $columns['cb'],
      'title'            => __('Event', 'NRDFacebookImporter'),
      'event_start_time' => __('Start', 'NRDFacebookImporter'),
      'event_end_time'   => __('End', 'NRDFacebookImporter'),
      'event_status'     => __('Status', 'NRDFacebookImporter'),
      'event_fb_link'    => __('Facebook', 'NRDFacebookImporter'),
      'date'             => __('Published', 'NRDFacebookImporter'),
    );
  }

  public function renderColumnContent($column, $post_id)
  {
    switch ($column) {
      case 'event_start_time':
        $value = get_post_meta($post_id, 'nrdfi_event_start_time', true);
        echo $value ? esc_html(date_i18n('M j, Y g:i a', strtotime($value))) : '<span style="color:#999;">—</span>';
        break;

      case 'event_end_time':
        $value = get_post_meta($post_id, 'nrdfi_event_end_time', true);
        echo $value ? esc_html(date_i18n('M j, Y g:i a', strtotime($value))) : '<span style="color:#999;">—</span>';
        break;

      case 'event_status':
        $start = get_post_meta($post_id, 'nrdfi_event_start_time', true);
        $end = get_post_meta($post_id, 'nrdfi_event_end_time', true);
        $now = current_time('Y-m-d\TH:i');
        $check_time = !empty($end) ? $end : $start;

        if (empty($start)) {
          echo '<span style="color:#999;">Unknown</span>';
        } elseif ($start <= $now && (!$end || $end >= $now)) {
          echo '<span style="color:#00a32a;font-weight:600;">Happening Now</span>';
        } elseif ($check_time < $now) {
          echo '<span style="color:#999;">Past</span>';
        } else {
          echo '<span style="color:#2271b1;font-weight:600;">Upcoming</span>';
        }
        break;

      case 'event_fb_link':
        $url = get_post_meta($post_id, 'nrdfi_event_url', true);
        if ($url) {
          echo '<a href="' . esc_url($url) . '" target="_blank" rel="noopener">View</a>';
        } else {
          echo '<span style="color:#999;">—</span>';
        }
        break;
    }
  }

  public function setSortableColumns($columns)
  {
    $columns['event_start_time'] = 'event_start_time';
    $columns['event_end_time'] = 'event_end_time';
    $columns['event_status'] = 'event_start_time';
    return $columns;
  }

  public function setDefaultSort($query)
  {
    if (!is_admin() || !$query->is_main_query()) {
      return;
    }

    if ($query->get('post_type') !== 'nrd-facebook-event') {
      return;
    }

    // Default sort by start time if no orderby specified
    if (!$query->get('orderby')) {
      $query->set('meta_key', 'nrdfi_event_start_time');
      $query->set('orderby', 'meta_value');
      $query->set('order', 'ASC');
    }
  }

  public function handleQueryMods($query)
  {
    if (!is_admin() || !$query->is_main_query()) {
      return;
    }

    if ($query->get('post_type') !== 'nrd-facebook-event') {
      return;
    }

    // Handle column sorting
    $orderby = $query->get('orderby');
    if ($orderby === 'event_start_time') {
      $query->set('meta_key', 'nrdfi_event_start_time');
      $query->set('orderby', 'meta_value');
    } elseif ($orderby === 'event_end_time') {
      $query->set('meta_key', 'nrdfi_event_end_time');
      $query->set('orderby', 'meta_value');
    }

    // Handle status filter
    $status_filter = isset($_GET['nrdfi_status']) ? sanitize_text_field($_GET['nrdfi_status']) : '';
    $now = current_time('Y-m-d\TH:i');

    if ($status_filter === 'upcoming') {
      $query->set('meta_query', array(
        array(
          'key' => 'nrdfi_event_start_time',
          'value' => $now,
          'compare' => '>=',
          'type' => 'CHAR',
        ),
      ));
    } elseif ($status_filter === 'past') {
      $meta_query = array(
        'relation' => 'OR',
        array(
          'relation' => 'AND',
          array(
            'key' => 'nrdfi_event_end_time',
            'value' => '',
            'compare' => '!=',
          ),
          array(
            'key' => 'nrdfi_event_end_time',
            'value' => $now,
            'compare' => '<',
            'type' => 'CHAR',
          ),
        ),
        array(
          'relation' => 'AND',
          array(
            'key' => 'nrdfi_event_end_time',
            'value' => '',
            'compare' => '=',
          ),
          array(
            'key' => 'nrdfi_event_start_time',
            'value' => $now,
            'compare' => '<',
            'type' => 'CHAR',
          ),
        ),
      );
      $query->set('meta_query', $meta_query);
    }

    // Handle month filter
    $month_filter = isset($_GET['nrdfi_month']) ? sanitize_text_field($_GET['nrdfi_month']) : '';
    if (!empty($month_filter) && preg_match('/^\d{4}-\d{2}$/', $month_filter)) {
      $start_of_month = $month_filter . '-01T00:00';
      $end_of_month = date('Y-m-t', strtotime($start_of_month)) . 'T23:59';

      $existing_meta = $query->get('meta_query');
      $month_meta = array(
        array(
          'key' => 'nrdfi_event_start_time',
          'value' => array($start_of_month, $end_of_month),
          'compare' => 'BETWEEN',
          'type' => 'CHAR',
        ),
      );

      if (!empty($existing_meta)) {
        $query->set('meta_query', array_merge(array('relation' => 'AND'), array($existing_meta), $month_meta));
      } else {
        $query->set('meta_query', $month_meta);
      }
    }
  }

  public function renderFilters($post_type)
  {
    if ($post_type !== 'nrd-facebook-event') {
      return;
    }

    $current_status = isset($_GET['nrdfi_status']) ? sanitize_text_field($_GET['nrdfi_status']) : '';
    $current_month = isset($_GET['nrdfi_month']) ? sanitize_text_field($_GET['nrdfi_month']) : '';

    // Status filter
    echo '<select name="nrdfi_status">';
    echo '<option value="">All Statuses</option>';
    echo '<option value="upcoming"' . selected($current_status, 'upcoming', false) . '>Upcoming</option>';
    echo '<option value="past"' . selected($current_status, 'past', false) . '>Past</option>';
    echo '</select>';

    // Month filter — build from existing events
    global $wpdb;
    $months = $wpdb->get_col(
      "SELECT DISTINCT DATE_FORMAT(meta_value, '%Y-%m') as event_month
       FROM {$wpdb->postmeta}
       WHERE meta_key = 'nrdfi_event_start_time' AND meta_value != ''
       ORDER BY event_month DESC"
    );

    if (!empty($months)) {
      echo '<select name="nrdfi_month">';
      echo '<option value="">All Months</option>';
      foreach ($months as $month) {
        $label = date_i18n('F Y', strtotime($month . '-01'));
        echo '<option value="' . esc_attr($month) . '"' . selected($current_month, $month, false) . '>' . esc_html($label) . '</option>';
      }
      echo '</select>';
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
      ),
      array(
        'option_group' => 'nrd_facebook_importer_options_settings',
        'option_name' => 'nrd_facebook_importer_options',
        'callback' => array($this->callbacks_mgr, 'optionsSanitize')
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
      ],
      [
        'id' => 'nrd_facebook_importer_options_section',
        'title' => '',
        'callback' => array($this->callbacks_mgr, 'optionsSectionManager'),
        'page' => 'nrd_facebook_importer_options'
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

    // Plugin options checkboxes
    $args[] = [
      'id' => 'email_expiry_alert',
      'title' => 'Email Expiry Alert',
      'callback' => array($this->callbacks_mgr, 'checkboxField'),
      'page' => 'nrd_facebook_importer_options',
      'section' => 'nrd_facebook_importer_options_section',
      'args' => array(
        'option_name' => 'nrd_facebook_importer_options',
        'label_for' => 'email_expiry_alert',
        'description' => 'Send an email to the site admin when the Facebook token is expiring or has expired.'
      )
    ];

    $args[] = [
      'id' => 'public_events',
      'title' => 'Enable Event Pages',
      'callback' => array($this->callbacks_mgr, 'checkboxField'),
      'page' => 'nrd_facebook_importer_options',
      'section' => 'nrd_facebook_importer_options_section',
      'args' => array(
        'option_name' => 'nrd_facebook_importer_options',
        'label_for' => 'public_events',
        'description' => 'Allow events to be viewed on the frontend with their own archive (/events/) and single post pages.'
      )
    ];

    $this->settings->setFields($args);
  }
}