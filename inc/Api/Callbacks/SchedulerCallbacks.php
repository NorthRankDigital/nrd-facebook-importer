<?php

/**
 * @package NRDFacebookImporter
 */

namespace NrdFacebookImporter\Inc\Api\Callbacks;

class SchedulerCallbacks
{
  public function scheduleSectionManager()
  {
    echo '<p>Manage the scheduled import of Facebook Events</p>';
  }

  public function inputSanitize( $input )
  {

    wp_clear_scheduled_hook('nrd_facebook_import_event');
    error_log('----- Schedule Cleared -----');

    $output = [];
    
    $output['import_schedule'] = sanitize_text_field($input['import_schedule']);
    $output['selected_page'] = sanitize_text_field($input['selected_page']);
    $output['default_event_image'] = sanitize_text_field($input['default_event_image']);

    return $output;
    
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
    $option_name = $args['option_name'];
    $options = get_option($option_name, array());
    $selected = isset($options['import_schedule']) ? $options['import_schedule'] : '';

    echo '<select name="' . $option_name . '[' . $name . ']' .'" id="'.$option_name . '[' . $name . ']'.'">';
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

  public function pageSelectField($args)
  {
    $name = $args['label_for'];
    $option_name = $args['option_name'];
    $options = get_option($option_name, array());
    $select_options = get_option($args['select_options'], array());
    $selected_option = isset($options['selected_page']) ? $options['selected_page'] : '';

    if(empty($select_options))
    {
      echo '<p style="color: red; font-weight:bold;">Need to Authenticate</p>';
    }
    else
    {
      echo '<select name="' . $option_name . '[' . $name . ']' .'" id="' . $option_name . '[' . $name . ']' . '">';
      foreach ($select_options as $page) {
        if($selected_option == $page['id'])
        {
          echo '<option value="' . $page['id'] . '" selected>' . $page['name'] . '</option>';
        }
        else
        {
          echo '<option value="' . $page['id'] . '">' . $page['name'] . '</option>';
        }
      }
      echo '</select>';
    }    
  }

  public function textBoxField($args)
  {
    $name = $args['label_for'];
    $title = $args['title'];
    $option_name = $args['option_name'];
    $input = get_option($option_name);

    echo '<input type="text" class="regular-text " name="' . $option_name . '[' . $name . ']' . '" value="' . (isset($input[$name]) ? $input[$name] : '') . '" placeholder="' . $title . '"/>';
  }

}