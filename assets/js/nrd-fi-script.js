jQuery(document).ready(function($) {

  function setMessage(response) {
    if (!response.success) {
      $("#nrd-status")
        .removeClass("nrd-hidden")
        .addClass("nrd-show notice-error");
      $("#nrd-status-message").text(response.data.message);
    } else {
      $("#nrd-status")
        .removeClass("nrd-hidden")
        .addClass("nrd-show notice-success");
      $("#nrd-status-message").text(response.data.message);
    }
  }

  function fillSelectField(data) {
    $("#untappd-menus").empty();
    $.each(data, function (index, value) {
      $("#untappd-menus").append(
        '<option value="' + index + '">' + value + "</option>"
      );
    });
  }

  $("#test-api").on("click", function (event) {
    event.preventDefault();
    $.ajax({
      url: ajax_object.ajax_url,
      type: "POST",
      data: {
        action: "test_api",
      },
      success: function (response) {
        setMessage(response);
      },
      error: function (xhr, status, error) {
        $("#result").html(error);
      },
    });
  });

  $("#get-menus").on("click", function (event) {
    event.preventDefault();
    $.ajax({
      url: ajax_object.ajax_url,
      type: "POST",
      data: {
        action: "get_menus",
      },
      success: function (response) {
        if(response.success)
        {
          setMessage(response);
          fillSelectField(response.data.data);
        }
        else
        {
          setMessage(response);
        }
      },
      error: function (xhr, status, error) {
        $("#result").html(error);
      },
    });
  });

   $(".nrd-sync-menu").on("click", function (event) {
     event.preventDefault();
     var itemId = $(this).data("item-id");
     var $button = $(this);
     // Store the original button content
     var originalContent = $button.html();
     // Replace button content with spinner
     $button.html(originalContent + '<div class="nrd-spinner"></div>');

     $.ajax({
       url: ajax_object.ajax_url,
       type: "POST",
       data: {
         action: "sync_menu",
         item_id: itemId,
       },
       success: function (response) {
        $button.html(originalContent);
         if (response.success) {
           setMessage(response);
         } else {
           setMessage(response);
         }
       },
       error: function (xhr, status, error) {
        $button.html(originalContent);
         $("#result").html(error);
       },
     });
   });
});


