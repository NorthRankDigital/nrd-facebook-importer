<?php 
  $menu_singular = esc_attr( get_option( 'nrd_ui_cpt_singular'));
  $menu_plural = esc_attr( get_option( 'nrd_ui_cpt_plural'));
  $menu_select = esc_attr( get_option( 'nrd_ui_untappd_menu'));
?>

<div class="wrap">
  <h1>Untappd Menu Manager</h1>

  <?php include 'partials/nav.php'; ?>
  <div class="nrd-wraper">
    <div class="nrd-content">

      <div class="nrd-description">
        <p><a id="get-menus" href="#">Click here to sync the Untappd menu list.</a></p>
      </div>

      <form action="options.php" method="POST">
        <?php
          settings_fields('nrd_untappd_importer_menu_settings');
          do_settings_sections('nrd_untappd_importer_menus');
          submit_button((isset($_POST["edit_post"]) ? 'Save Menu' : 'Add Menu'));
        ?>
      </form>
      
      <div class="nrd-table-wrapper">
        <table class="nrd-table">
          <thead>
            <tr>
              <th class="nrd-txt-left">Menu Name Singular</th>
              <th class="nrd-txt-left">Menu Name Plural</th>
              <th class="nrd-txt-left">Untappd Menu ID</th>
              <th class="nrd-txt-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $options = get_option('nrd_untappd_importer_menu') ?: array();
              if (count($options) == 0) {
                echo '<tr><td colspan=4 class="nrd-txt-center nrd-txt-mute">No Menus Added</td></tr>';
              } 
              else 
              {
                foreach ($options as $option) {
                  echo '<tr>';
                  echo '<td>' . $option['singular_name'] . '</td>';
                  echo '<td>' . $option['plural_name'] . '</td>';
                  echo '<td>' . $option['post_type'] . '</td>';
                  echo '<td class="nrd-txt-center nrd-flex-row nrd-justify-center">';

                  echo '<button data-item-id="'. $option['post_type'].'" class="nrd-sync-menu button nrd-btn-info">Sync Now</button>';
                 
                  echo '<form action="" method="POST" class="nrd-inline">';
                  echo '<input type="hidden" name="edit_post" value="' . $option['post_type'] . '">';
                  submit_button("Edit", "nrd-btn-warning","submit", false);
                  echo '</form>';
                  
                  echo '<form action="options.php" method="POST" class="nrd-inline">';
                  settings_fields('nrd_untappd_importer_menu_settings');
                  echo '<input type="hidden" name="remove" value="' . $option['post_type'] . '">';
                  submit_button("Delete", "nrd-btn-danger", "submit", false, array(
                      'onclick' => 'return confirm("Are you sure you want to delete this menu? The data associated with it will not be deleted.");'
                    )
                  );
                  echo '</form>';


                  echo '</td>';
                  echo '</tr>';
                }
              }               
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php include 'partials/support.php'; ?>
</div>
