jQuery(document).ready(function ($) {
  $('.dismiss-setup-wizard-notice').on('click', function(e) {

    e.preventDefault();
    $.ajax({
      url: setup_wizard_notice_options.ajax,
      type: "POST",
      data: {
        action: "wwof_dismiss_setup_wizard_notice",
        nonce: setup_wizard_notice_options.nonce
      }
    })
      .done(function (data) {
        $('.setup-wizard-notice-wrapper').hide();
      });

  });
});
