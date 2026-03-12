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

    $output = [];
    $output['import_schedule'] = isset($input['import_schedule']) ? sanitize_text_field($input['import_schedule']) : 'never';
    $output['selected_page'] = isset($input['selected_page']) ? sanitize_text_field($input['selected_page']) : '';
    $output['default_event_image'] = isset($input['default_event_image']) ? esc_url_raw($input['default_event_image']) : '';
    $output['date_range_months'] = isset($input['date_range_months']) ? sanitize_text_field($input['date_range_months']) : '0';

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

    echo '<select name="' . esc_attr($option_name) . '[' . esc_attr($name) . ']" id="' . esc_attr($option_name) . '[' . esc_attr($name) . ']">';
    foreach ($select_options as $key => $value) {
      echo '<option value="' . esc_attr($key) . '"' . selected($selected, $key, false) . '>' . esc_html($value) . '</option>';
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

    if (empty($select_options)) {
      echo '<p class="nrdfi-inline-error">No pages found. Please authenticate with Facebook first, then <a href="#" id="nrd-get-pages">refresh the page list</a>. <span id="nrd-get-pages-status"></span></p>';
    } else {
      echo '<select name="' . esc_attr($option_name) . '[' . esc_attr($name) . ']" id="' . esc_attr($option_name) . '[' . esc_attr($name) . ']">';
      echo '<option value="">' . esc_html__('— Select a page —', 'NRDFacebookImporter') . '</option>';
      foreach ($select_options as $page) {
        echo '<option value="' . esc_attr($page['id']) . '"' . selected($selected_option, $page['id'], false) . '>' . esc_html($page['name']) . '</option>';
      }
      echo '</select>';
    }
  }

  public function dateRangeSelectField($args)
  {
    $select_options = [
      "0"  => "All Events",
      "1"  => "1 Month",
      "2"  => "2 Months",
      "3"  => "3 Months",
      "6"  => "6 Months",
      "12" => "12 Months"
    ];

    $name = $args['label_for'];
    $option_name = $args['option_name'];
    $options = get_option($option_name, array());
    $selected = isset($options['date_range_months']) ? $options['date_range_months'] : '0';

    echo '<select name="' . esc_attr($option_name) . '[' . esc_attr($name) . ']" id="' . esc_attr($option_name) . '[' . esc_attr($name) . ']">';
    foreach ($select_options as $key => $value) {
      echo '<option value="' . esc_attr($key) . '"' . selected($selected, $key, false) . '>' . esc_html($value) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">Only import events within this time range from today.</p>';
  }

  public function pageIdField($args)
  {
    $name = $args['label_for'];
    $option_name = $args['option_name'];
    $options = get_option($option_name, array());
    $value = isset($options[$name]) ? $options[$name] : '';

    echo '<input type="text" class="regular-text" name="' . esc_attr($option_name) . '[' . esc_attr($name) . ']" value="' . esc_attr($value) . '" placeholder="e.g. 1016457921555649" />';
    echo '<p class="description">Find your Page ID at your Facebook Page &rarr; About &rarr; Page transparency.</p>';
  }

  public function textBoxField($args)
  {
    $name = $args['label_for'];
    $title = $args['title'];
    $option_name = $args['option_name'];
    $input = get_option($option_name);

    echo '<input type="text" class="regular-text" name="' . esc_attr($option_name) . '[' . esc_attr($name) . ']" value="' . esc_attr(isset($input[$name]) ? $input[$name] : '') . '" placeholder="' . esc_attr($title) . '"/>';
  }

}
