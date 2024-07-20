<div class="wrap">
  <h1>Untappd Schedule Import</h1>
    
  <?php include 'partials/nav.php'; ?>
  <div class="nrd-wraper">
    <div class="nrd-content" style="max-width: 40rem;">
      <form action="options.php" method="POST">
        <?php
        settings_fields('nrd_untappd_importer_schedule_settings');
        do_settings_sections('nrd_untappd_importer_schedule_import');
        submit_button();
        ?>
      </form>

      <?php
        $cron_jobs = _get_cron_array();

        if (empty($cron_jobs)) {
          echo '<p>No scheduled cron jobs found for update_data_event.</p>';
          return;
        }

        // Find the next scheduled run for 'update_data_event'
        $next_timestamp = false;
        foreach ($cron_jobs as $timestamp => $cron) {
          foreach ($cron as $hook => $details) {
            if ($hook === 'update_data_event') {
              $next_timestamp = $timestamp;
              break 2; // Exit both foreach loops
            }
          }
        }
        
        echo '<hr>';
        echo '<p>';

        if (!$next_timestamp) {
          echo '<p><strong>Next Run:</strong> No upcoming schedule found for update_data_event.</p>';
        }
        else
        {
          // Calculate the time until the next run
          $now = time();
          $difference = $next_timestamp - $now;

          // Convert difference to days, hours, minutes, seconds
          $days = floor($difference / (60 * 60 * 24));
          $hours = floor(($difference % (60 * 60 * 24)) / (60 * 60));
          $minutes = floor(($difference % (60 * 60)) / 60);
          $seconds = $difference % 60;

          echo '<strong>Next Run:</strong> ' . date('D M d H:i:s', $next_timestamp) . '<br>';
          echo '<strong>Time Until Next Run: </strong>';
          echo "{$days} days, {$hours} hours, {$minutes} minutes, {$seconds} seconds";
        } 

        echo '</p>';
      ?>
    </div>
  </div>
  <?php include 'partials/support.php'; ?>
</div>