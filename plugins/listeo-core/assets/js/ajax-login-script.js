/* ----------------- Start Document ----------------- */
(function ($) {
  "use strict";

  $(document).ready(function () {
    // $( 'body' ).on( 'keyup', 'input[name=password]', function( event ) {
    //     $('.pwstrength_viewport_progress').addClass('password-indicator-visible');

    //   });
    $("input[name=password]").keypress(function () {
      $(".pwstrength_viewport_progress")
        .addClass("password-strength-visible")
        .animate({ opacity: 1 });
    });

    var options = {};
    options.ui = {
      //container: "#password-row",
      viewports: {
        progress: ".pwstrength_viewport_progress",
        //  verdict: ".pwstrength_viewport_verdict"
      },
      colorClasses: ["bad", "short", "normal", "good", "good", "strong"],
      showVerdicts: false,
      minChar: 8,
      //useVerdictCssClass
    };
    options.common = {
      debug: true,
      onLoad: function () {
        $("#messages").text("Start typing password");
      },
    };
    $(":password").pwstrength(options);

    // Perform AJAX login on form submit
    $("#sign-in-dialog form#login").on("submit", function (e) {
      var redirecturl = $("input[name=_wp_http_referer]").val();
      var success;
      $("form#login .notification")
        .removeClass("error")
        .addClass("notice")
        .show()
        .text(listeo_login.loadingmessage);

      $.ajax({
        type: "POST",
        dataType: "json",
        url: listeo_login.ajaxurl,
        data: {
          action: "listeoajaxlogin",
          username: $("form#login #user_login").val(),
          password: $("form#login #user_pass").val(),
          rememberme: $("form#login #remember-me").val(),
          login_security: $("form#login #login_security").val(),
        },
      })
        .done(function (data) {
          if (data.loggedin == true) {
            $("form#login .notification")
              .show()
              .removeClass("error")
              .removeClass("notice")
              .addClass("success")
              .text(data.message);

            success = true;
          } else {
            $("form#login .notification")
              .show()
              .addClass("error")
              .removeClass("notice")
              .removeClass("success")
              .text(data.message);
          }
        })
        .fail(function (reason) {
          // Handles errors only
          console.debug("reason" + reason);
        })

        .then(function (data, textStatus, response) {
          if (success) {
            $.ajax({
              type: "POST",
              dataType: "json",
              url: listeo_login.ajaxurl,
              data: {
                action: "get_logged_header",
              },
              success: function (new_data) {
                $("body").removeClass("user_not_logged_in");
                $(".header-widget").html(new_data.data.output);
                var magnificPopup = $.magnificPopup.instance;
                if (magnificPopup) {
                  magnificPopup.close();
                }
              },
            });

            // if it's single listing page  
            if($('body').hasClass('single-listing')){
              $(".woocommerce-info").remove();
              $(".password-notice-info").remove();
           
            }

            var post_id = $("#form-booking").data("post_id");
            var owner_widget_id = $(".widget_listing_owner").attr("id");
            var freeplaces = $(".book-now-notloggedin").data("freeplaces");
            var booking_form = $("#booking-confirmation");

            if (post_id) {
              $.ajax({
                type: "POST",
                dataType: "json",
                url: listeo_login.ajaxurl,
                data: {
                  action: "get_booking_button",
                  post_id: post_id,
                  owner_widget_id: owner_widget_id,
                  freeplaces: freeplaces,
                },
                success: function (new_data) {
                  var freeplaces = $(".book-now-notloggedin").data(
                    "freeplaces"
                  );
                  $(".book-now-notloggedin").replaceWith(
                    new_data.data.booking_btn
                  );
                  $(".like-button-notlogged").replaceWith(
                    new_data.data.bookmark_btn
                  );
                  $("#owner-widget-not-logged-in").replaceWith(
                    new_data.data.owner_data
                  );
                },
              });
            }
            if (booking_form.length) {
              $(".woocommerce-info").remove();
              $(".booking-registration-field").remove();
              $.ajax({
                type: "POST",
                dataType: "json",
                url: listeo_login.ajaxurl,
                data: {
                  action: "get_booking_form",
                },
                success: function (data) {
                  console.log(data);

                  // Access the values
                  var email = data.data.email;
                  var firstName = data.data.first_name;
                  var lastName = data.data.last_name;
                  var phone = data.data.phone;
                  var address = data.data.billing_address_1;
                  var postcode = data.data.billing_postcode;
                  var city = data.data.billing_city;

                  $("#booking-confirmation #email").val(email);
                  $("#booking-confirmation #firstname").val(firstName);
                  $("#booking-confirmation #lastname").val(lastName);
                  $("#booking-confirmation #phone").val(phone);
                  $("#booking-confirmation #billing_address_1").val(address);
                  $("#booking-confirmation #billing_postcode").val(postcode);
                  $("#booking-confirmation #billing_country").val(city);
                },
              });
            }
          }

          // In case your working with a deferred.promise, use this method
          // Again, you'll have to manually separates success/error
        });
      e.preventDefault();
    });

    // Perform AJAX login on form submit
    $("#sign-in-dialog form#register").on("submit", function (e) {
      $("form#register .notification")
        .removeClass("error")
        .addClass("notice")
        .show()
        .text(listeo_login.loadingmessage);

      var form = new FormData(document.getElementById("register"));
      var action_key = {
        name: "action",
        value: "listeoajaxregister",
      };
      var privacy_key = {
        name: "privacy_policy",
        value: $("form#register #privacy_policy:checked").val(),
      };

      form.append("action", "listeoajaxregister");
      form.append(
        "privacy_policy",
        $("form#register #privacy_policy:checked").val()
      );
      form.append(
        "terms_and_conditions",
        $("form#register #terms_and_conditions:checked").val()
      );

      $.ajax({
        type: "POST",
        dataType: "json",
        url: listeo_login.ajaxurl,
        // data: form,
        data: form,
        processData: false,
        contentType: false,
      
        success: function (data) {
          if (data.registered == true) {
            $("form#register .notification")
              .show()
              .removeClass("error")
              .removeClass("notice")
              .addClass("success")
              .text(data.message);
            // $( 'body, html' ).animate({
            //                 scrollTop: $('#sign-in-dialog').offset().top
            //             }, 600 );
            $("#register").find("input:text").val("");
            $("#register input:checkbox").removeAttr("checked");
            if (listeo_core.autologin) {
              setTimeout(function () {
                window.location.reload(); // you can pass true to reload function to ignore the client cache and reload from the server
              }, 2000);
            }
          } else {
            $("form#register .notification")
              .show()
              .addClass("error")
              .removeClass("notice")
              .removeClass("success")
              .text(data.message);

            if (listeo_core.recaptcha_status) {
              if (listeo_core.recaptcha_version == "v3") {
                getRecaptcha();
              }
            }
          }
        },
      });
      e.preventDefault();
    });
  });
})(this.jQuery);
