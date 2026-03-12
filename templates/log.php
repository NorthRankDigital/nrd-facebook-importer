<?php
  $logger = new \NrdFacebookImporter\Inc\Base\ImportLogger();
  $current_page = isset($_GET['log_page']) ? (int) $_GET['log_page'] : 1;
  $current_page = max(1, $current_page);
  $per_page = 50;
  $total = (int) $logger->getTotalEntries();
  $total_pages = (int) max(1, ceil($total / $per_page));
  $entries = $logger->getEntries($per_page, $current_page);
  $last_sync = $logger->getLastSync();
?>
<div class="wrap">
  <h1>Facebook Importer</h1>

  <?php include 'partials/nav.php'; ?>

  <div class="nrdfi-wraper">

    <div class="nrdfi-card nrdfi-card-first">
      <h2>Import Log</h2>

      <?php if ($last_sync) : ?>
        <div class="nrdfi-last-sync">
          <strong>Last sync:</strong> <?php echo esc_html($last_sync->log_time); ?> &mdash; <?php echo esc_html($last_sync->message); ?>
        </div>
      <?php endif; ?>

      <div class="nrdfi-log-actions">
        <button id="nrdfi-run-import" class="button button-primary">Run Import Now</button>
        <button id="nrdfi-clear-log" class="button button-link-delete">Clear Log</button>
        <span id="nrdfi-log-status"></span>
      </div>

      <?php if (empty($entries)) : ?>
        <p style="color: #6b7280;">No log entries yet. Run an import to see activity here.</p>
      <?php else : ?>
        <table class="nrdfi-log-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Type</th>
              <th>Message</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($entries as $entry) : ?>
              <tr class="nrdfi-log-row nrdfi-log-<?php echo esc_attr($entry->event_type); ?>">
                <td class="nrdfi-log-date"><?php echo esc_html($entry->log_time); ?></td>
                <td>
                  <span class="nrdfi-log-type nrdfi-log-type-<?php echo esc_attr($entry->event_type); ?>">
                    <?php echo esc_html(str_replace('_', ' ', $entry->event_type)); ?>
                  </span>
                </td>
                <td><?php echo esc_html($entry->message); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <?php if ($total_pages > 1) : ?>
          <div class="nrdfi-pagination">
            <?php $prev_page = (int) $current_page - 1; $next_page = (int) $current_page + 1; ?>
            <?php if ($current_page > 1) : ?>
              <a href="<?php echo esc_url(add_query_arg('log_page', $prev_page)); ?>" class="button button-small">&laquo; Previous</a>
            <?php endif; ?>
            <span class="nrdfi-page-info">Page <?php echo (int) $current_page; ?> of <?php echo (int) $total_pages; ?> (<?php echo (int) $total; ?> entries)</span>
            <?php if ($current_page < $total_pages) : ?>
              <a href="<?php echo esc_url(add_query_arg('log_page', $next_page)); ?>" class="button button-small">Next &raquo;</a>
            <?php endif; ?>
          </div>
        <?php endif; ?>

      <?php endif; ?>

    </div>

  </div>
</div>
