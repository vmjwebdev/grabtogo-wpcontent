<?php
if (isset($data)) :


endif;
if ($data->comment == 'owner reservations') {
	return;
}
if (isset($data->order_id) && !empty($data->order_id) && $data->status == 'confirmed') {
	$payment_method = get_post_meta($data->order_id, '_payment_method', true);
	if (get_option('listeo_disable_payments')) {
		$payment_method = 'cod';
	}
}

$show_contact = (get_option('listeo_lock_contact_info_to_paid_bookings')) ? false : true;


$_payment_option = get_post_meta($data->listing_id, '_payment_option', true);
$_rental_timepicker = get_post_meta($data->listing_id, '_rental_timepicker', true);
$listing_type = get_post_meta($data->listing_id, '_listing_type', true);
if (empty($_payment_option)) {
	$_payment_option = 'pay_now';
}
if ($_payment_option == "pay_cash") {
	$payment_method = 'cod';
}

$class = array();
$tag = array();
$show_approve = false;
$show_reject = false;

switch ($data->status) {
	case 'waiting':
		$class[] = 'waiting-booking';
		$tag[] = '<span class="booking-status pending">' . esc_html__('Waiting for owner confirmation', 'listeo_core') . '</span>';
		$show_approve = true;
		$show_reject = true;
		break;


	case 'confirmed':
		$class[] = 'approved-booking';
		$tag[] = '<span  class="booking-status">' . esc_html__('Approved', 'listeo_core') . '</span>';;
		if ($data->price > 0) {
			if ($_payment_option == "pay_cash") {
				$tag[] = '<span class="booking-status unpaid">' . esc_html__('Cash payment', 'listeo_core') . '</span>';
			} else {
				$tag[] = '<span class="booking-status unpaid">' . esc_html__('Unpaid', 'listeo_core') . '</span>';
			}
		}
		$show_approve = false;
		$show_reject = true;
		break;


	case 'paid':

		$class[] = 'approved-booking';
		$tag[] = '<span class="booking-status">' . esc_html__('Approved', 'listeo_core') . '</span>';
		if ($data->price > 0) {
			$tag[] = '<span class="booking-status paid">' . esc_html__('Paid', 'listeo_core') . '</span>';
		}
		$show_approve = false;
		$show_reject = false;
		$show_contact = true;
		break;

	case 'cancelled':

		$class[] = 'canceled-booking';
		$tag[] = '<span class="booking-status">' . esc_html__('Canceled', 'listeo_core') . '</span>';
		$show_approve = false;
		$show_reject = false;
		$show_delete = true;
		break;
	case 'expired':

		$class[] = 'expired-booking';
		$tag[] = '<span class="booking-status" style="background-color:gray">' . esc_html__('Expired', 'listeo_core') . '</span>';
		$show_approve = false;
		$show_reject = false;
		$show_delete = true;
		break;

	default:
		if (Listeo_Core_Bookings_Calendar::is_booking_external($data->status)) {
			$class[]      = 'external-booking';
			$tag[]        = '<span class="booking-status" style="background-color:gray">' . esc_html__('External', 'listeo_core') . '</span>';
			$show_approve = false;
			$show_reject  = false;
			$show_delete  = false;
			break;
		}
		# code...
		break;
}
//$payment_url = $order->get_checkout_payment_url();


//get order data
if (isset($data->order_id) && !empty($data->order_id) && in_array($data->status, array('confirmed', 'pay_to_confirm'))) {
	$order = wc_get_order($data->order_id);
	if ($order) {
		$payment_url = $order->get_checkout_payment_url();

		$order_data = $order->get_data();

		$order_status = $order_data['status'];
	}

	if (isset($data->expiring) && $data->expiring != '0000-00-00 00:00:00' && new DateTime() > new DateTime($data->expiring)) {
		$payment_url = false;
		$class[] = 'expired-booking';
		unset($tag[1]);
		$tag[] = '<span class="booking-status">' . esc_html__('Expired', 'listeo_core') . '</span>';
		$show_delete = true;
	}
}

?>
<li class="user-booking <?php echo implode(' ', $class); ?>" id="booking-list-<?php echo esc_attr($data->ID); ?>">
	<div class="list-box-listing bookings">
		<div class="list-box-listing-img"><?php echo get_avatar($data->bookings_author, '70') ?></div>
		<div class="list-box-listing-content">
			<div class="inner">
				<h3 id="title"><a href="<?php echo get_permalink($data->listing_id); ?>"><?php echo get_the_title($data->listing_id); ?></a> <?php echo implode(' ', $tag); ?></h3>

				<div class="inner-booking-list">
					<h5><?php esc_html_e('Booking Date:', 'listeo_core'); ?></h5>
					<ul class="booking-list">

						<?php
						//get post type to show proper date
						$listing_type = get_post_meta($data->listing_id, '_listing_type', true);

						if ($listing_type == 'rental') { ?>
							<li class="highlighted" id="date">
								<?php
								if ($_rental_timepicker == 'on') {
									// $data->date_start  is date and time in this format 2024-03-01 12:00, 
									//return it in wordpress format with date and time
									echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($data->date_start)); ?>
									-
								<?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($data->date_end));
								} else { ?>
									<?php echo date_i18n(get_option('date_format'), strtotime($data->date_start)); ?>
									-
								<?php echo date_i18n(get_option('date_format'), strtotime($data->date_end));
								} ?>
							</li>
							<?php

							$today = date('Y-m-d H:i:s');
							if ($today > $data->date_start || $today > $data->date_end) {
								$show_renew = false;
							}
							?>
						<?php } else if ($listing_type == 'service') {
						?>
							<li class="highlighted" id="date">
								<?php echo date_i18n(get_option('date_format'), strtotime($data->date_start)); ?> <?php esc_html_e('at', 'listeo_core'); ?>
								<?php
								$time_start = date_i18n(get_option('time_format'), strtotime($data->date_start));
								$time_end = date_i18n(get_option('time_format'), strtotime($data->date_end)); ?>

								<?php echo esc_html($time_start); ?> <?php if ($time_start != $time_end) echo '- ' . $time_end; ?></li>

						<?php } else {
							//event 
						?>
							<li class="highlighted" id="date">
								<?php
								$meta_value = get_post_meta($data->listing_id, '_event_date', true);
								$meta_value_timestamp = get_post_meta($data->listing_id, '_event_date_timestamp', true);
								$meta_value_date = explode(' ', $meta_value, 2);

								$meta_value_date[0] = str_replace('/', '-', $meta_value_date[0]);

								$meta_value = date_i18n(get_option('date_format'), $meta_value_timestamp);


								//echo strtotime(end($meta_value_date));
								//echo date( get_option( 'time_format' ), strtotime(end($meta_value_date)));
								if (isset($meta_value_date[1])) {
									$time = str_replace('-', '', $meta_value_date[1]);
									$meta_value .= esc_html__(' at ', 'listeo_core');
									$meta_value .= date_i18n(get_option('time_format'), strtotime($time));
								}
								echo $meta_value;

								$meta_value = get_post_meta($data->listing_id, '_event_date_end', true);
								$meta_value_timestamp = get_post_meta($data->listing_id, '_event_date_end_timestamp', true);
								if (isset($meta_value) && !empty($meta_value)) :

									$meta_value_date = explode(' ', $meta_value, 2);

									$meta_value_date[0] = str_replace('/', '-', $meta_value_date[0]);
									$meta_value = date_i18n(get_option('date_format'), $meta_value_timestamp);


									//echo strtotime(end($meta_value_date));
									//echo date( get_option( 'time_format' ), strtotime(end($meta_value_date)));
									if (isset($meta_value_date[1])) {
										$time = str_replace('-', '', $meta_value_date[1]);
										$meta_value .= esc_html__(' at ', 'listeo_core');
										$meta_value .= date_i18n(get_option('time_format'), strtotime($time));
									}
									echo ' - ' . $meta_value; ?>
								<?php endif; ?>
							</li>
						<?php }
						?>

					</ul>
				</div>
				<?php $details = json_decode($data->comment);
				if (!get_option('listeo_remove_guests')) {
					if (
						(isset($details->children) && $details->children > 0)
						||
						(isset($details->adults) && $details->adults > 0)
						||
						(isset($details->tickets) && $details->tickets > 0)
					) {
						$has_children = false;
						if (isset($details->children) && $details->children > 0) {
							$has_children = true;
						} ?>
						<div class="inner-booking-list inner-booking-list-guests">
							<h5><?php esc_html_e('Booking Details:', 'listeo_core'); ?></h5>
							<ul class="booking-list">
								<li class="highlighted" id="details">
									<?php
									$details_guests_output = array();
									if (
										isset($details->adults)  && $details->adults > 0 &&
										$has_children == false
									) :  ?>
										<?php $details_guests_output[] = sprintf(_n('%d Guest', '%s Guests', $details->adults, 'listeo_core'), $details->adults) ?>
									<?php endif; ?>
									<?php if (
										isset($details->adults)  && $details->adults > 0 &&
										$has_children == true
									) : ?>
										<?php $details_guests_output[] = sprintf(_n('%d Adult', '%s Adults', $details->adults, 'listeo_core'), $details->adults) ?>
									<?php endif; ?>
									<?php if (isset($details->children) && $details->children > 0) : ?>
										<?php $details_guests_output[] = sprintf(_n('%d Child', '%s Children', $details->children, 'listeo_core'), $details->children) ?>
									<?php endif; ?>
									<?php if (isset($details->infants) && $details->infants > 0) : ?>
										<?php $details_guests_output[] = sprintf(_n('%d Infant', '%s Infants', $details->infants, 'listeo_core'), $details->infants) ?>
									<?php endif; ?>
									<?php if (isset($details->animals) && $details->animals > 0) : ?>
										<?php $details_guests_output[] = sprintf(_n('%d Animal', '%s Animals', $details->animals, 'listeo_core'), $details->animals) ?>
									<?php endif; ?>

									<?php if (isset($details->tickets)  && $details->tickets > 0) : ?>
										<?php $details_guests_output[] = sprintf(_n('%d Ticket', '%s Tickets', $details->tickets, 'listeo_core'), $details->tickets) ?>
									<?php endif; ?>

									<?php if (count($details_guests_output) > 1) : ?>
										<?php echo implode(', ', $details_guests_output); ?>
									<?php else: ?>
										<?php echo implode(' ', $details_guests_output); ?>
									<?php endif; ?>
								</li>
							</ul>
						</div>
				<?php }
				} ?>

				<?php $address = get_post_meta($data->listing_id, '_address', true);

				if ($address && $show_contact) { ?>
					<div class="inner-booking-list">
						<h5><?php esc_html_e('Booking Location:', 'listeo_core'); ?></h5>
						<ul class="booking-list">
							<?php echo esc_html($address); ?>

						</ul>
					</div>
				<?php
				}

				$currency_abbr = get_option('listeo_currency');
				$currency_abbr = apply_filters('vessno_listeo_currency_user_dashboard', $currency_abbr, $data);
				$currency_postion = get_option('listeo_currency_postion');
				$currency_symbol = Listeo_Core_Listing::get_currency_symbol($currency_abbr);
				$decimals = get_option('listeo_number_decimals', 2);

				if ($data->price) : ?>
					<div class="inner-booking-list">
						<h5><?php esc_html_e('Price:', 'listeo_core'); ?></h5>
						<ul class="booking-list">
							<li class="highlighted" id="price">
								<?php if ($currency_postion == 'before') {
									echo $currency_symbol . ' ';
								}  ?>
								<?php
								if (is_numeric($data->price)) {
									echo number_format_i18n($data->price, $decimals);
								} else {
									echo esc_html($data->price);
								};

								?>
								<?php if ($currency_postion == 'after') {
									echo ' ' . $currency_symbol;
								}  ?></li>
						</ul>
					</div>
				<?php endif; ?>
				<?php if ($show_contact && isset($data->owner_id)) { ?>
					<div class="inner-booking-list">
						<h5><?php esc_html_e('Owner :', 'listeo_core'); ?></h5>
						<ul class="booking-list" id="client">
							<li id="name">
								<a href="<?php echo get_author_posts_url($data->owner_id); ?>"><?php echo listeo_get_users_name($data->owner_id); ?></a>
							</li>
							<li>
								<?php
								$owner_email = get_the_author_meta('user_email', $data->owner_id);
								$owner_phone = get_the_author_meta('phone', $data->owner_id);
								?>
								<a href="mailto:<?php echo $owner_email; ?>"><?php echo $owner_email; ?></a>
								<a href="tel:<?php echo $owner_phone; ?>"><?php echo $owner_phone; ?></a>

							</li>
						</ul>
					</div>
				<?php } ?>
				<?php if (false === Listeo_Core_Bookings_Calendar::is_booking_external($data->status)) : ?>
					<div class="inner-booking-list">

						<h5><?php esc_html_e('Client:', 'listeo_core'); ?></h5>
						<ul class="booking-list" id="client">
							<?php if (isset($details->first_name) || isset($details->last_name)) : ?>
								<li id="name"><?php if (isset($details->first_name)) echo esc_html(stripslashes($details->first_name)); ?> <?php if (isset($details->last_name)) echo esc_html(stripslashes($details->last_name)); ?></li>
							<?php endif; ?>
							<?php if (isset($details->email)) : ?><li id="email"><a href="mailto:<?php echo esc_attr($details->email) ?>"><?php echo esc_html($details->email); ?></a></li>
							<?php endif; ?>
							<?php if (isset($details->phone)) : ?><li id="phone"><a href="tel:<?php echo esc_attr($details->phone) ?>"><?php echo esc_html($details->phone); ?></a></li>
							<?php endif; ?>
						</ul>
					</div>

					<?php

					if ($show_contact && isset($details->billing_address_1) && !empty($details->billing_address_1)) : ?>
						<div class="inner-booking-list">

							<h5><?php esc_html_e('Address:', 'listeo_core'); ?></h5>
							<ul class="booking-list" id="client">

								<?php if (isset($details->billing_address_1)) : ?>
									<li id="billing_address_1"><?php echo esc_html(stripslashes($details->billing_address_1)); ?> </li>
								<?php endif; ?>
								<?php if (isset($details->billing_address_1)) : ?>
									<li id="billing_postcode"><?php echo esc_html(stripslashes($details->billing_postcode)); ?> </li>
								<?php endif; ?>
								<?php if (isset($details->billing_city)) : ?>
									<li id="billing_city"><?php echo esc_html(stripslashes($details->billing_city)); ?> </li>
								<?php endif; ?>
								<?php if (isset($details->billing_country)) : ?>
									<li id="billing_country"><?php echo esc_html(stripslashes($details->billing_country)); ?> </li>
								<?php endif; ?>

							</ul>
						</div>
					<?php endif; ?>
					<?php $listing_phone = get_post_meta($data->listing_id, '_phone', true);
					if ($show_contact && !empty($listing_phone)) { ?>
						<div class="inner-booking-list">
							<h5><?php esc_html_e('Phone:', 'listeo_core'); ?></h5>
							<ul class="booking-list" id="client">
								<li id="phone"><a href="tel:<?php echo $listing_phone; ?>"><?php echo esc_html($listing_phone); ?></a></li>
							</ul>
						</div>
					<?php } ?>
					<?php if (isset($details->service) && !empty($details->service)) : ?>
						<div class="inner-booking-list">
							<h5><?php esc_html_e('Extra Services:', 'listeo_core'); ?></h5>
							<?php echo listeo_get_extra_services_html($details->service); //echo wpautop( $details->service); 
							?>
						</div>
					<?php endif; ?>
					<?php if (isset($details->message) && !empty($details->message)) : ?>
						<div class="inner-booking-list">
							<h5><?php esc_html_e('Message:', 'listeo_core'); ?></h5>
							<?php echo wpautop(esc_html(stripslashes($details->message))); ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>
				<?php if (true === Listeo_Core_Bookings_Calendar::is_booking_external($data->status)) : ?>
					<?php $ical_event_data = json_decode($data->comment); ?>
					<?php if (true === is_object($ical_event_data)) : ?>
						<div class="inner-booking-list">
							<?php if (true === isset($ical_event_data->summary)) : ?>
								<h5><?php esc_html_e('Summary:', 'listeo_core'); ?></h5>
								<?php esc_html_e($ical_event_data->summary); ?>
							<?php endif; ?>
						</div>
						<div class="inner-booking-list">
							<?php if (true === isset($ical_event_data->description)) : ?>
								<h5><?php esc_html_e('Details:', 'listeo_core'); ?></h5>
								<?php echo wp_kses(
									html_entity_decode($ical_event_data->description),
									array(
										'p'      => array(),
										'a'      => array(
											'href'  => array(),
											'title' => array()
										),
										'br'     => array(),
										'em'     => array(),
										'strong' => array(),
									)
								); ?>


							<?php endif; ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>

				<div class="inner-booking-list">
					<?php if (true === Listeo_Core_Bookings_Calendar::is_booking_external($data->status)) : ?>
						<h5><?php esc_html_e('Booking last imported on:', 'listeo_core'); ?></h5>
					<?php else : ?>
						<h5><?php esc_html_e('Booking requested on:', 'listeo_core'); ?></h5>
					<?php endif; ?>
					<ul class="booking-list">
						<li class="highlighted" id="price">
							<?php echo date_i18n(get_option('date_format'), strtotime($data->created)); ?>
							<?php
							$date_created = explode(' ', $data->created);
							if (isset($date_created[1])) { ?>
								<?php esc_html_e('at', 'listeo_core'); ?>

							<?php echo date_i18n(get_option('time_format'), strtotime($date_created[1]));
							} ?>
						</li>
					</ul>
				</div>
				<?php if (isset($data->expiring) && $data->expiring != '0000-00-00 00:00:00' && $data->expiring != $data->created) { ?>
					<div class="inner-booking-list">
						<h5><?php esc_html_e('Payment due:', 'listeo_core'); ?></h5>
						<ul class="booking-list">
							<li class="highlighted" id="price">
								<?php echo date_i18n(get_option('date_format'), strtotime($data->expiring)); ?>
								<?php
								$date_expiring = explode(' ', $data->expiring);
								if (isset($date_expiring[1])) { ?>
									<?php esc_html_e('at', 'listeo_core'); ?>

								<?php echo date_i18n(get_option('time_format'), strtotime($date_expiring[1]));
								} ?>
							</li>
						</ul>
					</div>
				<?php } ?>

				<!-- Cusotm fields -->
				<?php
				$fields = get_option("listeo_{$listing_type}_booking_fields");
				if ($fields) {
					foreach ($fields as $field) {
						if ($field['type'] == 'header') {
							continue;
						}
						$meta = get_booking_meta($data->ID, $field['id']);
						if (!empty($meta)) { ?>

							<div class="inner-booking-list">
								<h5><?php echo $field['name'] ?></h5>
								<ul class="booking-list">
									<li class="<?php if ($field['type'] == 'checkbox') {
													echo 'checkboxed';
													$meta = '';
												} ?> " id="<?php echo $field['type']; ?>">
										<?php
										if (is_array($meta)) {
											$i = 0;
											$last =  count($meta);
											foreach ($meta as $key) {
												$i++;
												echo $field['options'][$key];
												if ($i >= 0 && $i < $last) : echo ", ";
												endif;
											}
										} else {
											switch ($field['type']) {
												case 'file':
													echo '<a href="' . $meta . '" /> ' . esc_html__('Download', 'listeo_core') . ' ' . wp_basename($meta) . ' </a></li>';
													break;

												case 'radio':
													echo $field['options'][$meta];
													break;
												case 'select':
													echo $field['options'][$meta];
													break;

												default:
													echo $meta;
													break;
											}
										} ?>
									</li>
								</ul>
							</div>

				<?php }
					}
				}
				?>
				<div class="listeo-user-booking-action-buttons">
					<?php if (false === Listeo_Core_Bookings_Calendar::is_booking_external($data->status)) : ?>
						<?php if (get_option('listeo_messages_page')) { ?>
							<a href="#small-dialog" data-recipient="<?php echo esc_attr($data->owner_id); ?>" data-booking_id="booking_<?php echo esc_attr($data->ID); ?>" class="booking-message rate-review popup-with-zoom-anim"><i class="sl sl-icon-envelope-open"></i> <?php esc_attr_e('Send Message', 'listeo_core') ?></a>
						<?php } ?>
						<?php endif;
					if (get_option('listeo_ticket_status')) :
						if ($data->status == 'paid' || ($data->status == 'confirmed' && $_payment_option == 'pay_cash')) :
							$ticket_url = home_url("/get-ticket/{$data->ID}/"); ?>
							<a href="<?php echo esc_url($ticket_url) ?>" class="download-ticket-btn button" target="_blank"><i class="sl sl-icon-arrow-down-circle"></i> <?php esc_html_e('Get Ticket', 'listeo_core'); ?></a>
						<?php endif; ?>
					<?php endif; ?>
				</div>

			</div>
		</div>
	</div>
	<div class="buttons-to-right booking-buttons-actions">
		<?php

		if (isset($payment_url) && !empty($payment_url) && !get_option('listeo_disable_payments') && $_payment_option != 'pay_cash') :
			if ($order_status != 'completed') : ?>
				<a href="<?php echo esc_url($payment_url) ?>" class="button green pay"><i class="sl sl-icon-check"></i> <?php esc_html_e('Pay', 'listeo_core'); ?></a>
		<?php endif;
		endif; ?>
		<?php if (isset($show_delete) && $show_delete == true) : ?>
			<a href="#" class="button gray delete" data-booking_id="<?php echo esc_attr($data->ID); ?>"><i class="sl sl-icon-trash"></i> <?php esc_html_e('Delete', 'listeo_core'); ?></a>
		<?php endif; ?>
		<?php if ($show_reject) : ?>
			<a href="#" class="button gray reject" data-booking_id="<?php echo esc_attr($data->ID); ?>"><i class="sl sl-icon-close"></i> <?php esc_html_e('Cancel', 'listeo_core'); ?></a>
		<?php endif; ?>

	</div>
</li>