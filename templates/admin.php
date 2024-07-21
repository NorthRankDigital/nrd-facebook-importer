<div class="wrap">
  <h1>Facebook API Settings</h1>
 
  <?php include 'partials/nav.php'; ?>
  
  <div class="nrd-wraper">
    <div class="nrd-content" style="max-width: 45rem;">
      <p></p>
      <div class="nrdfi-description">
        <p>
          <strong>Note:</strong> A Facebook application must be created. For detailed instructions <a href="#">click here</a>. <br>
          <strong>Site url:</strong> <span><?php echo home_url(); ?> </span> <br>
          <strong>Redirect URI:</strong> <span>
            <?php echo home_url() . "/wp-admin/admin-post.php?action=nrdfi_facebook_authorize_callback"; ?> </span>
        </p>
      </div>
      
      <form action="options.php" method="POST">
        <?php 
          settings_fields( 'nrd_facebook_importer_settings' );
          do_settings_sections('nrd_facebook_importer');
          submit_button();  
        ?>
      </form>

      
      <?php 
        $app_creds = get_option('nrd_facebook_importer', array());
        $access_token = get_option('nrd_facebook_access_token', array());

        if(empty($app_creds) || (strlen($app_creds['nrdfi_facebook_app_id']) < 15 && strlen($app_creds['nrdfi_facebook_app_secret']) < 10))
        {
          ?>
            <hr>
            <p>Please enter Facebook app ID and app secret.</p>
          <?php
        } 
        else if(!empty($access_token) && $access_token['expires_at'] > time())
        {
          ?>
          <hr>
          <p>
            Facebook App Status: <span style="color:green; font-weight: 700;">Authenticated</span> <br>
            Expires: <?php echo date('F j, Y, g:i a', $access_token['expires_at']); ?>
            <div class="nrd-flex-row nrd-items-center">
              <button id="nrd-facebook-auth" class="nrd-btn">Re-authenticate Now</button>
              <div id="result"></div>
            </div>
          </p>

          <?php

        }
        else
        {
          ?>
            <hr>
            <p>Facebook App Status: <span style="color:red; font-weight: 700;">NOT Authenticated</span></p>
            <p>Please authenticate with Facebook</p>
            <div class="nrd-flex-row nrd-items-center">
              <button id="nrd-facebook-auth" class="nrd-btn">Authenticate</button>
              <div id="result"></div>
            </div>
          <?php
        }
      ?>      
    </div>

    
    <!-- TODO: Add links to help documents -->

    </div>
  </div>

  <?php include 'partials/support.php'; ?>

  
</div>