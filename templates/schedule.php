<div class="wrap">
  <h1>Facebook Import Schedule</h1>

  <?php include 'partials/nav.php'; ?>

  <div class="nrdfi-wraper">
    <div class="nrdfi-content" style="max-width: 40rem;">
      <div class="nrdfi-description">
        <p>Update page listings <a href="#" id="nrd-get-pages">click here</a></p>
        <?php echo get_option( 'nrd_facebook_access_token', array()); ?>
      </div>
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