jQuery(function($) {

    /* ===========================
       OTP REGISTRATION SCRIPT
       =========================== */
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
        const phoneRegex = /^[6-9]\d{9}$/;

        if (!phoneRegex.test(phone)) {
            e.preventDefault();
            alert('Please enter a valid 10-digit Indian mobile number starting with 6-9.');
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

    // ===== Hide "Become a Vendor" link for logged-in users =====
    if ($('body').hasClass('logged-in')) {
        $('a:contains("Become a Vendor")').closest('li').hide();
    }


    /* ===========================
       PRODUCT FILTER TABS FIX
       =========================== */
    // Change "Out of stock" → "No stock"
    $('ul.dokan-listing-filter.subsubsub li a').each(function() {
        let text = $(this).text().trim();
        if (text.includes("Out of stock")) {
            $(this).text(text.replace("Out of stock", "No stock"));
        }
    });

});

// Add this to your existing custom.js file

jQuery(function($) {
    
    // ============ Kerala Listings Grid Functionality ============
    
    let currentFilters = {
        district: '',
        category: '',
        price_range: '',
        user_lat: 0,
        user_lng: 0,
        page: 1
    };
    
    // Initialize Kerala listings functionality
    if ($('#kerala-listings-container').length) {
        initKeralaListings();
    }
    
    function initKeralaListings() {
        
        // Filter change handlers
        $('#kerala_district, #kerala_category, #kerala_price_range').on('change', function() {
            updateFilters();
            loadListings(true);
        });
        
        // Near Me button
        $('#kerala_near_me').on('click', function() {
            getUserLocation();
        });
        
        // Load More button
        $('#kerala_load_more').on('click', function() {
            currentFilters.page++;
            loadListings(false);
        });
        
    }
    
    // Update current filters from form inputs
    function updateFilters() {
        currentFilters.district = $('#kerala_district').val();
        currentFilters.category = $('#kerala_category').val();
        currentFilters.price_range = $('#kerala_price_range').val();
        currentFilters.page = 1;
    }
    
    // Get user's GPS location
    function getUserLocation() {
        const $btn = $('#kerala_near_me');
        
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by this browser.');
            return;
        }
        
        $btn.text('Getting location...').prop('disabled', true);
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                currentFilters.user_lat = position.coords.latitude;
                currentFilters.user_lng = position.coords.longitude;
                currentFilters.page = 1;
                
                $btn.html('<i class="fa fa-location-arrow"></i> Near Me').prop('disabled', false);
                loadListings(true);
            },
            function(error) {
                console.error('Geolocation error:', error);
                $btn.html('<i class="fa fa-location-arrow"></i> Near Me').prop('disabled', false);
                
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        alert('Location access denied. Please enable location services.');
                        break;
                    case error.POSITION_UNAVAILABLE:
                        alert('Location information unavailable.');
                        break;
                    case error.TIMEOUT:
                        alert('Location request timed out.');
                        break;
                    default:
                        alert('An error occurred while retrieving location.');
                        break;
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000
            }
        );
    }
    
    // Load listings via AJAX
    function loadListings(replace = true) {
        const $container = $('#kerala-listings-grid');
        const $loading = $('#kerala-loading');
        const $loadMore = $('#kerala_load_more');
        
        if (replace) {
            $loading.show();
            $container.fadeOut(200);
        } else {
            $loadMore.text('Loading...').prop('disabled', true);
        }
        
        $.ajax({
            url: gtg_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'kerala_listings_filter',
                nonce: '<?php echo wp_create_nonce("kerala_listings_nonce"); ?>',
                district: currentFilters.district,
                category: currentFilters.category,
                price_range: currentFilters.price_range,
                user_lat: currentFilters.user_lat,
                user_lng: currentFilters.user_lng,
                page: currentFilters.page
            },
            success: function(response) {
                if (response.success) {
                    if (replace) {
                        $container.html(response.data.html);
                        $container.fadeIn(300);
                        $loading.hide();
                        
                        // Scroll to results on mobile
                        if ($(window).width() < 768) {
                            $('html, body').animate({
                                scrollTop: $container.offset().top - 100
                            }, 500);
                        }
                    } else {
                        $container.append(response.data.html);
                        $loadMore.text('Load More Deals').prop('disabled', false);
                    }
                    
                    // Update load more button state
                    if (response.data.found_posts <= currentFilters.page * 12) {
                        $loadMore.hide();
                    } else {
                        $loadMore.show();
                    }
                    
                    // Trigger scroll animations if any
                    $(window).trigger('scroll');
                    
                } else {
                    console.error('AJAX Error:', response);
                    if (replace) {
                        $loading.hide();
                        $container.fadeIn(300);
                    } else {
                        $loadMore.text('Load More Deals').prop('disabled', false);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Request Failed:', error);
                if (replace) {
                    $loading.hide();
                    $container.fadeIn(300);
                } else {
                    $loadMore.text('Load More Deals').prop('disabled', false);
                }
            }
        });
    }
    
    // Add touch feedback for mobile
    $(document).on('touchstart', '.kerala-listing-card', function() {
        $(this).addClass('touching');
    });
    
    $(document).on('touchend touchcancel', '.kerala-listing-card', function() {
        const $this = $(this);
        setTimeout(function() {
            $this.removeClass('touching');
        }, 150);
    });
    
});

// Add touch feedback CSS
const touchStyles = 
<style>
.kerala-listing-card.touching {
    transform: scale(0.98);
    transition: transform 0.1s ease;
}
</style>
;
$('head').append(touchStyles);