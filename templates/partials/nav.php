<?php $current_page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : ''; ?>

<?php settings_errors(); ?>

<ul class="nrdfi-nav">
  <li class="<?php echo ($current_page === 'nrd_facebook_importer' ? 'nrdfi-active-link' : ''); ?>">
    <a href="<?php echo esc_url(admin_url('admin.php?page=nrd_facebook_importer')); ?>">FB Connection</a>
  </li>
  <li class="<?php echo ($current_page === 'nrd_facebook_importer_schedule_import' ? 'nrdfi-active-link' : ''); ?>">
    <a href="<?php echo esc_url(admin_url('admin.php?page=nrd_facebook_importer_schedule_import')); ?>">Schedule</a>
  </li>
  <li class="<?php echo ($current_page === 'nrd_facebook_importer_log' ? 'nrdfi-active-link' : ''); ?>">
    <a href="<?php echo esc_url(admin_url('admin.php?page=nrd_facebook_importer_log')); ?>">Log</a>
  </li>
  <li class="<?php echo ($current_page === 'nrd_facebook_importer_options' ? 'nrdfi-active-link' : ''); ?>">
    <a href="<?php echo esc_url(admin_url('admin.php?page=nrd_facebook_importer_options')); ?>">Options</a>
  </li>
</ul>
