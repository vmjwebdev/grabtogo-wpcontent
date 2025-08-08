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

// ============ KERALA LISTINGS GRID SHORTCODE ============
// Add this to your existing functions.php (don't duplicate the opening PHP tag)

// ============ Kerala Listings Grid Shortcode ============
function kerala_listings_grid_shortcode($atts) {
    $atts = shortcode_atts([
        'columns' => '2',
        'posts_per_page' => '12',
        'location_filter' => 'true',
        'category_filter' => 'true',
        'price_filter' => 'true',
        'style' => 'kaufda',
        'districts' => 'Ernakulam,Thrissur,Kottayam'
    ], $atts, 'kerala_listings_grid');

    ob_start();
    ?>
    <div id="kerala-listings-container" class="kerala-listings-wrapper">
        
        <!-- Filters Section -->
        <?php if ($atts['location_filter'] == 'true' || $atts['category_filter'] == 'true') : ?>
        <div class="kerala-filters-section">
            <div class="filters-row">
                
                <?php if ($atts['location_filter'] == 'true') : ?>
                <div class="filter-group location-filter">
                    <select name="kerala_district" id="kerala_district">
                        <option value=""><?php esc_html_e('All Kerala', 'grabtogo'); ?></option>
                        <?php 
                        $districts = explode(',', $atts['districts']);
                        foreach ($districts as $district) : ?>
                            <option value="<?php echo esc_attr(trim($district)); ?>">
                                <?php echo esc_html(trim($district)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group gps-filter">
                    <button type="button" id="kerala_near_me" class="near-me-btn">
                        <i class="fa fa-location-arrow"></i>
                        <?php esc_html_e('Near Me', 'grabtogo'); ?>
                    </button>
                </div>
                <?php endif; ?>

                <?php if ($atts['category_filter'] == 'true') : ?>
                <div class="filter-group category-filter">
                    <select name="kerala_category" id="kerala_category">
                        <option value=""><?php esc_html_e('All Categories', 'grabtogo'); ?></option>
                        <?php 
                        $categories = get_terms([
                            'taxonomy' => 'listing_category',
                            'hide_empty' => true
                        ]);
                        foreach ($categories as $cat) : ?>
                            <option value="<?php echo esc_attr($cat->slug); ?>">
                                <?php echo esc_html($cat->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <?php if ($atts['price_filter'] == 'true') : ?>
                <div class="filter-group price-filter">
                    <select name="kerala_price_range" id="kerala_price_range">
                        <option value=""><?php esc_html_e('Any Price', 'grabtogo'); ?></option>
                        <option value="0-500">₹0 - ₹500</option>
                        <option value="500-1000">₹500 - ₹1,000</option>
                        <option value="1000-2000">₹1,000 - ₹2,000</option>
                        <option value="2000+">₹2,000+</option>
                    </select>
                </div>
                <?php endif; ?>

            </div>
        </div>
        <?php endif; ?>

        <!-- Loading State -->
        <div id="kerala-loading" style="display:none;">
            <div class="loading-spinner">
                <div class="spinner"></div>
                <p><?php esc_html_e('Finding best deals...', 'grabtogo'); ?></p>
            </div>
        </div>

        <!-- Results Grid -->
        <div id="kerala-listings-grid" class="kerala-grid" data-columns="<?php echo esc_attr($atts['columns']); ?>">
            <?php echo kerala_get_listings_html($atts); ?>
        </div>

        <!-- Load More Button -->
        <div class="kerala-load-more-container">
            <button type="button" id="kerala_load_more" class="load-more-btn" data-page="1" data-max-pages="1">
                <?php esc_html_e('Load More Deals', 'grabtogo'); ?>
            </button>
        </div>

    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('kerala_listings_grid', 'kerala_listings_grid_shortcode');

// ============ Get Listings HTML Function ============
function kerala_get_listings_html($atts = [], $filters = []) {
    $args = [
        'post_type' => 'listing',
        'post_status' => 'publish',
        'posts_per_page' => intval($atts['posts_per_page'] ?? 12),
        'paged' => intval($filters['page'] ?? 1),
        'meta_query' => [
            'relation' => 'AND'
        ]
    ];

    // Location filtering
    if (!empty($filters['district'])) {
        $args['meta_query'][] = [
            'relation' => 'OR',
            [
                'key' => '_friendly_address',
                'value' => $filters['district'],
                'compare' => 'LIKE'
            ],
            [
                'key' => '_address',
                'value' => $filters['district'],
                'compare' => 'LIKE'
            ]
        ];
    }

    // Category filtering
    if (!empty($filters['category'])) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'listing_category',
                'field' => 'slug',
                'terms' => $filters['category']
            ]
        ];
    }

    // Price filtering
    if (!empty($filters['price_range'])) {
        if ($filters['price_range'] === '2000+') {
            $args['meta_query'][] = [
                'key' => '_price_min',
                'value' => 2000,
                'type' => 'NUMERIC',
                'compare' => '>='
            ];
        } else {
            $range = explode('-', $filters['price_range']);
            if (count($range) === 2) {
                $args['meta_query'][] = [
                    'relation' => 'AND',
                    [
                        'key' => '_price_min',
                        'value' => intval($range[0]),
                        'type' => 'NUMERIC',
                        'compare' => '>='
                    ],
                    [
                        'key' => '_price_max',
                        'value' => intval($range[1]),
                        'type' => 'NUMERIC',
                        'compare' => '<='
                    ]
                ];
            }
        }
    }

    // GPS proximity sorting
    if (!empty($filters['user_lat']) && !empty($filters['user_lng'])) {
        $args['meta_query'][] = [
            'relation' => 'AND',
            [
                'key' => '_geolocation_lat',
                'compare' => 'EXISTS'
            ],
            [
                'key' => '_geolocation_long',
                'compare' => 'EXISTS'
            ]
        ];
        // Add custom orderby for distance (simplified)
        $args['orderby'] = 'meta_value_num';
        $args['meta_key'] = '_geolocation_lat';
    }

    $query = new WP_Query($args);
    
    ob_start();
    
    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();
            kerala_render_listing_card(get_post());
        endwhile;
        wp_reset_postdata();
    else :
        ?>
        <div class="no-listings-found">
            <div class="no-results-content">
                <i class="fa fa-search"></i>
                <h3><?php esc_html_e('No businesses found', 'grabtogo'); ?></h3>
                <p><?php esc_html_e('Try adjusting your filters or explore all Kerala', 'grabtogo'); ?></p>
            </div>
        </div>
        <?php
    endif;

    return ob_get_clean();
}

// ============ Render Individual Listing Card ============
function kerala_render_listing_card($post) {
    $post_id = $post->ID;
    
   // Prefer the listing Featured Image (Listeo widget uses this)
$featured_image = '';
if (has_post_thumbnail($post_id)) {
    // same size Listeo uses in the widget
    $featured_image = get_the_post_thumbnail_url($post_id, 'full');
    if (!$featured_image) { // fallback size if grid size not registered
        $featured_image = get_the_post_thumbnail_url($post_id, 'large');
    }
}

// If no featured image, pull first item from _gallery
if (empty($featured_image)) {
    $gallery = get_post_meta($post_id, '_gallery', true);
    if ($gallery) {
        $gallery_array = maybe_unserialize($gallery);
        if (is_array($gallery_array) && !empty($gallery_array)) {
            $first = reset($gallery_array);
            if (is_numeric($first)) {
                // gallery sometimes stores attachment IDs
                $featured_image = wp_get_attachment_image_url((int)$first, 'full');
                if (!$featured_image) {
                    $featured_image = wp_get_attachment_image_url((int)$first, 'large');
                }
            } elseif (is_string($first)) {
                // gallery stores full URL; force https to avoid mixed-content blanks
                $featured_image = preg_replace('#^http://#', 'https://', $first);
            }
        }
    }
}

    
    // Default placeholder
    if (empty($featured_image)) {
        $featured_image = get_template_directory_uri() . '/images/placeholder-listing.jpg';
    }

    // Get location
    $location = get_post_meta($post_id, '_friendly_address', true);
    if (empty($location)) {
        $location = get_post_meta($post_id, '_address', true);
    }

    // Get rating
    $rating = get_post_meta($post_id, 'listeo-avg-rating', true);
    $rating_display = ($rating && $rating > 0) ? number_format($rating, 1) : 'New';

    // Get price range
    $price_min = get_post_meta($post_id, '_price_min', true);
    $price_max = get_post_meta($post_id, '_price_max', true);
    $price_display = '';
    if ($price_min && $price_max) {
        $price_display = "₹{$price_min} - ₹{$price_max}";
    } elseif ($price_min) {
        $price_display = "From ₹{$price_min}";
    }

    ?>
    <div class="kerala-listing-card" data-id="<?php echo esc_attr($post_id); ?>">
        <a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="card-link">
            
            <!-- Card Image -->
            <div class="card-image-container">
                <img src="<?php echo esc_url($featured_image); ?>" 
                     alt="<?php echo esc_attr(get_the_title()); ?>" 
                     class="card-image"
                     loading="lazy">
                
                <!-- Rating Badge -->
                <div class="rating-badge">
                    <?php if ($rating && $rating > 0) : ?>
                        <i class="fa fa-star"></i>
                        <span><?php echo esc_html($rating_display); ?></span>
                    <?php else : ?>
                        <span class="new-badge"><?php esc_html_e('New', 'grabtogo'); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Card Content -->
            <div class="card-content">
                <h3 class="business-name"><?php echo esc_html(get_the_title()); ?></h3>
                
                <?php if ($location) : ?>
                <div class="business-location">
                    <i class="fa fa-map-marker"></i>
                    <span><?php echo esc_html($location); ?></span>
                </div>
                <?php endif; ?>

                <?php if ($price_display) : ?>
                <div class="business-price">
                    <span class="price-text"><?php echo esc_html($price_display); ?></span>
                </div>
                <?php endif; ?>
                
            </div>

        </a>
    </div>
    <?php
}

// ============ AJAX Handler for Filtering ============
function kerala_listings_ajax_filter() {
    check_ajax_referer('kerala_listings_nonce', 'nonce');
    
    $filters = [
        'district' => sanitize_text_field($_POST['district'] ?? ''),
        'category' => sanitize_text_field($_POST['category'] ?? ''),
        'price_range' => sanitize_text_field($_POST['price_range'] ?? ''),
        'user_lat' => floatval($_POST['user_lat'] ?? 0),
        'user_lng' => floatval($_POST['user_lng'] ?? 0),
        'page' => intval($_POST['page'] ?? 1)
    ];
    
    $atts = [
        'posts_per_page' => 12,
        'columns' => 2
    ];
    
    $html = kerala_get_listings_html($atts, $filters);
    
    wp_send_json_success([
        'html' => $html,
        'found_posts' => $GLOBALS['wp_query']->found_posts ?? 0
    ]);
}
add_action('wp_ajax_kerala_listings_filter', 'kerala_listings_ajax_filter');
add_action('wp_ajax_nopriv_kerala_listings_filter', 'kerala_listings_ajax_filter');