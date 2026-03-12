<?php
  $token_manager = new \NrdFacebookImporter\Inc\Base\TokenManager();
  $token_status = $token_manager->getTokenStatus();
  $days_left = $token_manager->getDaysUntilExpiry();

  $app_creds = get_option('nrd_facebook_importer', array());
  $has_app_creds = !empty($app_creds)
    && isset($app_creds['nrdfi_facebook_app_id'])
    && isset($app_creds['nrdfi_facebook_app_secret'])
    && strlen($app_creds['nrdfi_facebook_app_id']) >= 15
    && strlen($app_creds['nrdfi_facebook_app_secret']) >= 10;
?>
<div class="wrap">
  <h1>Facebook Importer</h1>

  <?php include 'partials/nav.php'; ?>

  <div class="nrdfi-wraper">

    <!-- API Configuration Card -->
    <div class="nrdfi-card nrdfi-card-first">
      <h2>API Configuration</h2>

      <div class="nrdfi-description">
        <p>
          <strong>Note:</strong> A Facebook application must be created before connecting.<br>
          <strong>Site URL:</strong> <span class="nrdfi-copy" title="Click to copy"><?php echo esc_url(home_url()); ?></span><br>
          <strong>Redirect URI:</strong> <span class="nrdfi-copy" title="Click to copy"><?php echo esc_url(home_url('/wp-admin/admin-post.php?action=nrdfi_facebook_authorize_callback')); ?></span>
        </p>
      </div>

      <form action="options.php" method="POST">
        <?php
          settings_fields('nrd_facebook_importer_settings');
          do_settings_sections('nrd_facebook_importer');
          submit_button('Save Credentials');
        ?>
      </form>
    </div>

    <!-- Connection Status Card -->
    <div class="nrdfi-card">
      <h2>Connection Status</h2>

      <?php if (!$has_app_creds) : ?>

        <div class="nrdfi-status-card">
          <div class="nrdfi-status-header">
            <span class="nrdfi-status-dot nrdfi-dot-gray"></span>
            <strong>Not Configured</strong>
          </div>
          <p class="nrdfi-help-text">Enter your Facebook App ID and App Secret, then save to continue.</p>
        </div>

      <?php elseif ($token_status === 'none') : ?>

        <div class="nrdfi-status-card nrdfi-status-error">
          <div class="nrdfi-status-header">
            <span class="nrdfi-status-dot nrdfi-dot-red"></span>
            <strong>Not Authenticated</strong>
          </div>
          <p class="nrdfi-help-text">Authenticate with Facebook to begin importing events.</p>
          <div class="nrdfi-auth-actions">
            <button id="nrd-facebook-auth" class="button button-primary">Authenticate with Facebook</button>
            <span id="result"></span>
          </div>
        </div>

      <?php else : ?>

        <div class="nrdfi-status-card nrdfi-status-<?php echo $token_status === 'active' ? 'ok' : ($token_status === 'expiring' ? 'warn' : 'error'); ?>">

          <div class="nrdfi-status-header">
            <?php if ($token_status === 'active') : ?>
              <span class="nrdfi-status-dot nrdfi-dot-green"></span>
              <strong>User Token</strong>
              <span class="nrdfi-status-badge nrdfi-badge-green"><?php echo esc_html($days_left); ?> days remaining</span>
            <?php elseif ($token_status === 'expiring') : ?>
              <span class="nrdfi-status-dot nrdfi-dot-amber"></span>
              <strong>User Token</strong>
              <span class="nrdfi-status-badge nrdfi-badge-amber"><?php echo esc_html($days_left); ?> <?php echo $days_left === 1 ? 'day' : 'days'; ?> remaining</span>
            <?php else : ?>
              <span class="nrdfi-status-dot nrdfi-dot-red"></span>
              <strong>User Token</strong>
              <span class="nrdfi-status-badge nrdfi-badge-red">Expired</span>
            <?php endif; ?>
          </div>

          <?php if (in_array($token_status, array('active', 'expiring'), true) && $days_left > 0) : ?>
            <div class="nrdfi-progress-bar">
              <div class="nrdfi-progress-fill <?php echo $token_status === 'expiring' ? 'nrdfi-progress-amber' : ''; ?>" style="width: <?php echo esc_attr(min(100, round(($days_left / 60) * 100))); ?>%;"></div>
            </div>
          <?php endif; ?>

          <div class="nrdfi-auth-actions">
            <button id="nrd-facebook-auth" class="button button-secondary">Re-authenticate</button>
            <span id="result"></span>
          </div>
        </div>

      <?php endif; ?>
    </div>

  </div>

  <?php include 'partials/support.php'; ?>

</div>
