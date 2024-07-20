<?php

/**
 * @package NRDFacebookImporter
 */

namespace Inc\Api\Callbacks;

use Inc\Base\BaseController; 

class AdminCallbacks extends BaseController
{
  public function dashboardTemplate()
  {
    return require_once ("$this->plugin_path/templates/admin.php");
  }

  public function menusTemplate()
  {
    return require_once ("$this->plugin_path/templates/menus.php");
  }

  public function scheduleTemplate()
  {
    return require_once ("$this->plugin_path/templates/schedule.php");
  }

  public function aboutTemplate()
  {
    return require_once ("$this->plugin_path/templates/about.php");
  }

  public function nrdUntappdImporterAdminSection( )
  {
    echo "Untappd API Settings";
  }

}