<?php

/**
 * @package NRDFacebookImporter
 */

namespace Inc\Base;

use Inc\Api\SettingsApi;
use Inc\Base\BaseController;
use Inc\Api\Callbacks\AdminCallbacks;
use Inc\Api\Callbacks\SchedulerCallbacks;

use Inc\Api\CreatePostApi;
use Inc\Api\External\UntappdApi;

class SchedulerController extends BaseController
{
  public $subpages = array();
  public $callbacks;
  public $schedulerCallbacks;
  public $settings;
  public $schedule;

  public $createPost;
  public $untappdApi;

  public function register()
  {
    $this->settings = new SettingsApi();
    $this->callbacks = new AdminCallbacks();
    $this->schedulerCallbacks = new SchedulerCallbacks();
    $this->createPost = new CreatePostApi();
    $this->untappdApi = new UntappdApi();

    $this->setSettings();
    $this->setSections();
    $this->setFields();

    $this->updateSchedule();

    $this->setSubpages();
    $this->settings->addSubPages($this->subpages)->register();

    if(isset($this->schedule))
    {
      add_action('update_data_event', array($this,'updateDataFromExternalApi'));
    }
  }

  public function updateDataFromExternalApi()
  {
    $menus = get_option('nrd_untappd_importer_menu') ?: array();

    if(count($menus) == 0)
    {
      error_log(' --- No Menus Found ---');
      return;
    }

    foreach($menus as $menu)
    {
      $menu_id = $menu['post_type'];
      $api_response = $this->untappdApi->getMenuItems($menu_id);

      if (isset($api_response['error'])) {
        error_log('API ERROR: ' . $api_response['error']);
        return;
      }

      $this->createPost->syncPosts($api_response, $menu_id);     
    }
  }

  public function updateSchedule()
  {
    $interval = get_option( 'nrd_untappd_importer_schedule', '');
    if($interval == '' || $interval == 'never')
    {
      $this->schedule = null;
      wp_clear_scheduled_hook('update_data_event');
    }
    else
    {
      $this->schedule = $interval;

      if (!wp_next_scheduled('update_data_event')) {
        wp_schedule_event(time(), $this->schedule, 'update_data_event');
      }
    }
  }

  public function setSubpages()
  {
    $this->subpages = [
      [
        'parent_slug' => 'nrd_untappd_importer',
        'page_title' => 'Schedule',
        'menu_title' => 'Schedule Import',
        'capability' => 'manage_options',
        'menu_slug' => 'nrd_untappd_importer_schedule_import',
        'callback' => array( $this->callbacks, 'scheduleTemplate'),
      ],
    ];
  }

  public function setSettings()
  {
    $args = array(
      array(
        'option_group' => 'nrd_untappd_importer_schedule_settings',
        'option_name' => 'nrd_untappd_importer_schedule',
        'callback' => array($this->schedulerCallbacks, 'inputSanitize')
      )
    );

    $this->settings->setSettings($args);
  }

  public function setSections()
  {
    $args = [
      [
        'id' => 'nrd_untapped_importer_schedule_index',
        'title' => 'Schedule Manager',
        'callback' => array($this->schedulerCallbacks, 'scheduleSectionManager'),
        'page' => 'nrd_untappd_importer_schedule_import'
      ]
    ];

    $this->settings->setSections($args);
  }

  public function setFields()
  {
    $args = [
      [
        'id' => 'schedule_import',
        'title' => 'Schedule',
        'callback' => array($this->schedulerCallbacks, 'selectField'),
        'page' => 'nrd_untappd_importer_schedule_import',
        'section' => 'nrd_untapped_importer_schedule_index',
        'args' => array(
          'option_name' => 'nrd_untappd_importer_schedule',
          'label_for' => 'schedule_import',
          'title' => 'Schedule'
        )
      ]
    ];

    $this->settings->setFields($args);
  }

}