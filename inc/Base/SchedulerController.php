<?php

/**
 * @package NRDFacebookImporter
 */

namespace NrdFacebookImporter\Inc\Base;

use NrdFacebookImporter\Inc\Base\BaseController;

use NrdFacebookImporter\Inc\Api\SettingsApi;
use NrdFacebookImporter\Inc\Api\CreatePostApi;
use NrdFacebookImporter\Inc\Api\External\FacebookAPI;
use NrdFacebookImporter\Inc\Api\Callbacks\AdminCallbacks;
use NrdFacebookImporter\Inc\Api\Callbacks\SchedulerCallbacks;

class SchedulerController extends BaseController
{
  private $settings;
  private $callbacks;
  private $schedulerCallbacks;
  private $facebook_api;
  private $createPost;
  
  public function register()
  {
    $this->settings = new SettingsApi();
    $this->callbacks = new AdminCallbacks();
    $this->schedulerCallbacks = new SchedulerCallbacks();
    $this->facebook_api = new FacebookAPI();
    $this->createPost = new CreatePostApi();

    $this->setSettings();
    $this->setSections();
    $this->setFields();

    $this->updateSchedule();

    $this->setSubpages();
    $this->settings->addSubPages($this->subpages)->register();

    if (isset($this->schedule)) {
      add_action('nrd_facebook_import_event', array($this, 'updateDataFromExternalApi'));
    }
  }

  public function setSubpages()
  {
    $this->subpages = [
      [
        'parent_slug' => 'nrd_facebook_importer',
        'page_title' => 'Schedule',
        'menu_title' => 'Schedule Import',
        'capability' => 'manage_options',
        'menu_slug' => 'nrd_facebook_importer_schedule_import',
        'callback' => array($this->callbacks, 'scheduleTemplate'),
      ],
    ];
  }

  public function setSettings()
  {
    $args = array(
      array(
        'option_group' => 'nrd_facebook_importer_schedule_settings',
        'option_name' => 'nrd_facebook_importer_schedule',
        'callback' => array($this->schedulerCallbacks, 'inputSanitize')
      )
    );

    $this->settings->setSettings($args);
  }

  public function setSections()
  {
    $args = [
      [
        'id' => 'nrd_facebook_importer_schedule_index',
        'title' => 'Schedule Manager',
        'callback' => array($this->schedulerCallbacks, 'scheduleSectionManager'),
        'page' => 'nrd_facebook_importer_schedule_import'
      ]
    ];

    $this->settings->setSections($args);
  }

  public function setFields()
  {
    $args = [
      [
        'id' => 'selected_page',
        'title' => 'Page to Import',
        'callback' => array($this->schedulerCallbacks, 'pageSelectField'),
        'page' => 'nrd_facebook_importer_schedule_import',
        'section' => 'nrd_facebook_importer_schedule_index',
        'args' => array(
          'select_options' => 'nrd_facebook_pages',
          'option_name' => 'nrd_facebook_importer_schedule',
          'label_for' => 'selected_page',
          'title' => 'Page to Import'
        )
      ],
      [
        'id' => 'default_event_image',
        'title' => 'Event Default Img URL',
        'callback' => array($this->schedulerCallbacks, 'textBoxField'),
        'page' => 'nrd_facebook_importer_schedule_import',
        'section' => 'nrd_facebook_importer_schedule_index',
        'args' => array(
          'option_name' => 'nrd_facebook_importer_schedule',
          'label_for' => 'default_event_image',
          'title' => 'Event Default Img URL'
        )
      ],
      [
        'id' => 'import_schedule',
        'title' => 'Schedule',
        'callback' => array($this->schedulerCallbacks, 'selectField'),
        'page' => 'nrd_facebook_importer_schedule_import',
        'section' => 'nrd_facebook_importer_schedule_index',
        'args' => array(
          'option_name' => 'nrd_facebook_importer_schedule',
          'label_for' => 'import_schedule',
          'title' => 'Schedule'
        )
      ]      
    ];

    $this->settings->setFields($args);
  }

  public function updateSchedule()
  {
    $options = get_option('nrd_facebook_importer_schedule', array());
    $interval = (isset($options['import_schedule'])) ? $options['import_schedule'] : 'never';
    if ($interval == '' || $interval == 'never') {
      $this->schedule = null;
      wp_clear_scheduled_hook('nrd_facebook_import_event');
    } else {
      $this->schedule = $interval;

      if (!wp_next_scheduled('nrd_facebook_import_event')) {
        wp_schedule_event(time(), $this->schedule, 'nrd_facebook_import_event');
      }
    }
  }

  public function updateDataFromExternalApi()
  {
    $events = $this->facebook_api->fetchPageEvents();
    $this->createPost->syncPosts($events, 'nrd-facebook-event');
  }

}
