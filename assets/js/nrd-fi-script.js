jQuery(document).ready(function ($) {

  function showSpinner($el) {
    $el.after('<span class="nrdfi-spinner"></span>');
  }

  function removeSpinner() {
    $(".nrdfi-spinner").remove();
  }

  // Click to copy
  $(".nrdfi-copy").on("click", function () {
    var $el = $(this);
    var text = $el.text().trim();

    navigator.clipboard.writeText(text).then(function () {
      $el.addClass("nrdfi-copied");
      setTimeout(function () {
        $el.removeClass("nrdfi-copied");
      }, 1500);
    });
  });

  // Authenticate with Facebook
  $("#nrd-facebook-auth").on("click", function (event) {
    event.preventDefault();
    var $btn = $(this);
    $btn.prop("disabled", true);
    showSpinner($btn);

    $.ajax({
      url: nrdfi_ajax.ajax_url,
      type: "POST",
      data: {
        action: "nrdfi_facebook_auth",
        nonce: nrdfi_ajax.nonce,
      },
      success: function (response) {
        if (response.success) {
          window.location.href = response.data.login_url;
        } else {
          removeSpinner();
          $btn.prop("disabled", false);
          $("#result").text("Failed to generate login URL.");
        }
      },
      error: function () {
        removeSpinner();
        $btn.prop("disabled", false);
        $("#result").text("Request failed.");
      },
    });
  });

  // Refresh Facebook pages
  $("#nrd-get-pages").on("click", function (event) {
    event.preventDefault();
    var $link = $(this);
    var $status = $("#nrd-get-pages-status");
    var originalText = $link.text();

    $link.text("Refreshing...");
    $status.text("").removeClass("nrdfi-inline-error");

    $.ajax({
      url: nrdfi_ajax.ajax_url,
      type: "POST",
      data: {
        action: "nrdfi_get_pages",
        nonce: nrdfi_ajax.nonce,
      },
      success: function (response) {
        if (response.success) {
          location.reload();
        } else {
          $link.text(originalText);
          var msg = response.data || "Failed to fetch pages. Check your authentication.";
          $status.text(msg).addClass("nrdfi-inline-error");
        }
      },
      error: function (xhr) {
        $link.text(originalText);
        var msg = "Request failed.";
        if (xhr.status === 403) {
          msg = "Unauthorized. Please re-authenticate with Facebook.";
        } else if (xhr.status === 0) {
          msg = "Network error. Please check your connection.";
        }
        $status.text(msg).addClass("nrdfi-inline-error");
      },
    });
  });

  // Run import now
  $("#nrdfi-run-import").on("click", function (event) {
    event.preventDefault();
    var $btn = $(this);
    var $status = $("#nrdfi-log-status");

    $btn.prop("disabled", true).text("Importing...");
    showSpinner($btn);
    $status.text("");

    $.ajax({
      url: nrdfi_ajax.ajax_url,
      type: "POST",
      data: {
        action: "nrdfi_run_import",
        nonce: nrdfi_ajax.nonce,
      },
      success: function (response) {
        removeSpinner();
        if (response.success) {
          $status.text(response.data.message);
          setTimeout(function () {
            location.reload();
          }, 800);
        } else {
          $status.text("Import failed.");
          $btn.prop("disabled", false).text("Run Import Now");
        }
      },
      error: function () {
        removeSpinner();
        $status.text("Request failed. Please try again.");
        $btn.prop("disabled", false).text("Run Import Now");
      },
    });
  });

  // Clear log
  $("#nrdfi-clear-log").on("click", function (event) {
    event.preventDefault();

    if (!confirm("Are you sure you want to clear all log entries?")) {
      return;
    }

    var $btn = $(this);
    var $status = $("#nrdfi-log-status");

    $btn.prop("disabled", true);
    $status.text("Clearing...");

    $.ajax({
      url: nrdfi_ajax.ajax_url,
      type: "POST",
      data: {
        action: "nrdfi_clear_log",
        nonce: nrdfi_ajax.nonce,
      },
      success: function (response) {
        if (response.success) {
          location.reload();
        } else {
          $status.text("Failed to clear log.");
          $btn.prop("disabled", false);
        }
      },
      error: function () {
        $status.text("Request failed.");
        $btn.prop("disabled", false);
      },
    });
  });
});
