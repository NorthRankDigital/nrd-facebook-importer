<?php
  $logger = new \NrdFacebookImporter\Inc\Base\ImportLogger();
  $last_sync = $logger->getLastSync();
  $next_scheduled = wp_next_scheduled('nrd_facebook_import_event');
?>
<div class="wrap">
  <h1>Facebook Importer</h1>

  <?php include 'partials/nav.php'; ?>

  <div class="nrdfi-wraper">

    <!-- Import Configuration Card -->
    <div class="nrdfi-card nrdfi-card-first">
      <h2>Import Settings</h2>

      <form action="options.php" method="POST">
        <?php
          settings_fields('nrd_facebook_importer_schedule_settings');
          do_settings_sections('nrd_facebook_importer_schedule_import');
          submit_button('Save Schedule');
        ?>
      </form>
    </div>

    <!-- Import Status Card -->
    <div class="nrdfi-card">
      <h2>Import Status</h2>

      <div class="nrdfi-schedule-info">
        <?php if ($next_scheduled) : ?>
          <p><strong>Next import:</strong> <?php echo esc_html(get_date_from_gmt(gmdate('Y-m-d H:i:s', $next_scheduled), 'M j, Y \a\t g:i a')); ?></p>
        <?php else : ?>
          <p><strong>Next import:</strong> <em>Not scheduled</em></p>
        <?php endif; ?>

        <?php if ($last_sync) : ?>
          <p><strong>Last import:</strong> <?php echo esc_html($last_sync->log_time); ?> &mdash; <?php echo esc_html($last_sync->message); ?></p>
        <?php endif; ?>
      </div>

      <div class="nrdfi-log-actions" style="margin-top: 1rem;">
        <button id="nrdfi-run-import" class="button button-primary">Run Import Now</button>
        <span id="nrdfi-log-status"></span>
      </div>
    </div>

  </div>

  <?php include 'partials/support.php'; ?>

</div>
