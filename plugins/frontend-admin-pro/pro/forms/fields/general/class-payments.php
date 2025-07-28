<?php
namespace Frontend_Admin\Field_Types;

if ( ! class_exists( 'Frontend_Admin\Field_Types\payment_form' ) ) :

class payment_form extends Field_Base {	
	
	/*
	*  initialize
	*
	*  This function will setup the field type data
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function initialize() {
		
		// vars
		$this->name = 'payment_form';
		$this->label = __("Payment",'acf-frontend-form-element');
        $this->category = __("Payments",'acf-frontend-form-element');
		$this->defaults = array(
			'payment_value' => 'form_submissions',
			'credit_card_fields' => array(
				array(
					'placeholder' => '•••• •••• •••• ••••',
					'label' => __( 'Number', 'acf-frontend-form-element' ),
					'id' => __( 'Number', 'acf-frontend-form-element' ),
					'width' => '25',
					'name' => 'number',
				),
				array(
					'placeholder' => __( 'Full Name', 'acf-frontend-form-element' ),
					'label' => __( 'Name on Card', 'acf-frontend-form-element' ),
					'id' => __( 'Name', 'acf-frontend-form-element' ),
					'width' => '25',
					'name' => 'name',
				),
				array(
					'placeholder' => '••/••',
					'label' => __( 'Expiration', 'acf-frontend-form-element' ),
					'id' => __( 'Expiration', 'acf-frontend-form-element' ),
					'width' => '15',
					'name' => 'exp',
				),
				array(
					'placeholder' => '•••',
					'label' => __( 'CVC', 'acf-frontend-form-element' ),
					'id' => __( 'CVC', 'acf-frontend-form-element' ),
					'width' => '15',
					'name' => 'cvc',
				)    
			),
			'payment_methods' => [ 'credit_card', 'paypal' ],
			'submit_form' => false
		);

		add_action( 'wp', array( $this, 'complete_paypal_purchase' ) );
		add_action( 'wp_ajax_frontend_admin/payments/new_payment', [ $this, 'new_payment'] );
        add_action( 'wp_ajax_nopriv_frontend_admin/payments/new_payment', [ $this, 'new_payment'] );	

		add_action( 'wp_ajax_frontend_admin/ajax_query_get_plans', array( $this, 'get_plans_query' ) );

		add_action( 'frontend_admin/form_assets/type=' . $this->name, array( $this, 'form_assets' ) );
		
	}


	function complete_paypal_purchase(){
		if( ! isset( $_GET['fea_paypal_endpoint'] ) || ! isset( $_GET['fea_token'] ) ) return;

		$fea_token = $_GET['fea_token'];

		$user_id = get_current_user_id();
		//get cookie that has json data
		$data = stripslashes( $_COOKIE['fea_payment_data-'.$fea_token] );

		if( ! $data ) return;
		
		$data = json_decode( $data, true );
		if (is_null($data)) {
			return;
		}

		if( ! wp_verify_nonce( $_GET['fea_token'], 'fea_payment_'.$data['field_id'] ) ){
			error_log( __( 'Invalid Paypal Nonce', 'acf-frontend-form-element' ) );
			echo '<script>localStorage.setItem("feaToken-"'.$data['field_id'].', "error");</script>';
		}

		if( ! isset( $_GET['paymentId'] ) && ! isset( $_GET['PayerID'] ) ) return;

		$pay_id = $_GET['paymentId'];

		if( $pay_id != $data['paypal_payid'] ) return;

		$payer_id = $_GET['PayerID'];

		$gateway = fea_instance()->paypal;
		
		if( ! $gateway ){
			echo __( 'Could not connect to Paypal. Please check your API keys.' );
		}		
		// Once the transaction has been approved, we need to complete it.
		$transaction = $gateway->completePurchase( array(
			'payer_id'             => $payer_id,
			'transactionReference' => $pay_id,
		) );
		$response = $transaction->send();

		if( $response->isSuccessful() ){
			$transaction = $response->getData()['transactions'][0];

			$external_id = $response->getTransactionReference();
			$payment_id = fea_instance()->payments_handler->insert_payment( array(
				'created_at' => current_time( 'mysql' ),
				'description' => $data['description'] ?? __( 'Form Submission Payment via Pay', 'frontend-admin' ),
				'external_id' => $external_id,
				'amount' => $transaction['amount']['total'],
				'currency' => $transaction['amount']['currency'],
				'user' => $user_id,
				'method' => 'paypal',
			) );

			//setcookie( 'fea_payment_data-'.$fea_token, '', time() - 3600, '/' );

			echo '<script>localStorage.setItem("feaToken-'.$data['field_id'].'", "'.$external_id.'")</script>';
		}else{
			echo __( 'Error With Payment. Please close this window and try again.', 'acf-frontend-form-element' );
		}
					
		exit();
	}

	public function get_payment_data( $data ){
		$payment_data = array();
		foreach( array( 'amount', 'card', 'description', 'currency' ) as $key ){
			if( isset( $data["payment_$key"] ) ){
				$payment_data[$key] = $data["payment_$key"];
			}
		}		

		return $payment_data;
	}

	public function new_payment(){
		global $fea_instance; 

		$error_return = array( 'message' => __( 'Something went wrong...', 'acf-frontend-form-element' ) ); 

		// Verify nonce
		if( ! isset( $_POST['feap_nonce'] ) || ! wp_verify_nonce( $_POST['feap_nonce'], 'feap_nonce' ) ) wp_send_json_error( $error_return );

		if( ! isset( $_POST['field'] ) ) wp_send_json_error( $error_return );
		$field = sanitize_text_field( $_POST['field'] );

		$field = $fea_instance->frontend->get_field( $field );

		if( ! $field ) wp_send_json_error( $error_return );

		$user_id = get_current_user_id();

		$method = sanitize_text_field( $_POST['method'] );
			
		$data = wp_parse_args( $field, array(
			'payment_value' => 'form_submissions',
			'payment_amount' => '1',
			'payment_currency' => 'USD',
			'payment_plan' => '',
		) );

		if( empty( $data['payment_value'] ) ){
			wp_send_json_error( $error_return );
		} 

		$payment_data = $this->get_payment_data( $data );

		if( ! empty( $field['product'] ) && 'plan' == $field['product'] ){
			$plan = $fea_instance->plans_handler->get_plan( $field['plan'] );
			if( $plan ){
				$payment_data['amount'] = $plan->pricing;
				$payment_data['currency'] = $plan->currency;
			}
		}

		if( 'paypal' == $method ){
			$gateway = $fea_instance->paypal;
			if( ! $gateway ){
				$error_return['message'] = __( 'Could not connect to Paypal. Please check your API keys.', 'acf-frontend-form-element' );
				wp_send_json_error( $error_return );
			}

			try {
				$fea_token = wp_create_nonce( 'fea_payment_'.$field['key'] );
				$payment_data = array_merge( $payment_data, array(
					'returnUrl'     => home_url( '?fea_paypal_endpoint' . '&fea_token=' . $fea_token ),
					'cancelUrl'     => home_url( '?fea_paypal_endpoint' . '&fea_token=' . $fea_token ),
				) );

				$transaction = $gateway->purchase( $payment_data );

				$response = $transaction->send();

				if ($response->isRedirect()) {
					$user_data = array( 
						'amount'=> $payment_data['payment_amount'],
						'currency' => $payment_data['payment_currency'],
						'field_id' => $field['key'],
						'paypal_payid' => $response->getTransactionReference(),
						'description' => $field['payment_description'],
					);
					setcookie( 'fea_payment_data-'.$fea_token, json_encode( $user_data ), time() + (86400 * 30), '/' );


					wp_send_json_success( array( 'redirect' => $response->getRedirectUrl(), 'token' => $fea_token ));
				}else{
					$error_return['message'] = $response->getMessage();
					wp_send_json_error( $error_return );
				}
		
			} catch (\Exception $e) {
				$error_return['message'] = $e->getMessage();
				wp_send_json_error( $error_return );
			}
		}elseif( 'stripe' == $method ){	
			$card = sanitize_text_field( $_POST['card'] );	
			if( empty( $card ) ){
				$error_return['message'] = __( 'Missing Stripe token.', 'acf-frontend-form-element' );
				wp_send_json_error( $error_return );
			}
			$payment_data['token'] = $card;
			$gateway = $fea_instance->stripe;
			if( ! $gateway ){
				$error_return['message'] = __( 'Could not connect to Stripe. Please check your API keys.', 'acf-frontend-form-element' );
				wp_send_json_error( $error_return );
			}
	
			$response = $gateway->purchase($payment_data)->send();
			// Process response
			if ($response->isSuccessful()) {
				$external_id = $response->getTransactionReference();
				$payment_id = $fea_instance->payments_handler->insert_payment( 
					[
						'created_at' => current_time( 'mysql' ),
						'description' => $payment_data['payment_description'] ?? $payment_data['description'] ?? __( 'Form Submission Payment via Stripe', 'frontend-admin' ),
						'external_id' => $external_id,
						'amount' => $payment_data['amount'],
						'currency' => $payment_data['currency'],
						'user' => $user_id,
						'method' => $method,
					]
				);
	
				// create plan subscription
				/* if( ! empty( $field['product'] ) && 'plan' == $field['product'] ){
					$plan = $fea_instance->payments_handler->get_plan( $field['plan'] );
					if( $plan ){
						$subscription = array(
							'user' => get_current_user_id(),
							'plan' => $plan->id,
							'gross' => $plan->amount,
							'payment_token' => $payment_id,
						);

						$subscription_id = $fea_instance->payments_handler->insert_subscription( $subscription );
					}
				} */
				
				wp_send_json_success( array( 'payment' => $external_id, 'message' => $response->getMessage() ) );

			} else {
				$error_return['message'] = $response->getMessage();
				wp_send_json_error( $error_return );
			}
		}
		
		wp_send_json_error( $error_return );
	}



    function prepare_field( $field ){
		if( ! empty( $GLOBALS['admin_form']['approval'] ) ) return false;

		if( ! empty( $field['payment_methods'] ) && in_array( 'credit_card', $field['payment_methods'] ) ){
	    	$field['wrapper']['data-method'] = 'credit-card';
		}
  		return $field;
    }

 	public function validate_value( $valid, $value, $field, $input ){
		if( $value == 'preview' ) return $valid;

		if( ! $value ){			
			$valid = __('Please fill in the payment form.', 'acf-frontend-form-element');
		}else{
			$payment = fea_instance()->payments_handler->get_payment( $value, 'external_id' );
			if( ! $payment ){
				$valid = __('Payment not found.', 'acf-frontend-form-element');
			}
		}

		return $valid;
	}

	public function form_assets( $field ){
		$processor = '';
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '-min';
		$deps = array( 'fea-public', 'acf' );
		
		if( $field['payment_methods'] && in_array( 'credit_card', $field['payment_methods'] ) ){
			wp_enqueue_style( 'fea-card', FEAP_URL . 'assets/css/card-min.css', array(), FEA_VERSION );
			wp_enqueue_script( 'fea-card', FEAP_URL . 'assets/js/card-min.js', array(), FEA_VERSION, true );
			wp_enqueue_script( 'stripe', 'https://js.stripe.com/v2/', [], FEA_VERSION, true );	
			$deps[] = 'stripe';
			$deps[] = 'fea-card';
		}		
		wp_enqueue_style( 'fea-payments', FEA_URL . 'pro/assets/css/payments' .$min. '.css', array(), FEA_VERSION );
		wp_enqueue_script( 'fea-payments', FEA_URL . 'pro/assets/js/payments' .$min. '.js', $deps, FEA_VERSION, true );
				
		$payment_vars = array(
			'cc_nonce' => wp_create_nonce( 'feap_nonce' ),
			'ajax_url' => admin_url( 'admin-ajax.php' ), 
		);
		if( ! empty( fea_instance()->stripe ) ){
			$stripe_test = get_option( 'acff_stripe_live_mode' ) ? 'live' : 'test';
			$payment_vars['stripe_spk'] = get_option( "acff_stripe_{$stripe_test}_publish_key" );
		}
		wp_localize_script( 'fea-payments', 'payment_vars', $payment_vars );
		
	} 

	public function render_field( $field ){
		if( empty( $field['value'] ) ) $field['value'] = '';

		$paid = false;
 		
		$div = array(
			'class' => 'frontend-admin-payment',
		);

		$div['data-submit'] = $field['submit_form'] ?? 0;

		if( $paid ){
			$div['class'] .= ' paid';
		}
		if( feadmin_edit_mode() ){
			$value = 'preview';
		}
		acf_hidden_input( array( 'name' => $field['name'], 'class' => 'payment-input', 'value' => $field['value'] ) );
		?>
		<div <?php acf_esc_attr_e( $div ); ?>>
			<div class="hide-if-payed">
				<?php $this->display_payment_gateways( $field ); ?>
			</div>
		</div> 
		<?php
			if( isset( $field['payment_plan'] ) && $field['already_paid'] == 'message' && $field['already_paid_message'] ){
				?>
				<div class="show-if-payed">
				<?php echo '<p>' . str_replace( '[remaining_submits]', '<span class="remaining-submits">'.$remaining.'</span>', $field['already_paid_message'] ) . '</p>'; ?>
				</div>
				<?php		
			}

			?>
		<?php
	}

	function display_payment_gateways( $field ){
		if( empty( $field['payment_methods'] ) ){
			return;
		} 

		if( ! is_array( $field['payment_methods'] ) ){
			$field['payment_methods'] = array( $field['payment_methods'] );
		}

		$methods_count = count( $field['payment_methods'] ); 

		$hide_methods = '';

		if( $methods_count > 1 ){
			$hide_methods = 'frontend-admin-hidden';
			$choices = array();
			foreach( $field['payment_methods'] as $index => $method ){
				if( $index == 0 ){
					$default_method = $method;
				}
				$choices[$method] = empty( $field[$method.'_label'] ) ? ucwords(str_replace("_", " ", $method ) ) : $field[$method.'_label'];
			}

			acf_render_field( array(
				'name' 			   => $field['key'] . '_method',
				'key' 			   => $field['key'] . '_method',
				'prefix'		   => $field['prefix'],
				'field_label_hide' => 1,
				'type'			   => 'radio',
				'choices'		   => $choices,
				'value' 		   => $default_method,
				'allow_null'	   => 0,
				'layout'		   => 'horizontal',
				'class'			   => 'payment-methods'
			) );

		}

		foreach( $field['payment_methods'] as $index => $method ){ 

			$wrap_args = array(
				'class' => 'payment-method',
				'data-type' => $method,
			);
			if( $index > 0 ) $wrap_args['class'] .= ' '. $hide_methods; 
			?>
			<div <?php echo feadmin_get_esc_attrs( $wrap_args ); ?> >
				<?php $this->display_method( $method, $field ); ?>
			</div>
		<?php }
	}
	
	function card_skeleton(){
		?>
			<div class="card-skeleton">
				<div class="jp-card-container"><div class="jp-card"><div class="jp-card-front"><div class="jp-card-logo jp-card-elo"><div class="e">e</div><div class="l">l</div><div class="o">o</div></div><div class="jp-card-logo jp-card-visa">Visa</div><div class="jp-card-lower"><div class="jp-card-shiny"></div><div class="jp-card-cvc jp-card-display jp-card-invalid">•••</div><div class="jp-card-number jp-card-display jp-card-invalid">•••• •••• •••• ••••</div><div class="jp-card-name jp-card-display jp-card-invalid">Full Name</div><div class="jp-card-expiry jp-card-display jp-card-invalid" data-before="mm/yyyy" data-after="valid
				date">••/••</div></div></div>
				</div></div>
			</div>
		<?php
	}

	function display_method( $method, $field ){
		if( $method == 'credit_card' ){
			?>
			<div class="card-wrapper">
				<?php $this->card_skeleton(); ?>
			</div>
			<?php 	
			if( ! empty( $field['credit_card_fields'] ) ){
				echo '<div class="fea-cc-fields">';
				foreach( $field['credit_card_fields'] as $cc_field ){
					if( empty( $cc_field['name'] ) ) continue;

					$attrs = array(
						'type' => 'text',
						'class' => $cc_field['name'],
						'placeholder' => $cc_field['placeholder'],
						'data-stripe' => $cc_field['name'],
					);

					echo '<div class="acf-label" style="flex-basis:'. esc_attr( $cc_field['width'] ) .'%;"><label>'. esc_html( $cc_field['label'] );
					acf_text_input( $attrs );
					echo '</label></div>';
				}
				echo '</div>';
				$button_text = ! empty( $field['paypal_button_text'] ) ? $field['paypal_button_text'] : __('Pay Now','acf-frontend-form-element');
				?>
				<button type="button" class="credit-card-button"><?php esc_html_e( $button_text ); ?></button>
				<span class="fea-loader acf-hidden"></span>
				<?php
			}
			
		}
		if( 'paypal' == $method ){
			$button_text = ! empty( $field['paypal_button_text'] ) ? $field['paypal_button_text'] : __('Pay Now','acf-frontend-form-element');
			?>
			<button type="button" class="paypal-button"><?php esc_html_e( $button_text ); ?></button>
			<?php
		}

		
	}



    	/*
	*  render_field_settings()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/
	
	function render_field_settings( $field ) {
		$input_prefix = 'acf_fields[' . $field['ID'] . ']';

		$gateways = array();

		if( ! empty( fea_instance()->stripe ) ) $gateways['credit_card'] = __('Credit Card','acf-frontend-form-element');
		if( ! empty( fea_instance()->paypal ) ) $gateways['paypal'] = __('Paypal','acf-frontend-form-element');	

		if( empty( $gateways ) ){
			acf_render_field_setting( $field, array(
				'label'			=> __( 'Methods', 'acf-frontend-form-element' ),
				'message'		=> __( 'No methods found. Please activate Stripe or Paypal.', 'acf-frontend-form-element' ),
				'type'			=> 'message',
				'name'			=> 'payment_methods',
			));
		}else{
			acf_render_field_setting( $field, array(
				'label'			=> __('Methods','acf-frontend-form-element'),
				'instructions'	=> '',
				'type'			=> 'select',
				'multiple'		=> 1,
				'ui'			=> 1,
				'name'			=> 'payment_methods',
				'choices'		=> $gateways,
			));
		}
		acf_render_field_setting( $field, array(
			'label'			=> __('Credit Card Label','acf-frontend-form-element'),
			'placeholder'	=> __('Credit Card','acf-frontend-form-element'),
			'instructions'	=> '',
			'prefix' => $input_prefix,
			'type'			=> 'text',
			'name'			=> 'credit_card_label',
			'conditions'	=> array(
				'field'		=> 'payment_methods',
				'operator'	=> '==contains',
				'value'		=> 'paypal'
			),
		));
		acf_render_field_setting( $field, array(
			'label'			=> __('Paypal Label','acf-frontend-form-element'),
			'placeholder'	=> __('Paypal','acf-frontend-form-element'),
			'instructions'	=> '',
			'prefix' => $input_prefix,
			'type'			=> 'text',
			'name'			=> 'paypal_label',
			'conditions'	=> array(
				'field'		=> 'payment_methods',
				'operator'	=> '==contains',
				'value'		=> 'paypal'
			),
		));


		acf_render_field_setting( $field, array(
			'label' => __( 'Credit Card Fields', 'acf-frontend-form-element' ),
			'name' => 'credit_card_fields',
			'type' => 'list_items',
			'prefix' => $input_prefix,
			'show_add' => false,
			'show_remove' => false,
			'show_order' => 'ids',
			'conditions'	=> array(
				'field'		=> 'payment_methods',
				'operator'	=> '==contains',
				'value'		=> 'credit_card'
			),
			'sub_fields' => array(
				array(
					'key' => 'name',
					'name' => 'name',
					'type' => 'text',
					'frontend_admin_display_mode' => 'hidden'
				),
				array(
					'key' => 'id',
					'name' => 'id',
					'type' => 'text',
					'frontend_admin_display_mode' => 'hidden'
				),
				array(
					'key' => 'label',
					'name' => 'label',
					'label' => __( 'Label', 'acf-frontend-form-element' ),
					'type' => 'text'
				),
				array(
					'key' => 'placeholder',
					'name' => 'placeholder',
					'label' => __( 'Placeholder', 'acf-frontend-form-element' ),
					'type' => 'text'
				),  
				array(
					'key' => 'width',
					'name' => 'width',
					'label' => __( 'Width', 'acf-frontend-form-element' ),
					'prepend' => '%',
					'type' => 'number'
				),  
			),
		) );
	
		acf_render_field_setting( $field, array(
			'label'			=> __('Pay Button Text','acf-frontend-form-element'),
			'instructions'	=> '',
			'type'			=> 'text',
			'placeholder'   => __('Pay Now','acf-frontend-form-element'),
			'name'			=> 'paypal_button_text',
		) );
		acf_render_field_setting( $field, array(
			'label'			=> __('When user has already paid...','acf-frontend-form-element'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'already_paid',
			'choices'		=> array(
				'nothing'	=> __('Show Nothing','acf-frontend-form-element'),
				'message'	=> __('Show Message','acf-frontend-form-element'),
			)
		));

		acf_render_field_setting( $field, array(
			'label'			=> __('Product','acf-frontend-form-element'),
			'instructions'	=> __( 'What will the user receive?', 'frontend-admin' ),
			'type'			=> 'select',
			'allow_null'    => true,
			'name'			=> 'product',
			'choices'		=> array(
				'submission'	=> __('Form Submission','acf-frontend-form-element'),
				'plan'	=> __('Plan','acf-frontend-form-element'),
			)
		) );

		acf_render_field_setting( $field, array(
			'label'			=> __('Plan','acf-frontend-form-element'),
			'instructions'	=> '',
			'type'			=> 'fea_plans',
			'allow_null'    => true,
			'name'			=> 'plan',
			'placeholder'	=> __( 'Choose Plan', 'acf-frontend-form-element' ),
			'conditions'	=> array(
				array(
					'field'		=> 'product',
					'operator'	=> '==',
					'value'		=> 'plan'
				)
			),
			'add_plan'		=> true,
			'edit_plans'	=> true
		));		
	
		acf_render_field_setting( $field, array(
			'label'			=> __('Plan Message','acf-frontend-form-element'),
			'instructions'	=> sprintf( __( 'Use %s to display the remaining submissions left for the current user.', 'acf-frontend-form-element' ), '[remaining_submits]' ),
			'rows'			=> 3,
			'default_value' => sprintf( __( 'You still have %s submissions left.', 'acf-frontend-form-element' ), '[remaining_submits]' ),
			'type'			=> 'textarea',
			'name'			=> 'already_paid_message',
			'conditions'	=> array(
				array(
					'field'		=> 'already_paid',
					'operator'	=> '==',
					'value'		=> 'message'
				),
				array(
					'field'		=> 'product',
					'operator'	=> '==',
					'value'		=> 'plan'
				)
			)
		));		
		
		acf_render_field_setting( $field, array(
			'label'			=> __('Payment Description','acf-frontend-form-element'),
			'instructions'	=> __('Description of payment for the payments list','acf-frontend-form-element'),
			'type'			=> 'text',
			'name'			=> 'payment_description',
		));
		acf_render_field_setting( $field, array(
			'label'			=> __('Amount to Charge','acf-frontend-form-element'),
			'instructions'	=> '',
			'type'			=> 'number',
			'name'			=> 'payment_amount',
			'default_value' => 1,
			'min'			=> '0.1',
			'conditions'	=> array(
				'field'		=> 'product',
				'operator'	=> '==',
				'value'		=> 'submission'
			)
		));
		acf_render_field_setting( $field, array(
			'label'			=> __('Currency','acf-frontend-form-element'),
			'instructions'	=> '',
			'type'			=> 'select',
			'default_value' => 'USD',
			'name'			=> 'payment_currency',
			'choices' 		=> feadmin_get_stripe_currencies(),
			'conditions'	=> array(
				'field'		=> 'product',
				'operator'	=> '==',
				'value'		=> 'submission'
			)
		) );
		acf_render_field_setting( $field, array(
			'label'			=> __('Submit Form on Payment','acf-frontend-form-element'),
			'instructions'	=> '',
			'type'			=> 'true_false',
			'name'			=> 'submit_form',
			'ui' 			=> 1
		));
		
    }

}

acf_register_field_type( 'Frontend_Admin\Field_Types\payment_form' );

endif;
	
?>