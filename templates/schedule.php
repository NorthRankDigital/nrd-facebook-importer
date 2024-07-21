<div class="wrap">
  <h1>Facebook Import Schedule</h1>

  <?php include 'partials/nav.php'; ?>

  <div class="nrd-wraper">
    <div class="nrd-content" style="max-width: 40rem;">
      <form action="options.php" method="POST">
      <?php
      settings_fields('nrd_facebook_importer_schedule_settings');
      do_settings_sections('nrd_facebook_importer_schedule_import');
      submit_button();
      ?>
      </form>
    </div>
  </div>

</div>