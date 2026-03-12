<?php

/**
 * @package NRDFacebookImporter
 */

namespace NrdFacebookImporter\Inc\Api\Callbacks;

use NrdFacebookImporter\Inc\Base\BaseController; 

class ManagerCallbacks extends BaseController
{  
  public function textBoxSanitize( $input )
  {
    $output = array();

    foreach ( $this->api_settings as $key => $value ) {
      $output[$key] = isset($input[$key]) ? sanitize_text_field($input[$key]) : '';
    }

    return $output;
  }

  public function adminSectionManager()
  {
    echo 'Manage the API connection to Facebook.';
  }

  public function textBoxField($args)
  {
    $name = $args['label_for'];
    $classes = $args['classes'];
    $title = $args['title'];
    $option_name = $args['option_name'];
    $input = get_option($option_name);
    
    echo '<input type="text" class="regular-text ' . esc_attr($classes) . '" name="' . esc_attr($option_name) . '[' . esc_attr($name) . ']' . '" value="' . esc_attr(isset($input[$name]) ? $input[$name] : '') . '" placeholder="' . esc_attr($title) . '"/>';
  }

  public function renderCustomFields($post, $args)
  {
    $name = $args['args']['label_for'];
    $placeholder = $args['args']['place_holder'];
    $value = get_post_meta($post->ID, $name, true);
    echo '<input type="text" class="regular-text" id="' . $name . '" name="' . $name . '" value="' . esc_attr($value) . '" placeholder="' . $placeholder . '" />';
  }

  public function optionsSectionManager()
  {
    // No description needed
  }

  public function optionsSanitize($input)
  {
    $output = array();
    $output['email_expiry_alert'] = !empty($input['email_expiry_alert']) ? '1' : '0';
    $output['public_events'] = !empty($input['public_events']) ? '1' : '0';

    // Flush rewrite rules when public_events changes so archive/single URLs work
    $current = get_option('nrd_facebook_importer_options', array());
    $was_public = isset($current['public_events']) ? $current['public_events'] : '0';
    if ($output['public_events'] !== $was_public) {
      add_action('shutdown', 'flush_rewrite_rules');
    }

    return $output;
  }

  public function checkboxField($args)
  {
    $name = $args['label_for'];
    $option_name = $args['option_name'];
    $description = isset($args['description']) ? $args['description'] : '';
    $options = get_option($option_name, array());
    $checked = isset($options[$name]) && $options[$name] === '1';

    echo '<label>';
    echo '<input type="checkbox" name="' . esc_attr($option_name) . '[' . esc_attr($name) . ']" value="1"' . checked($checked, true, false) . ' />';
    if (!empty($description)) {
      echo ' ' . esc_html($description);
    }
    echo '</label>';
  }

}