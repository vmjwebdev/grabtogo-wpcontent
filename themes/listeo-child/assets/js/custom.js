jQuery(function($) {
    const form          = $('#gtg-reg-form');
    const emailField    = $('#gtg_email');
    const otpInput      = $('#gtg_otp_input');
    const sendOTPBtn    = $('#send_otp_btn');
    const verifyOTPBtn  = $('#verify_otp_btn');
    const otpSection    = $('#otp_section');
    const registerBtn   = $('#gtg_submit_btn');
    const otpStatus     = $('#otp_status');

    // Initially disable register button
    registerBtn.prop('disabled', true);

    // ===== Client-side validation: GST & Phone on submit =====
    form.on('submit', function(e) {
        const gst   = $('input[name="gtg_gst_number"]').val().trim();
        const phone = $('input[name="gtg_whatsapp"]').val().trim();
        const gstRegex   = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][A-Z0-9]Z[A-Z0-9]$/;
        const phoneRegex = /^[6-9]\d{9}$/;

        if (!phoneRegex.test(phone)) {
            e.preventDefault();
            alert('Please enter a valid 10-digit Indian mobile number starting with 6-9.');
            return false;
        }
        if (!gstRegex.test(gst)) {
            e.preventDefault();
            alert('Please enter a valid 15-character GSTIN (e.g., 22ABCDE1234F1Z5).');
            return false;
        }
    });

    // ===== Send OTP =====
    sendOTPBtn.on('click', function () {
        const email = emailField.val().trim();
        if (!email) {
            alert('Please enter your email before requesting OTP.');
            return;
        }

        $.ajax({
            url: gtg_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'gtg_send_otp',
                email: email
            },
            beforeSend: function () {
                sendOTPBtn.text('Sending...').prop('disabled', true);
            },
            success: function (res) {
                if (res.success) {
                    otpStatus.html('<span style="color:green;">OTP sent to your email.</span>');
                    otpSection.show();
                } else {
                    otpStatus.html('<span style="color:red;">' + res.data + '</span>');
                }
                sendOTPBtn.text('Resend OTP').prop('disabled', false);
            }
        });
    });

    // ===== Verify OTP =====
    verifyOTPBtn.on('click', function () {
        const email = emailField.val().trim();
        const otp   = otpInput.val().trim();
        if (!otp) {
            alert('Please enter the OTP.');
            return;
        }

        $.ajax({
            url: gtg_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'gtg_verify_otp',
                email: email,
                otp: otp
            },
            success: function (res) {
                if (res.success) {
                    otpStatus.html('<span style="color:green;">OTP verified successfully.</span>');
                    sendOTPBtn.hide();
                    verifyOTPBtn.hide();
                    otpSection.hide();
                    registerBtn.prop('disabled', false);
                } else {
                    otpStatus.html('<span style="color:red;">' + res.data + '</span>');
                }
            }
        });
    });

    // ===== Toggle password visibility =====
    form.on('click', '.gtg-toggle-password', function() {
        const $inp  = $(this).siblings('input[type="password"],input[type="text"]');
        const isPwd = $inp.attr('type') === 'password';
        $inp.attr('type', isPwd ? 'text' : 'password');
        $(this).toggleClass('fa-eye fa-eye-slash');
    });
});