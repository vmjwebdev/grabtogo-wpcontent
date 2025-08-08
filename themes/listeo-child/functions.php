<?php
/**
 * GrabToGo optimized child theme functions.php
 */

// ============ Enqueue Styles & Scripts ============
function grabtogo_enqueue_assets() {
    wp_enqueue_script( 'jquery' );
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style(
        'grabtogo-fonts',
        'https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap',
        [],
        null
    );
    $css_path = get_stylesheet_directory() . '/assets/css/custom.css';
    if ( file_exists( $css_path ) ) {
        wp_enqueue_style(
            'grabtogo-custom-css',
            get_stylesheet_directory_uri() . '/assets/css/custom.css',
            [],
            filemtime( $css_path )
        );
    }
    $js_path = get_stylesheet_directory() . '/assets/js/custom.js';
    if ( file_exists( $js_path ) ) {
        wp_enqueue_script(
            'grabtogo-custom-js',
            get_stylesheet_directory_uri() . '/assets/js/custom.js',
            [ 'jquery' ],
            filemtime( $js_path ),
            true
        );
        wp_localize_script(
            'grabtogo-custom-js',
            'gtg_ajax',
            [ 'ajax_url' => admin_url( 'admin-ajax.php' ) ]
        );
    }
}
add_action( 'wp_enqueue_scripts', 'grabtogo_enqueue_assets' );

// ============ Redirect /my-account/ → /my-profile/ ============
function grabtogo_redirect_my_account() {
    if ( is_page( 'my-account' ) ) {
        wp_redirect( home_url( '/my-profile/' ) );
        exit;
    }
}
add_action( 'template_redirect', 'grabtogo_redirect_my_account' );

// ============ AJAX: Send OTP ============
function gtg_send_otp() {
    $email = sanitize_email( $_POST['email'] );
    if ( ! is_email( $email ) ) {
        wp_send_json_error( 'Invalid email.' );
    }
    $otp = rand( 100000, 999999 );
    set_transient( 'gtg_otp_' . md5( $email ), $otp, 10 * MINUTE_IN_SECONDS );
    wp_mail( $email, 'Your OTP for GrabToGo', "Your OTP is: $otp\n\nExpires in 10 minutes." );
    wp_send_json_success( 'OTP sent.' );
}
add_action( 'wp_ajax_gtg_send_otp', 'gtg_send_otp' );
add_action( 'wp_ajax_nopriv_gtg_send_otp', 'gtg_send_otp' );

// ============ AJAX: Verify OTP ============
function gtg_verify_otp() {
    $email   = sanitize_email( $_POST['email'] );
    $entered = sanitize_text_field( $_POST['otp'] );
    $stored  = get_transient( 'gtg_otp_' . md5( $email ) );
    if ( $entered == $stored ) {
        set_transient( 'gtg_otp_verified_' . md5( $email ), true, 30 * MINUTE_IN_SECONDS );
        wp_send_json_success( 'OTP verified.' );
    }
    wp_send_json_error( 'Incorrect OTP.' );
}
add_action( 'wp_ajax_gtg_verify_otp', 'gtg_verify_otp' );
add_action( 'wp_ajax_nopriv_gtg_verify_otp', 'gtg_verify_otp' );

// ============ Shortcode: Vendor Registration Form ============
function grabtogo_vendor_registration_form_shortcode() {
    ob_start(); ?>
    <form method="POST" enctype="multipart/form-data" id="gtg-reg-form" class="gtg-registration-form" style="max-width:500px;margin:auto;background:#fff;padding:30px;border-radius:12px;">
        <h3 style="text-align:center;">Vendor Registration</h3>
        <!-- Email & OTP -->
        <label>Email*</label>
        <input type="email" name="gtg_email" id="gtg_email" required>
        <button type="button" id="send_otp_btn">Send OTP</button>
        <div id="otp_section" style="display:none;">
            <label>Enter OTP*</label>
            <input type="text" id="gtg_otp_input">
            <button type="button" id="verify_otp_btn">Verify OTP</button>
        </div>
        <div id="otp_status"></div>
        <!-- Names -->
        <label>First Name*</label>
        <input type="text" name="gtg_first_name" id="gtg_first_name" required>
        <label>Last Name*</label>
        <input type="text" name="gtg_last_name" id="gtg_last_name" required>
        <!-- Password & Shop Details -->
        <label>Password*</label>
        <div class="gtg-password-container">
            <input type="password" name="gtg_password" required>
            <i class="fa-solid fa-eye gtg-toggle-password" title="Show/Hide Password"></i>
        </div>
        <label>Shop Name*</label>
        <input type="text" name="gtg_shop_name" required>
        <label>Owner Name*</label>
        <input type="text" name="gtg_owner_name" required>
        <!-- Address -->
        <label>Shop Address*</label>
        <input type="text" name="gtg_shop_address" required>
        <label>City*</label>
        <input type="text" name="gtg_shop_city" required>
        <!-- State -->
        <?php if ( class_exists( 'WC_Countries' ) ) :
            $states = ( new WC_Countries() )->get_states( 'IN' );
            woocommerce_form_field( 'gtg_shop_state', [
                'type'     => 'select',
                'label'    => __( 'State*', 'grabtogo' ),
                'required' => true,
                'options'  => array_merge( [ '' => __( 'Select a state', 'grabtogo' ) ], $states ),
            ], '' );
        endif; ?>
        <label>Postcode*</label>
        <input type="text" name="gtg_shop_postcode" required>
        <input type="hidden" name="gtg_country" value="IN">
        <!-- Contact & GST -->
        <label>Phone Number (WhatsApp)*</label>
        <input type="text" name="gtg_whatsapp" required>
        <label>GST Number*</label>
        <input type="text" name="gtg_gst_number" required>
        <label>Upload GST Document*</label>
        <input type="file" name="gtg_document" accept=".jpg,.jpeg,.png,.pdf" required>
        <!-- Referral -->
        <label>Agent Name (if referred)</label>
        <input type="text" name="gtg_agent_name">
        <!-- Submit -->
        <button type="submit" name="gtg_register_submit" id="gtg_submit_btn" disabled>
            Register
        </button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode( 'grabtogo_vendor_registration_form', 'grabtogo_vendor_registration_form_shortcode' );

// ============ Registration Handler ============
function grabtogo_handle_registration() {
    if ( ! isset( $_POST['gtg_register_submit'] ) ) {
        return;
    }

    $email = sanitize_email( $_POST['gtg_email'] );
    if ( ! get_transient( 'gtg_otp_verified_' . md5( $email ) ) ) {
        wp_die( 'Please verify your email with OTP first.' );
    }
    if ( email_exists( $email ) ) {
        wp_die( 'Email already exists.' );
    }

    $first_name  = sanitize_text_field( $_POST['gtg_first_name'] );
    $last_name   = sanitize_text_field( $_POST['gtg_last_name'] );
    $password    = $_POST['gtg_password'];
    $shop_name   = sanitize_text_field( $_POST['gtg_shop_name'] );
    $owner_name  = sanitize_text_field( $_POST['gtg_owner_name'] );
    $address     = sanitize_text_field( $_POST['gtg_shop_address'] );
    $city        = sanitize_text_field( $_POST['gtg_shop_city'] );
    $state       = sanitize_text_field( $_POST['gtg_shop_state'] );
    $postcode    = sanitize_text_field( $_POST['gtg_shop_postcode'] );
    $whatsapp    = sanitize_text_field( $_POST['gtg_whatsapp'] );
    $gst_number  = sanitize_text_field( $_POST['gtg_gst_number'] );
    $agent_name  = sanitize_text_field( $_POST['gtg_agent_name'] );

    $user_id = wp_create_user( $email, $password, $email );
    wp_update_user( [ 'ID' => $user_id, 'role' => 'customer' ] );

    wp_update_user( [
        'ID'           => $user_id,
        'display_name' => $shop_name,
        'first_name'   => $first_name,
        'last_name'    => $last_name,
    ] );

    $meta_map = [
        'gtg_shop_name'     => $shop_name,
        'gtg_owner_name'    => $owner_name,
        'gtg_shop_address'  => $address,
        'gtg_shop_city'     => $city,
        'gtg_shop_state'    => $state,
        'gtg_shop_postcode' => $postcode,
        'gtg_whatsapp'      => $whatsapp,
        'gtg_gst_number'    => $gst_number,
        'gtg_agent_name'    => $agent_name,
    ];
    foreach ( $meta_map as $key => $val ) {
        update_user_meta( $user_id, $key, $val );
    }

    $wc_map = [
        'billing_first_name' => $first_name,
        'billing_last_name'  => $last_name,
        'billing_address_1'  => $address,
        'billing_city'       => $city,
        'billing_state'      => $state,
        'billing_postcode'   => $postcode,
        'billing_country'    => 'IN',
        'billing_phone'      => $whatsapp,
        'billing_company'    => $shop_name,
    ];
    foreach ( $wc_map as $key => $val ) {
        update_user_meta( $user_id, $key, $val );
    }

    if ( ! function_exists( 'wp_handle_upload' ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    $move = wp_handle_upload( $_FILES['gtg_document'], [ 'test_form' => false ] );
    if ( isset( $move['file'] ) && ! isset( $move['error'] ) ) {
        $attach_id = wp_insert_attachment( [
            'post_mime_type' => $move['type'],
            'post_title'     => sanitize_file_name( $move['file'] ),
            'post_status'    => 'inherit',
        ], $move['file'] );
        require_once ABSPATH . 'wp-admin/includes/image.php';
        wp_update_attachment_metadata( $attach_id, wp_generate_attachment_metadata( $attach_id, $move['file'] ) );
        update_user_meta( $user_id, 'gtg_uploaded_document', $attach_id );
    }

    $slug = sanitize_title( $shop_name );
    update_user_meta( $user_id, 'dokan_store_name', $shop_name );
    update_user_meta( $user_id, 'store_name',       $shop_name );
    update_user_meta( $user_id, 'dokan_store_url',  $slug );
    update_user_meta( $user_id, 'dokan_profile_settings', [
        'store_name'  => $shop_name,
        'store_url'   => $slug,
        'phone'       => $whatsapp,
        'address'     => [
            'street_1' => $address,
            'city'     => $city,
            'zip'      => $postcode,
            'state'    => $state,
            'country'  => 'IN',
        ],
        'document_id' => $attach_id ?? '',
        'gst_number'  => $gst_number,
    ] );

    wp_mail(
        'info@grabtogo.in',
        'New Vendor Registration',
        "Shop: $shop_name<br>Owner: $owner_name<br>Email: $email<br>Phone: $whatsapp<br>Address: $address, $city, $state, $postcode<br>GST: $gst_number<br>Agent: $agent_name",
        [ 'Content-Type: text/html; charset=UTF-8' ],
        isset($move['file']) ? [ $move['file'] ] : []
    );
    
    wp_mail(
        $email,
        'Thanks for registering with GrabToGo',
        "Hi $owner_name,
    
        Thanks for registering your shop '$shop_name' 
        with GrabToGo! We’ll review your document shortly 
        and email you when you can continue onboarding.
    
        Here are your login details:
        Email: $email
        Password: $password
    
        Login here: https://grabtogo.in/my-profile/
    
        – Team GrabToGo"
    );
    
    

    wp_redirect( home_url( '/thank-you-registration/' ) );
    exit;
}
add_action( 'template_redirect', 'grabtogo_handle_registration' );

// ============ WooCommerce Checkout Tweaks ============
function grabtogo_checkout_fields_tweak( $fields ) {
    unset( $fields['billing']['billing_last_name'] );
    if ( isset( $fields['billing']['billing_state'] ) ) {
        $fields['billing']['billing_state']['required'] = false;
    }
    return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'grabtogo_checkout_fields_tweak' );

// ============ Auto-fill Listing Title ============
function grabtogo_autofill_listing_title_script() {
    if ( ! is_page( 'add-listing' ) ) {
        return;
    }
    $store = get_user_meta( get_current_user_id(), 'dokan_store_name', true );
    if ( $store ) {
        ?>
        <script>
        jQuery(function($){
            $('input[name="listing_title"]').val('<?php echo esc_js( $store ); ?>').prop('readonly',true);
        });
        </script>
        <?php
    }
}
add_action( 'wp_footer', 'grabtogo_autofill_listing_title_script' );

// ============ Auto-fill Other Listing Fields ============
function grabtogo_get_vendor_active_listing_data( $user_id ) {
    $q = new WP_Query( [
        'post_type'      => 'listing',
        'post_status'    => 'publish',
        'author'         => $user_id,
        'posts_per_page' => 1,
    ] );
    if ( $q->have_posts() ) {
        $p = $q->posts[0];
        return [
            '_job_logo'         => get_post_meta( $p->ID, '_job_logo', true ),
            '_phone'            => get_post_meta( $p->ID, '_phone', true ),
            '_email'            => get_post_meta( $p->ID, '_email', true ),
            '_website'          => get_post_meta( $p->ID, '_website', true ),
            '_facebook'         => get_post_meta( $p->ID, '_facebook', true ),
            '_instagram'        => get_post_meta( $p->ID, '_instagram', true ),
            '_whatsapp'         => get_post_meta( $p->ID, '_whatsapp', true ),
            '_job_location'     => get_post_meta( $p->ID, '_job_location', true ),
            'geolocation_lat'   => get_post_meta( $p->ID, 'geolocation_lat', true ),
            'geolocation_long'  => get_post_meta( $p->ID, 'geolocation_long', true ),
            '_job_address'      => get_post_meta( $p->ID, '_job_address', true ),
            '_friendly_address' => get_post_meta( $p->ID, '_friendly_address', true ),
            '_region'           => get_post_meta( $p->ID, '_region', true ),
        ];
    }
    return [];
}
function grabtogo_autofill_listing_fields_script() {
    if ( ! is_page( 'add-listing' ) ) {
        return;
    }
    $data = grabtogo_get_vendor_active_listing_data( get_current_user_id() );
    if ( $data ) {
        ?>
        <script>
        jQuery(function($){
            <?php foreach ( $data as $field => $val ) : ?>
            <?php if ( $val ) : ?>
                $('input[name="<?php echo esc_js( $field ); ?>"]').val('<?php echo esc_js( $val ); ?>');
            <?php endif; ?>
            <?php endforeach; ?>
        });
        </script>
        <?php
    }
}
add_action( 'wp_footer', 'grabtogo_autofill_listing_fields_script' );

// ===== Fix: Default WP and Listeo registration should create 'customer' role instead of 'subscriber' =====
add_filter('registration_defaults', function($defaults) {
    $defaults['role'] = 'customer';
    return $defaults;
});


// ============ Zoho Flow Webhook Integration ============
add_action( 'set_user_role', 'gtg_send_to_zoho_flow', 20, 3 );
function gtg_send_to_zoho_flow( $user_id, $new_role, $old_roles ) {
    // Only run when role becomes 'seller'
    if ( 'seller' !== $new_role ) {
        return;
    }

    // Load user object
    $user = get_userdata( $user_id );
    if ( ! $user ) {
        return;
    }

    // Collect and sanitize the data from usermeta (registration form fields)
    $first_name     = sanitize_text_field( get_user_meta( $user_id, 'first_name', true ) );
    $last_name      = sanitize_text_field( get_user_meta( $user_id, 'last_name', true ) );
    $shop_name      = sanitize_text_field( get_user_meta( $user_id, 'gtg_shop_name', true ) );  // Company Name/Shop Name
    $gst_number     = sanitize_text_field( get_user_meta( $user_id, 'gtg_gst_number', true ) ); // GST Number
    $email          = sanitize_email( $user->user_email );  // Email
    $address        = sanitize_text_field( get_user_meta( $user_id, 'gtg_shop_address', true ) ); // Address
    $agent_name     = sanitize_text_field( get_user_meta( $user_id, 'gtg_agent_name', true ) );  // Agent Name
    $owner_name     = sanitize_text_field( get_user_meta( $user_id, 'gtg_owner_name', true ) );  // Owner's Name
    $billing_phone  = sanitize_text_field( get_user_meta( $user_id, 'billing_phone', true ) );  // Phone
    $billing_address = sanitize_text_field( get_user_meta( $user_id, 'billing_address_1', true ) );  // Address
    $postcode       = sanitize_text_field( get_user_meta( $user_id, 'gtg_shop_postcode', true ) ); // Postcode/Zipcode
    $city           = sanitize_text_field( get_user_meta( $user_id, 'gtg_shop_city', true ) );   // City

    // GST Document (File)
    $gst_document_id = get_user_meta( $user_id, 'gtg_uploaded_document', true ); // Get document ID
    $gst_document_url = wp_get_attachment_url( $gst_document_id ); // Get the URL of the uploaded document

    // Build the payload
    $payload = [
        'first_name'        => $first_name,
        'last_name'         => $last_name,
        'email'             => $email,
        'company_name'      => $shop_name,  // Company Name/Shop Name
        'gst_number'        => $gst_number, // GST Number
        'address'           => $address,    // Address
        'agent_name'        => $agent_name, // Agent Name
        'owner_name'        => $owner_name, // Owner's Name
        'billing_phone'     => $billing_phone,  // Phone
        'billing_address_1' => $billing_address,  // Billing Address
        'postcode'          => $postcode, // Postcode/Zipcode
        'city'              => $city,    // City
        'gst_document'      => $gst_document_url,  // URL of the uploaded GST Document
    ];

    // Send to Zoho Flow webhook
    wp_remote_post(
        'https://flow.zoho.in/60044344737/flow/webhook/incoming?zapikey=1001.b6b5af0e06537f5d9d0204cd63e322e1.2747887e0b408ef7125e4721defdc778&isdebug=false',
        [
            'headers' => [ 'Content-Type' => 'application/json' ],
            'body'    => wp_json_encode( $payload ),
            'timeout' => 20,
        ]
    );
}