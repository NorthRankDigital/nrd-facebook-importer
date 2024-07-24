<?php

/**
 * @package NRDFacebookImporter
 */

namespace NrdFacebookImporter\Inc\Api;

class CustomFieldApi
{
  private $fields = array();
  private $metaBoxId = 'nrd_facebook_event_details_meta_box';
  private $metaBoxTitle = 'Facebook Event Details';
  private $postType = 'nrd-facebook-event'; 

  public function register()
  {
    if (!empty($this->fields)) {
      add_action('add_meta_boxes', [$this, 'addCustomMetaBox']);
      add_action('save_post', [$this, 'saveCustomMetaBoxData']);
    }
  }

  public function setFields(array $fields)
  {
    $this->fields = $fields;
    return $this;
  }

  public function addCustomMetaBox()
  {
    add_meta_box(
      $this->metaBoxId,
      $this->metaBoxTitle,
      [$this, 'customMetaBoxCallback'],
      $this->postType,
      'advanced',
      'default'
    );
  }

  public function customMetaBoxCallback($post)
  {
    wp_nonce_field('save_custom_meta_box_data', 'custom_meta_box_nonce');

    echo '<div class="nrdfi-form">';
    foreach ($this->fields as $field) {
      $value = get_post_meta($post->ID, $field['id'], true);
      echo '<div><label for="' . esc_attr($field['id']) . '">' . esc_html($field['title']) . '</label>';

      switch ($field['type']) {
        case 'text':
          echo '<input type="text" id="' . esc_attr($field['id']) . '" name="' . esc_attr($field['id']) . '" value="' . esc_attr($value) . '" />';
          break;
        case 'datetime':
          
          echo '<input type="datetime-local" id="' . esc_attr($field['id']) . '" name="' . esc_attr($field['id']) . '" value="' . esc_attr($value) . '" />';
          break;
        default:
          echo '<input type="text" id="' . esc_attr($field['id']) . '" name="' . esc_attr($field['id']) . '" value="' . esc_attr($value) . '" />';
          break;
      }
      echo '</div>';
    }
    echo "</div>";
  }

  public function saveCustomMetaBoxData($post_id)
  {
    if (!isset($_POST['custom_meta_box_nonce']) || !wp_verify_nonce($_POST['custom_meta_box_nonce'], 'save_custom_meta_box_data')) {
      return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return;
    }

    if (!current_user_can('edit_post', $post_id)) {
      return;
    }

    foreach ($this->fields as $field) {
      if (array_key_exists($field['id'], $_POST)) {
        $sanitized_value = sanitize_text_field($_POST[$field['id']]);

        if ($field['type'] == 'checkbox' && empty($sanitized_value)) {
          $sanitized_value = 'off';
        }

        update_post_meta(
          $post_id,
          $field['id'],
          $sanitized_value
        );
      }
    }
  }
}