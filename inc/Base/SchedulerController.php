<?php

/**
 * @package NRDFacebookImporter
 */

namespace NrdFacebookImporter\Inc\Base;

use NrdFacebookImporter\Inc\Api\SettingsApi;
use NrdFacebookImporter\Inc\Base\BaseController;
use NrdFacebookImporter\Inc\Api\Callbacks\AdminCallbacks;
use NrdFacebookImporter\Inc\Api\Callbacks\SchedulerCallbacks;

class SchedulerController extends BaseController
{
  public function register()
  {
    $this->settings = new SettingsApi();
    $this->callbacks = new AdminCallbacks();
    $this->schedulerCallbacks = new SchedulerCallbacks();

    $this->setSettings();
    $this->setSections();
    $this->setFields();

    // $this->updateSchedule();

    $this->setSubpages();
    $this->settings->addSubPages($this->subpages)->register();

    // if (isset($this->schedule)) {
    //   add_action('update_data_event', array($this, 'updateDataFromExternalApi'));
    // }
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
        'id' => 'nrd_untapped_importer_schedule_index',
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
        'id' => 'schedule_import',
        'title' => 'Schedule',
        'callback' => array($this->schedulerCallbacks, 'selectField'),
        'page' => 'nrd_facebook_importer_schedule_import',
        'section' => 'nrd_untapped_importer_schedule_index',
        'args' => array(
          'option_name' => 'nrd_facebook_importer_schedule',
          'label_for' => 'schedule_import',
          'title' => 'Schedule'
        )
      ]
    ];

    $this->settings->setFields($args);
  }

}