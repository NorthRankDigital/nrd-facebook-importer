jQuery(document).ready(function ($) {

  $("#nrd-facebook-auth").on("click", function (event) {
    event.preventDefault();
    $.ajax({
      url: ajax_object.ajax_url,
      type: "POST",
      data: {
        action: "nrdfi_facebook_auth",
      },
      success: function (response) {
        if (response.success) {
          window.location.href = response.data.login_url;
        } else {
          alert("Error generating Facebook login URL.");
        }
      },
    });
  });
});
