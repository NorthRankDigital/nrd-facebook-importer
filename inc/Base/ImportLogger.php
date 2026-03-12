<?php

/**
 * @package NRDFacebookImporter
 */

namespace NrdFacebookImporter\Inc\Base;

class ImportLogger
{
  private $table_name;

  public function __construct()
  {
    global $wpdb;
    $this->table_name = $wpdb->prefix . 'nrdfi_import_log';
  }

  public static function createTable()
  {
    global $wpdb;
    $table_name = $wpdb->prefix . 'nrdfi_import_log';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$table_name} (
      id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      log_time datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      event_type varchar(50) NOT NULL,
      message text NOT NULL,
      details longtext,
      PRIMARY KEY (id),
      KEY event_type (event_type),
      KEY log_time (log_time)
    ) {$charset_collate};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
  }

  public static function dropTable()
  {
    global $wpdb;
    $table_name = $wpdb->prefix . 'nrdfi_import_log';
    $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
  }

  public function log($event_type, $message, $details = null)
  {
    global $wpdb;

    $wpdb->insert(
      $this->table_name,
      array(
        'log_time'   => current_time('mysql'),
        'event_type' => sanitize_text_field($event_type),
        'message'    => sanitize_text_field($message),
        'details'    => $details ? wp_json_encode($details) : null,
      ),
      array('%s', '%s', '%s', '%s')
    );

    if ($event_type === 'sync_complete') {
      $this->trimLog();
    }
  }

  private function trimLog($keep_syncs = 5)
  {
    global $wpdb;

    // Find the log_time of the Nth most recent sync_start
    $cutoff = $wpdb->get_var($wpdb->prepare(
      "SELECT log_time FROM {$this->table_name} WHERE event_type = 'sync_start' ORDER BY log_time DESC LIMIT 1 OFFSET %d",
      $keep_syncs
    ));

    if ($cutoff) {
      $wpdb->query($wpdb->prepare(
        "DELETE FROM {$this->table_name} WHERE log_time < %s",
        $cutoff
      ));
    }
  }

  public function syncStart()
  {
    $this->log('sync_start', 'Import sync started');
  }

  public function syncComplete($created, $updated, $deleted, $errors)
  {
    $this->log('sync_complete', sprintf(
      'Import complete: %d created, %d updated, %d deleted, %d errors',
      $created, $updated, $deleted, $errors
    ), compact('created', 'updated', 'deleted', 'errors'));
  }

  public function postCreated($post_id, $title)
  {
    $this->log('post_created', sprintf('Created event: %s', $title), array('post_id' => $post_id));
  }

  public function postUpdated($post_id, $title)
  {
    $this->log('post_updated', sprintf('Updated event: %s', $title), array('post_id' => $post_id));
  }

  public function postDeleted($post_id, $title = '')
  {
    $this->log('post_deleted', sprintf('Deleted event (ID: %d) %s', $post_id, $title), array('post_id' => $post_id));
  }

  public function apiError($message, $details = null)
  {
    $this->log('error', $message, $details);
  }

  public function getEntries($per_page = 50, $page = 1)
  {
    global $wpdb;

    $offset = ($page - 1) * $per_page;

    return $wpdb->get_results(
      $wpdb->prepare(
        "SELECT * FROM {$this->table_name} ORDER BY log_time DESC LIMIT %d OFFSET %d",
        $per_page,
        $offset
      )
    );
  }

  public function getTotalEntries()
  {
    global $wpdb;
    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is from $wpdb->prefix
    return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
  }

  public function getLastSync()
  {
    global $wpdb;
    return $wpdb->get_row($wpdb->prepare(
      // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is from $wpdb->prefix
      "SELECT * FROM {$this->table_name} WHERE event_type = %s ORDER BY log_time DESC LIMIT 1",
      'sync_complete'
    ));
  }

  public function clearLog()
  {
    global $wpdb;
    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is from $wpdb->prefix
    $wpdb->query("TRUNCATE TABLE {$this->table_name}");
  }
}
