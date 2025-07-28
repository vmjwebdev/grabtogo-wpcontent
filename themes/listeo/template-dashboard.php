<?php

/**
 * Template Name: Dashboard Page
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package Listeo
 */

if (!is_user_logged_in()) {

	$errors = array();

	if (isset($_REQUEST['login'])) {
		$error_codes = explode(',', $_REQUEST['login']);

		foreach ($error_codes as $code) {
			switch ($code) {
				case 'empty_username':
					$errors[] = esc_html__('You do have an email address, right?', 'listeo');
					break;
				case 'empty_password':
					$errors[] =  esc_html__('You need to enter a password to login.', 'listeo');
					break;
				case 'username_exists':
					$errors[] =  esc_html__('This username already exists.', 'listeo');
					break;
				case 'authentication_failed':
				case 'invalid_email':
				case 'invalid_username':
					$errors[] =  esc_html__(
						"We don't have any users with that email address. Maybe you used a different one when signing up?",
						'listeo'
					);
					break;
				case 'incorrect_password':
					$err = __(
						"The password you entered wasn't quite right. <a href='%s'>Did you forget your password</a>?",
						'listeo'
					);
					$errors[] =  sprintf($err, wp_lostpassword_url());
					break;
				default:
					break;
			}
		}
	}
	// Retrieve possible errors from request parameters
	if (isset($_REQUEST['register-errors'])) {
		$error_codes = explode(',', $_REQUEST['register-errors']);

		foreach ($error_codes as $error_code) {

			switch ($error_code) {
				case 'email':
					$errors[] = esc_html__('The email address you entered is not valid.', 'listeo');
					break;
				case 'gmail-only':
					$errors[] = esc_html__('Please use address from gmail.', 'listeo');
					break;
				case 'email_exists':
					$errors[] = esc_html__('An account exists with this email address.', 'listeo');
					break;
				case 'closed':
					$errors[] = esc_html__('Registering new users is currently not allowed.', 'listeo');
					break;
				case 'captcha-no':
					$errors[] = esc_html__('Please check reCAPTCHA checbox to register.', 'listeo');
					break;
				case 'required-field':
					$errors[] = esc_html__('You have missed required field', 'listeo');
					break;
				case 'username_exists':
					$errors[] =  esc_html__('This username already exists.', 'listeo');
					break;
				case 'captcha-fail':
					$errors[] = esc_html__("You're a bot, aren't you?.", 'listeo');
					break;
				case 'policy-fail':
					$errors[] = esc_html__("Please accept the Privacy Policy to register account.", 'listeo');
					break;
				case 'terms-fail':
					$errors[] = esc_html__("Please accept the Terms and Conditions to register account.", 'listeo');
					break;
				case 'otp-fail':
					$errors[] = esc_html__("Please enter the correct OTP to register account.", 'listeo');
					break;
				case 'first_name':
					$errors[] = esc_html__("Please provide your first name", 'listeo');
					break;
				case 'last_name':
					$errors[] = esc_html__("Please provide your last name", 'listeo');
					break;
				case 'empty_user_login':
					$errors[] = esc_html__("Please provide your user login", 'listeo');
					break;
				case 'password-no':
					$errors[] = esc_html__("You have forgot about password.", 'listeo_core', 'listeo');
					break;
				case 'strong_password':
					$errors[] = esc_html__("Your password is not strong enough.", 'listeo_core', 'listeo');
					break;
				case 'incorrect_password':
					$err = __(
						"The password you entered wasn't quite right. <a href='%s'>Did you forget your password</a>?",
						'listeo'
					);
					$errors[] =  sprintf($err, wp_lostpassword_url());
					break;
				default:
					break;
			}
		}
	}
	get_header();

	$page_top = get_post_meta($post->ID, 'listeo_page_top', TRUE);

	switch ($page_top) {
		case 'titlebar':
			get_template_part('template-parts/header', 'titlebar');
			break;

		case 'parallax':
			get_template_part('template-parts/header', 'parallax');
			break;

		case 'off':

			break;

		default:
			get_template_part('template-parts/header', 'titlebar');
			break;
	}

	$layout = get_post_meta($post->ID, 'listeo_page_layout', true);
	if (empty($layout)) {
		$layout = 'right-sidebar';
	}
	$class  = ($layout != "full-width") ? "col-lg-9 col-md-8 padding-right-30" : "col-md-12"; ?>
	<div class="container <?php echo esc_attr($layout); ?>">

		<div class="row">

			<article id="post-<?php the_ID(); ?>" <?php post_class($class); ?>>
				<div class="col-lg-5 col-md-4 col-md-offset-3 sign-in-form style-1 margin-bottom-45">
					<?php if (count($errors) > 0) : ?>
						<?php foreach ($errors  as $error) : ?>
							<div class="notification error closeable">
								<p><?php echo ($error); ?></p>
								<a class="close"></a>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
					<?php if (isset($_REQUEST['registered'])) : ?>
						<div class="notification success closeable">
							<p>
								<?php
								$password_field = get_option('listeo_display_password_field');
								if ($password_field) {
									printf(
										esc_html__('You have successfully registered to %s.', 'listeo'),
										get_bloginfo('name')
									);
								} else {
									printf(
										esc_html__('You have successfully registered to %s. We have emailed your password to the email address you entered.', 'listeo'),
										get_bloginfo('name')
									);
								}

								?>
							</p>
						</div>
					<?php endif; ?>
					<?php do_action('listeo_login_form');	 ?>
				</div>
			</article>

			<?php if ($layout != "full-width") { ?>
				<div class="col-lg-3 col-md-4">
					<div class="sidebar right">
						<?php get_sidebar(); ?>
					</div>
				</div>
			<?php } ?>

		</div>

	</div>
	<div class="clearfix"></div>
<?php
	get_footer();
} else { //is logged

	get_header('dashboard');
	$current_user = wp_get_current_user();
	$user_id = get_current_user_id();
	$roles = $current_user->roles;
	$role = array_shift($roles);

?>

	<!-- Dashboard -->
	<div id="dashboard">

		<!-- Navigation
	================================================== -->

		<!-- Responsive Navigation Trigger -->
		<a href="#" class="dashboard-responsive-nav-trigger"><i class="fa fa-reorder"></i> <?php esc_html_e('Dashboard Navigation', 'listeo'); ?></a>

		<div class="dashboard-nav">
			<div class="dashboard-nav-inner">
				<?php do_action('listeo/dashboard-menu/start'); ?>
				<ul data-submenu-title="<?php esc_html_e('Main', 'listeo'); ?>">

					<?php if (in_array($role, array('administrator', 'admin', 'owner', 'seller', 'vendor_staff'))) : ?>
						<?php $dashboard_page = get_option('listeo_dashboard_page');
						if ($dashboard_page) : ?>
							<li <?php if ($post->ID == $dashboard_page) : ?>class="active" <?php endif; ?>><a href="<?php echo esc_url(get_permalink($dashboard_page)); ?>"><i class="sl sl-icon-settings"></i> <?php esc_html_e('Dashboard', 'listeo'); ?></a></li>
						<?php endif; ?>
					<?php endif; ?>

					<?php
					$user_bookings_page = get_option('listeo_user_bookings_page');
					if (get_option('listeo_owners_can_book')) {

						if ($user_bookings_page) : ?>
							<li <?php if ($post->ID == $user_bookings_page) : ?>class="active" <?php endif; ?>><a href="<?php echo esc_url(get_permalink($user_bookings_page)); ?>"><i class="fa fa-calendar-check"></i> <?php esc_html_e('My Bookings', 'listeo'); ?></a></li>
						<?php endif;
					} else {
						if (!in_array($role, array('owner', 'seller', 'vendor_staff'))) : ?>
							<?php if ($user_bookings_page) : ?>
								<li <?php if ($post->ID == $user_bookings_page) : ?>class="active" <?php endif; ?>><a href="<?php echo esc_url(get_permalink($user_bookings_page)); ?>"><i class="fa fa-calendar-check"></i> <?php esc_html_e('My Bookings', 'listeo'); ?></a></li>
							<?php endif; ?>
					<?php endif;
					} ?>


					<?php $messages_page = get_option('listeo_messages_page');
					if ($messages_page) : ?>
						<li <?php if ($post->ID == $messages_page) : ?>class="active" <?php endif; ?>><a href="<?php echo esc_url(get_permalink($messages_page)); ?>"><i class="sl sl-icon-envelope-open"></i> <?php esc_html_e('Messages', 'listeo'); ?>
								<?php
								$counter = listeo_get_unread_counter();
								if ($counter) { ?>
									<span class="nav-tag messages"><?php echo esc_html($counter); ?></span>
								<?php } ?>
							</a>
						</li>
					<?php endif; ?>

					<?php if (in_array($role, array('administrator', 'admin', 'owner', 'seller', 'vendor_staff'))) : ?>
						<?php
						$bookings_page = get_option('listeo_bookings_page');
						$bookings_calendar_page = get_option('listeo_bookings_calendar_page');
						$qr_page = get_option('listeo_ticket_check_page');

						if ($bookings_page) : ?>
							<li <?php if ($post->ID == $bookings_page || $post->ID == $bookings_calendar_page || $post->ID == $qr_page) : ?>class="active" <?php endif; ?>><a><i class="fa fa-calendar-check"></i> <?php esc_html_e('Bookings', 'listeo'); ?></a>
								<ul>
									<?php
									if ($bookings_calendar_page) : ?>
										<li <?php if ($post->ID == $bookings_calendar_page) : ?>class="active" <?php endif; ?>><a href="<?php echo esc_url(get_permalink($bookings_calendar_page)); ?>"><?php esc_html_e('Calendar View', 'listeo'); ?>

											</a>
										</li>
									<?php endif; ?>
									<?php if (in_array($role, array('administrator', 'admin', 'owner', 'seller', 'vendor_staff'))) : ?>
										<?php 
										if ($qr_page) : ?>
											<li <?php if ($post->ID == $qr_page) : ?>class="active" <?php endif; ?>><a href="<?php echo esc_url(get_permalink($qr_page)); ?>"><?php esc_html_e('QR Scan', 'listeo'); ?></a></li>
										<?php endif; ?>
									<?php endif; ?>
									<li>
										<a href="<?php echo esc_url(get_permalink($bookings_page)); ?>?status=waiting"><?php esc_html_e('Pending', 'listeo'); ?>
											<?php
											$count_pending = listeo_count_bookings($user_id, 'waiting');
											if (isset($count_pending)) : ?><span class="nav-tag blue"><?php echo esc_html($count_pending); ?></span><?php endif; ?>
										</a>
									</li>
									<li>
										<a href="<?php echo esc_url(get_permalink($bookings_page)); ?>?status=approved"><?php esc_html_e('Approved', 'listeo'); ?>
											<?php
											$count_approved = listeo_count_bookings($user_id, 'approved');
											if (isset($count_approved)) : ?><span class="nav-tag green"><?php echo esc_html($count_approved); ?></span><?php endif; ?>
										</a>
									</li>
									<li>
										<a href="<?php echo esc_url(get_permalink($bookings_page)); ?>?status=cancelled"><?php esc_html_e('Cancelled', 'listeo'); ?>
											<?php
											$count_cancelled = listeo_count_bookings($user_id, 'cancelled');
											if (isset($count_cancelled)) : ?><span class="nav-tag red"><?php echo esc_html($count_cancelled); ?></span><?php endif; ?>
										</a>
									</li>
									<?php if (get_option('listeo_show_expired')) : ?>
										<li>
											<a href="<?php echo esc_url(get_permalink($bookings_page)); ?>?status=expired"><?php esc_html_e('Expired', 'listeo'); ?>
												<?php
												$count_cancelled = listeo_count_bookings($user_id, 'expired');
												if (isset($count_cancelled)) : ?><span class="nav-tag red"><?php echo esc_html($count_cancelled); ?></span><?php endif; ?>
											</a>
										</li>
									<?php endif; ?>



								</ul>

							</li>
						<?php endif; ?>
					<?php endif; ?>
					<?php if (in_array($role, array('administrator', 'admin', 'owner', 'seller', 'vendor_staff'))) : ?>
						<?php $wallet_page = get_option('listeo_wallet_page');
						if ($wallet_page) : ?>
							<li <?php if ($post->ID == $wallet_page) : ?>class="active" <?php endif; ?>><a href="<?php echo esc_url(get_permalink($wallet_page)); ?>"><i class="sl sl-icon-wallet"></i> <?php esc_html_e('Wallet', 'listeo'); ?></a>
							</li>
						<?php endif; ?>
					<?php endif; ?>
					<?php wp_nav_menu(array('theme_location' => 'dashboard_main', 'menu_id' => 'dashboard_main', 'container' => false, 'items_wrap' => '%3$s', 'fallback_cb' => false)); ?>
					<?php
					if (in_array($role, array('administrator', 'admin', 'guest'))) :
						wp_nav_menu(array('theme_location' => 'dashboard_main_guest', 'menu_id' => 'dashboard_main_guest', 'container' => false, 'items_wrap' => '%3$s', 'fallback_cb' => false));
					endif; ?>
					<?php
					if (in_array($role, array('administrator', 'admin', 'owner', 'seller', 'vendor_staff'))) :
						wp_nav_menu(array('theme_location' => 'dashboard_main_owner', 'menu_id' => 'dashboard_main_owner', 'container' => false, 'items_wrap' => '%3$s', 'fallback_cb' => false));
					endif; ?>
				</ul>

				<ul data-submenu-title="<?php esc_html_e('Listings', 'listeo'); ?>">
					<?php if (in_array($role, array('administrator', 'admin', 'owner', 'seller', 'vendor_staff'))) : ?>
						<?php $submit_page = get_option('listeo_submit_page');
						if ($submit_page) : ?>
							<li <?php if ($post->ID == $submit_page) : ?>class="active" <?php endif; ?>><a href="<?php echo esc_url(get_permalink($submit_page)); ?>"><i class="sl sl-icon-plus"></i> <?php esc_html_e('Add Listing', 'listeo'); ?></a></li>
						<?php endif; ?>

						<?php $listings_page = get_option('listeo_listings_page');
						if ($listings_page) : ?>
							<li <?php if ($post->ID == $listings_page) : ?>class="active" <?php endif; ?>><a><i class="sl sl-icon-layers"></i> <?php esc_html_e('My Listings', 'listeo'); ?></a>

								<ul>
									<li>
										<a href="<?php echo esc_url(get_permalink($listings_page)); ?>?status=active"><?php esc_html_e('Active', 'listeo'); ?>
											<?php
											$count_published =  listeo_count_posts_by_user($user_id, 'listing', 'publish');
											if (isset($count_published)) : ?><span class="nav-tag green"><?php echo esc_html($count_published); ?></span><?php endif; ?>
										</a>
									</li>
									<li>
										<a href="<?php echo esc_url(get_permalink($listings_page)); ?>?status=pending"><?php esc_html_e('Pending', 'listeo'); ?>
											<?php
											$count_pending =  listeo_count_posts_by_user($user_id, 'listing', 'pending');
											$count_pending_payment =  listeo_count_posts_by_user($user_id, 'listing', 'pending_payment');
											$count_draft =  listeo_count_posts_by_user($user_id, 'listing', 'draft');
											$total_pending_count = $count_pending + $count_pending_payment + $count_draft;
											if ($total_pending_count) : ?><span class="nav-tag blue"><?php echo esc_html($total_pending_count); ?></span><?php endif; ?>
										</a>
									</li>
									<li>
										<a href="<?php echo esc_url(get_permalink($listings_page)); ?>?status=expired">
											<?php esc_html_e('Expired', 'listeo'); ?>
											<?php
											$count_expired =  listeo_count_posts_by_user($user_id, 'listing', 'expired');
											if ($count_expired) : ?><span class="nav-tag red"><?php echo esc_html($count_expired) ?></span><?php endif; ?>
										</a>
									</li>

								</ul>
							</li>
						<?php endif; ?>
					<?php endif; ?>
					<?php if (in_array($role, array('administrator', 'admin', 'owner', 'seller', 'vendor_staff'))) : ?>
						<?php $stats_page = get_option('listeo_stats_page');
						if ($stats_page) : ?>
							<li <?php if ($post->ID == $stats_page) : ?>class="active" <?php endif; ?>><a href="<?php echo esc_url(get_permalink($stats_page)); ?>"><i class="sl sl-icon-chart"></i> <?php esc_html_e('Statistics', 'listeo'); ?></a></li>
						<?php endif; ?>
					<?php endif; ?>
					<?php if (in_array($role, array('administrator', 'admin', 'owner', 'seller', 'vendor_staff'))) : ?>
						<?php $ads_page = get_option('listeo_ad_campaigns_page');
						if ($ads_page) : ?>
							<li <?php if ($post->ID == $ads_page) : ?>class="active" <?php endif; ?>><a href="<?php echo esc_url(get_permalink($ads_page)); ?>"><i class="sl sl-icon-rocket"></i> <?php esc_html_e('Ad Campaign', 'listeo'); ?></a></li>
						<?php endif; ?>
					<?php endif; ?>
					<?php if (in_array($role, array('administrator', 'admin', 'owner', 'seller', 'vendor_staff'))) : ?>
						<?php $coupons_page = get_option('listeo_coupons_page');
						if ($coupons_page) : ?>
							<li <?php if ($post->ID == $coupons_page) : ?>class="active" <?php endif; ?>><a href="<?php echo esc_url(get_permalink($coupons_page)); ?>"><i class="sl sl-icon-credit-card"></i> <?php esc_html_e('Coupons', 'listeo'); ?></a></li>
						<?php endif; ?>
					<?php endif; ?>
					<?php $reviews_page = get_option('listeo_reviews_page');
					if ($reviews_page) : ?>
						<li <?php if ($post->ID == $reviews_page) : ?>class="active" <?php endif; ?>><a href="<?php echo esc_url(get_permalink($reviews_page)); ?>"><i class="sl sl-icon-star"></i> <?php esc_html_e('Reviews', 'listeo'); ?></a></li>
					<?php endif; ?>

					<?php $bookmarks_page = get_option('listeo_bookmarks_page');
					if ($bookmarks_page) : ?>
						<li <?php if ($post->ID == $bookmarks_page) : ?>class="active" <?php endif; ?>><a href="<?php echo esc_url(get_permalink($bookmarks_page)); ?>"><i class="sl sl-icon-heart"></i> <?php esc_html_e('Bookmarks', 'listeo'); ?></a></li>
					<?php endif; ?>

					<?php wp_nav_menu(array('theme_location' => 'dashboard_listings', 'menu_id' => 'dashboard_listings', 'container' => false, 'items_wrap' => '%3$s', 'fallback_cb' => false)); ?>
				</ul>
				<?php if (in_array($role, array('administrator', 'admin', 'owner', 'seller', 'vendor_staff'))) : ?>
					<?php if (class_exists('WeDevs_Dokan')) : ?>
						<ul data-submenu-title="<?php esc_html_e('Store', 'listeo'); ?>">
							<?php
							$home_url = home_url();
							$active_class = ' class="active"';
							global $wp;

							$request = $wp->request;

							$active = explode('/', $request);

							//unset($active[0]);

							if ($active) {
								$active_menu = implode('/', $active);

								if ($active_menu == 'new-product') {
									$active_menu = 'products';
								}

								if (get_query_var('edit') && is_singular('product')) {
									$active_menu = 'products';
								}
								if ($active_menu == 'store-dashboard') {
									$active_menu = 'dashboard';
								} else if ($active_menu == 'dashboard') {

									$active_menu = 'store-dashboard';
								}
							} else {
							}
							global $allowedposttags;

							// These are required for the hamburger menu.
							if (is_array($allowedposttags)) {
								$allowedposttags['input'] = [
									'id'      => [],
									'type'    => [],
									'checked' => []
								];
							}

							echo wp_kses(dokan_dashboard_nav($active_menu), $allowedposttags); ?>
							<?php wp_nav_menu(array('theme_location' => 'dashboard_dokan', 'menu_id' => 'dashboard_listings', 'container' => false, 'items_wrap' => '%3$s', 'fallback_cb' => false)); ?>
						</ul>
					<?php endif; ?>
				<?php endif; ?>
				<ul data-submenu-title="<?php esc_html_e('Account', 'listeo'); ?>">
					<?php $profile_page = get_option('listeo_profile_page');
					if ($profile_page) : ?>
						<li <?php if ($post->ID == $profile_page) : ?>class="active" <?php endif; ?>><a href="<?php echo esc_url(get_permalink($profile_page)); ?>"><i class="sl sl-icon-user"></i> <?php esc_html_e('My Profile', 'listeo'); ?></a></li>
					<?php endif; ?>


					<?php

					$orders_page_status = get_option('listeo_orders_page');

					if (class_exists('woocommerce') && $orders_page_status) :
						$orders_page =  wc_get_endpoint_url('orders', '', get_permalink(get_option('woocommerce_myaccount_page_id'))); ?>
						<li <?php if ($post->ID == $orders_page) : ?>class="active" <?php endif; ?>><a href="<?php echo esc_url($orders_page); ?>"><i class="sl sl-icon-basket"></i> <?php esc_html_e('My Orders', 'listeo'); ?></a></li>
					<?php endif; ?>

					<?php if (in_array($role, array('administrator', 'admin', 'owner', 'seller', 'vendor_staff'))) : ?>
						<?php
						$subscription_page_status = get_option('listeo_subscription_page');
						if (class_exists('WC_Subscriptions') && $subscription_page_status) {
							$subscription_page =  wc_get_endpoint_url('subscriptions', '', get_permalink(get_option('woocommerce_myaccount_page_id')));

							if ($subscription_page) : ?>
								<li <?php if ($post->ID == $subscription_page) : ?>class="active" <?php endif; ?>><a href="<?php echo esc_url($subscription_page); ?>"><i class="sl sl-icon-refresh"></i> <?php esc_html_e('My Subscriptions', 'listeo'); ?></a></li>
						<?php endif;
						} ?>
					<?php endif; ?>

					<li><a href="<?php echo wp_logout_url(home_url()); ?>"><i class="sl sl-icon-power"></i> <?php esc_html_e('Logout', 'listeo'); ?></a></li>
					<?php wp_nav_menu(array('theme_location' => 'dashboard_account', 'menu_id' => 'dashboard_listings', 'container' => false, 'items_wrap' => '%3$s', 'fallback_cb' => false)); ?>
				</ul>

				<?php do_action('listeo/dashboard-menu/end'); ?>



			</div>
		</div>
		<!-- Navigation / End -->

		<!-- Content
	================================================== -->
		<?php
		$current_user = wp_get_current_user();

		$roles = $current_user->roles;
		$role = array_shift($roles);
		if (!empty($current_user->user_firstname)) {
			$name = $current_user->user_firstname;
		} else {
			$name =  $current_user->display_name;
		}
		?>
		<div class="dashboard-content" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <?php
    if (current_user_can('seller') && function_exists('grabtogo_is_vendor_fully_verified') && !grabtogo_is_vendor_fully_verified(get_current_user_id())) {
        echo '<div class="notification warning margin-bottom-20">
            <p><strong>Action Required:</strong> Please <a href="' . home_url('/store-dashboard/settings/verification/') . '">verify your documents</a> to get your listing approved and published.</p>
        </div>';
    }
    ?>
			<!-- Titlebar -->
			<?php

			if (listeo_check_abandoned_cart()) { ?>

				<div class="notice notification" id="unpaid_listing_in_cart">
					<span><?php esc_html_e('You have unpaid listing in cart.', 'listeo') ?></span>
					<?php esc_html_e(' Please pay or cancel it before submitting new listing.', 'listeo') ?>
					<a class="" href="<?php echo wc_get_cart_url(); ?>"><strong><?php esc_html_e('View cart &#8594;', 'listeo') ?></strong></a>
				</div>
				<?php };

			if (listeo_is_payout_active() &&  get_user_meta(get_current_user_id(), 'listeo_core_payment_type', true) == 'paypal_payout') {
				$is_payout_email_added = esc_attr(get_user_meta(get_current_user_id(), 'listeo_paypal_payout_email', true));
				if (empty($is_payout_email_added)) {
					if (in_array($role, array('administrator', 'admin', 'owner', 'seller', 'vendor_staff'))) :
				?>

						<div class="notice notification margin-bottom-40" id="unpaid_listing_in_cart">
							<span style="display: block; font-weight: bold;"><?php esc_html_e('PayPal email missing!', 'listeo') ?></span>
							<?php esc_html_e('Please add your PayPal email address. This is required to get your payments for booking using PayPal Payout service.', 'listeo'); ?>
							<a class="" href="<?php echo get_permalink(get_option('listeo_wallet_page')); ?>"><strong><?php esc_html_e('View Wallet and set the Payout Method there &#8594;', 'listeo') ?></strong></a>
						</div>
			<?php endif;
				}
			}; ?>

			<div id="titlebar">
				<div class="row">
					<div class="col-md-12">
						<?php
						$is_dashboard_page = get_option('listeo_dashboard_page');
						$is_booking_page = get_option('listeo_bookings_page');
						global $post;
						if ($is_dashboard_page == $post->ID) { ?>
							<h2><?php esc_html_e('Hello', 'listeo'); ?> <?php echo esc_html($name); ?> !</h2>
							<?php } else if ($is_booking_page == $post->ID) {
							$status = '';
							if (isset($_GET['status'])) {
								$status = $_GET['status'];
								switch ($status) {
									case 'approved': ?>
										<h1><?php esc_html_e('Approved Bookings', 'listeo'); ?></h1>
									<?php
										break;
									case 'waiting': ?>
										<h1><?php esc_html_e('Pending Bookings', 'listeo'); ?></h1>
									<?php
										break;
									case 'expired': ?>
										<h1><?php esc_html_e('Expired Bookings', 'listeo'); ?></h1>
									<?php
										break;
									case 'cancelled': ?>
										<h1><?php esc_html_e('Cancelled Bookings', 'listeo'); ?></h1>
									<?php
										break;

									default:
									?>
										<h1><?php esc_html_e('Bookings', 'listeo'); ?></h1>
								<?php
										break;
								}
							} else { ?>
								<h1><?php the_title(); ?></h1>
							<?php }
						} else { ?>
							<h1><?php the_title(); ?></h1>
						<?php } ?>
						<!-- Breadcrumbs -->
						<nav id="breadcrumbs">
							<ul>
								<li><a href="<?php echo home_url(); ?>"><?php esc_html_e('Home', 'listeo'); ?></a></li>
								<li><?php esc_html_e('Dashboard', 'listeo'); ?></li>
							</ul>
						</nav>
					</div>
				</div>
			</div>
			<?php

			while (have_posts()) : the_post();
				the_content();
			endwhile; // End of the loop. 
			?>

			<!-- Copyrights -->
			<div class="row">
				<div class="col-md-12">
					<div class="copyrights"> <?php $copyrights = get_option('pp_copyrights', '&copy; Theme by Purethemes.net. All Rights Reserved.');

												echo wp_kses($copyrights, array('a' => array('href' => array(), 'title' => array()), 'br' => array(), 'em' => array(), 'strong' => array(),));
												?></div>
				</div>
			</div>

		</div>
	</div>
	<!-- Dashboard / End -->
<?php
	get_footer('empty');
} ?>