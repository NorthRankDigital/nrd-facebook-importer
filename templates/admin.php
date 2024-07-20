<div class="wrap">
  <h1>Untappd API Settings</h1>
 
  <?php include 'partials/nav.php'; ?>
  
  <div class="nrd-wraper">
    <div class="nrd-content" style="max-width: 40rem;">
      <form action="options.php" method="POST">
        <?php 
          settings_fields( 'nrd_untappd_importer_settings' );
          do_settings_sections('nrd_untappd_importer');
          submit_button();  
        ?>
      </form>

      <hr>
      <p>Test the credentials entered above.</p>
      <div class="nrd-flex-row nrd-items-center">
        <button id="test-api" class="nrd-btn">Test Connection</button>
        <div id="result"></div>
      </div>
    </div>

    </div>
  </div>

  <?php include 'partials/support.php'; ?>

  
</div>