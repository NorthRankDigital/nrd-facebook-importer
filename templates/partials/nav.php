<?php $current_page = isset($_GET['page']) ? $_GET['page'] : '';  ?>

<?php settings_errors() ?>

<div id="nrd-status" class="nrd-hidden notice is-dismissible">
  <p><strong id="nrd-status-message"></strong></p>
</div>

<ul class="nrd-nav">
  <li class="<?php echo ($current_page == 'nrd_untappd_importer' ? 'nrd-active-link' : '') ?>">
    <a href="<?php echo admin_url('admin.php?page=nrd_untappd_importer'); ?>">API Settings</a>
  </li>
  <li class="<?php echo ($current_page == 'nrd_untappd_importer_menus' ? 'nrd-active-link' : '') ?>">
    <a href="<?php echo admin_url('admin.php?page=nrd_untappd_importer_menus'); ?>">Menu Manager</a>
  </li>
  <li class="<?php echo ($current_page == 'nrd_untappd_importer_schedule_import' ? 'nrd-active-link' : '') ?>">
    <a href="<?php echo admin_url('admin.php?page=nrd_untappd_importer_schedule_import'); ?>">Schedule Import</a>
  </li>
  <li class="<?php echo ($current_page == 'nrd_untappd_importer_about' ? 'nrd-active-link' : '') ?>">
    <a href="<?php echo admin_url('admin.php?page=nrd_untappd_importer_about'); ?>">About</a>
  </li>
</ul>
