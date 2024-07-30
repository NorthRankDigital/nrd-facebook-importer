<?php $current_page = isset($_GET['page']) ? $_GET['page'] : '';  ?>

<?php settings_errors() ?>

<div id="nrd-status" class="nrdfi-hidden notice is-dismissible">
  <p><strong id="nrd-status-message"></strong></p>
</div>

<ul class="nrdfi-nav">
  <li class="<?php echo ($current_page == 'nrd_facebook_importer' ? 'nrdfi-active-link' : '') ?>">
    <a href="<?php echo admin_url('admin.php?page=nrd_facebook_importer'); ?>">API Settings</a>
  </li>
  <li class="<?php echo ($current_page == 'nrd_facebook_importer_schedule_import' ? 'nrdfi-active-link' : '') ?>">
    <a href="<?php echo admin_url('admin.php?page=nrd_facebook_importer_schedule_import'); ?>">Schedule Import</a>
  </li>
</ul>
