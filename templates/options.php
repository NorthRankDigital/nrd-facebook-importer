<div class="wrap">
  <h1>Facebook Importer</h1>

  <?php include 'partials/nav.php'; ?>

  <div class="nrdfi-wraper">

    <div class="nrdfi-card nrdfi-card-first">
      <h2>Plugin Options</h2>

      <form action="options.php" method="POST">
        <?php
          settings_fields('nrd_facebook_importer_options_settings');
          do_settings_sections('nrd_facebook_importer_options');
          submit_button('Save Options');
        ?>
      </form>
    </div>

  </div>

  <?php include 'partials/support.php'; ?>

</div>
