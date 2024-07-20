<?php

/**
 * @package NRDFacebookImporter
 */

namespace Inc\Api\Callbacks;

use Inc\Base\BaseController; 

class ManagerCallbacks extends BaseController
{  
  public function textBoxSanitize( $input )
  {
    $output = array();

    foreach ( $this->api_settings as $key => $value ) {
      $output[$key] = strip_tags( $input[$key] );
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
    
    echo '<input type="text" class="regular-text ' . $classes . '" name="' . $option_name . '[' . $name . ']' . '" value="' . (isset($input[$name]) ? $input[$name] : '') . '" placeholder="' . $title . '"/>';
  }

}