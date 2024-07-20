<?php

/**
 * @package NRDFacebookImporter
 */

namespace Inc\Api\Callbacks;

class SchedulerCallbacks
{
  public function scheduleSectionManager()
  {
    echo '<p>Manage the scheduled import of Facebook Events</p>';
  }

  public function inputSanitize( $input )
  {

    wp_clear_scheduled_hook('update_data_event');
    error_log('----- Schedule Cleared -----');

    return $input;
  }

  public function selectField($args)
  {
    $select_options = [
      "never" => "Never",
      "hourly" => "Hourly",
      "twicedaily" => "Twice Daily",
      "daily" => "Daily",
      "weekly" => "Weekly"
    ];

    $name = $args['label_for'];
    $title = $args['title'];
    $option_name = $args['option_name'];
    $selected = get_option($option_name, '');

    echo '<select name="'.$option_name .'" id="'.$option_name . '[' . $name . ']'.'">';
    foreach($select_options as $key => $value)
    {
      if($selected == $key)
      {
        echo '<option value="'. $key .'" selected>'.$value.'</option>';
      }
      else
      {
        echo '<option value="' . $key . '">' . $value . '</option>';
      }
    }
    echo '</select>';
  }

}