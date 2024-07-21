<?php

/**
 * @package NRDFacebookImporter
 */

namespace NrdFacebookImporter\Inc\Api;

class CustomFieldApi
{
  private $fields = array();

  public function register()
  {
    if (!empty($this->fields)) {
      add_action('add_meta_boxes', [$this, 'addCustomMetaBoxes']);
      add_action('save_post', [$this, 'saveCustomMetaBoxData']);
    }
  }

  public function setFields(array $fields)
  {
    $this->fields = $fields;

    return $this;
  }

  public function addCustomMetaBoxes()
  {
    foreach ($this->fields as $field) {
      add_meta_box(
        $field['id'],
        $field['title'],
        $field['callback'],
        $field['post_type'],
        'advanced',
        'default',
        $field['args']
      );
    }
  }

  public function saveCustomMetaBoxData($post_id)
  {
    foreach ($this->fields as $field) {
      if (array_key_exists($field['id'], $_POST)) {
        update_post_meta(
          $post_id,
          $field['id'],
          sanitize_text_field($_POST[$field['id']])
        );
      }
    }
  }
}