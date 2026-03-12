<?php

/**
 * @package NRDFacebookImporter
 */

namespace NrdFacebookImporter\Inc\Api\Callbacks;

use NrdFacebookImporter\Inc\Base\BaseController; 

class AdminCallbacks extends BaseController
{
  public function dashboardTemplate()
  {
    return require_once ("$this->plugin_path/templates/admin.php");
  }

  public function scheduleTemplate()
  {
    return require_once ("$this->plugin_path/templates/schedule.php");
  }

  public function logTemplate()
  {
    return require_once ("$this->plugin_path/templates/log.php");
  }

  public function optionsTemplate()
  {
    return require_once ("$this->plugin_path/templates/options.php");
  }

}