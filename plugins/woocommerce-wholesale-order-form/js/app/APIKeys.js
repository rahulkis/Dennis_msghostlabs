jQuery(document).ready(function ($) {
  var errorMessageDuration = "10000",
    successMessageDuration = "5000";

  $("#wwof_generate_api_keys").on("click", function (e) {
    
    e.preventDefault();
    var $this = $(this);

    $this
      .attr("disabled", "disabled")
      .siblings(".spinner")
      .css("display", "inline-block")
      .css("visibility", "visible");
    
    // Create WWOF API Key
    $.ajax({
      url: api_keys.root + "wwof/v1/api-keys",
      type: "POST",
      headers: {
        "X-WP-Nonce": api_keys.nonce,
      },
      dataType: "json",
    }).done(function(data){
      if (data.success === true) {
        toastr.success(api_keys.success_message, api_keys.i18n.success, {
          closeButton: true,
          showDuration: successMessageDuration,
        });

        // Hide Generate Key row
        $this.closest("tr").css("display", "none");

        // Make status valid
        $("body").find("div.status.valid").show();
        $("body").find("div.status.invalid").hide();

        // Update consumer key and secret
        $("body")
          .find("#wwof_v2_consumer_key")
          .val(data.data.consumer_key);

        $("body")
          .find("#wwof_v2_consumer_secret")
          .val(data.data.consumer_secret);
      } else {
        toastr.error(data.message, api_keys.i18n.fail, {
          closeButton: true,
          showDuration: errorMessageDuration,
        });
      }
    }).fail(function (jqxhr) {
      console.log(jqxhr);
    })
    .always(function () {
      $this
        .removeAttr("disabled")
        .siblings(".spinner")
        .css("visibility", "hidden");
    });

  });
});
