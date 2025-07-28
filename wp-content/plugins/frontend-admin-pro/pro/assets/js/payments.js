(function ($) {
	
	var Field = acf.Field.extend({
		
		type: 'payment_form',
				
		wait: 'load',
		
		events: {
			'click .paypal-button': 'onClickPaypalButton',
			'click .credit-card-button:not(disabled)': 'onClickCreditCardButton',
			'click .payment-methods input': 'onClickPaymentMethods',
		},

		$input: function(){
			return this.$('input.payment-input');
		},
		$control: function(){
			return this.$('.frontend-admin-payment');
		},
		$remainingSubmits: function(){
			return this.$('.remaining-submits');
		},
		$form: function(){
			return this.$el.parents('form');
		},

		addSuccess: function(){
			this.$input().trigger('change');
			this.showNotice({
				text: acf.__('Payment Successful...'),
				type: 'success'
			});
			
			this.$control().addClass('paid');
			

			/* if( this.$control().data('submit') ){
				args = {
					form: this.$form(),
					reset: false,
					complete: acf.submitFrontendForm,
				}
				acf.validateFrontendForm(args);
			}  */
		},
		requestHandler: function(method,card) {	
			var formData = new FormData(this.$form()[0]);
	
			paymentData = {
				//payment_plan: this.$input().val(),
				field: this.get('key'),
				feap_nonce: payment_vars.cc_nonce,
				method: method,
				action: 'frontend_admin/payments/new_payment',
			};        		
	
			for ( var key in paymentData ) {
				formData.append(key,paymentData[key]);
			}
			if(typeof card != 'undefined'){
				formData.append('card',card);
			}

			if( 'paypal' == method ){
				formData.append('page_url',window.location.href);
				paypalWindow = this.paypalPopUp(600,600);
				paypalWindow.document.write(acf.__('Loading Paypal...'));
			}
			
	
			var field = this;
			$.ajax({
				url: acf.get('ajaxurl'),
				type: 'post',
				data: formData,
				cache: false,
				processData: false,
				contentType: false,
				success: function(result){
					if( result.success ){
						if( result.data.redirect ){
							acf.unload.reset();
							localStorage.setItem("feaToken-"+field.get('key'), "0")
							localStorage.setItem("feaToken-"+field.get('key'), "0")
							paypalWindow.location = result.data.redirect;
						}else{
							if( result.data.payment ){
								field.$input().val(result.data.payment);
							}
							field.addSuccess();
						}
					}else{
						field.showNotice({
							text: result.data.message,
							type: 'warning'
						});
						field.$('button.disabled').removeClass('disabled').next('.fea-loader').addClass('acf-hidden');
					}
					return null;
				},   
			});
			
		},

		paypalPopUp: function(width, height) {
			var leftPosition, topPosition;
			//Allow for borders.
			leftPosition = (window.screen.width / 2) - ((width / 2) + 10);
			//Allow for title and status bars.
			topPosition = (window.screen.height / 2) - ((height / 2) + 50);
			//Open the window.
			return window.open('', 'paypalWindow',
			"status=no,height=" + height + ",width=" + width + ",resizable=yes,left="
			+ leftPosition + ",top=" + topPosition + ",screenX=" + leftPosition + ",screenY="
			+ topPosition + ",toolbar=no,menubar=no,scrollbars=no,location=no,directories=no");
		},

		onClickPaymentMethods: function(e,$el){
			// vars
			var $label = $el.parent('label');
			var selected = $label.hasClass('selected');
			
			// remove previous selected
			this.$('.selected').removeClass('selected');
			
			// add active class
			$label.addClass('selected');
			
			var methods = this.$('.payment-method');
			methods.addClass('frontend-admin-hidden');

			// allow null
			if( selected ) {
				$label.removeClass('selected');
				$el.prop('checked', false).trigger('change');
			}else{
				methods.each(function() {
					if($(this).data('type')==$el.val()){
						$(this).removeClass('frontend-admin-hidden');
					}                
				});
			}
			
		},
		onClickPaypalButton: function(e,$el){
			if(typeof paypalWindow != 'undefined' && ! paypalWindow.closed){
				paypalWindow.focus();
				return;
			}
			this.requestHandler('paypal');
		},

		onClickCreditCardButton: function(e,$el){	
			if(!$el.hasClass('disabled')){
				$el.addClass('disabled').next('.fea-loader').removeClass('acf-hidden');
			}

			if(payment_vars.stripe_spk == ''){
				this.showNotice({
					text: acf.__('Could not connect to Stripe. Please check your API keys.'),
					type: 'warning'
				});
				$el.removeClass('disabled').next('.fea-loader').addClass('acf-hidden');
				return;
			}else{
				if(typeof Stripe !== 'undefined'){
					Stripe.setPublishableKey(payment_vars.stripe_spk);
					if(this.$("input.stripeToken").length == 0){
						var field = this;
						Stripe.card.createToken(this.$form(), function(event, result){
							if (result.error) {
								field.showNotice({
									text: result.error.message,
									type: 'warning'
								});
								$el.removeClass('disabled').next('.fea-loader').addClass('acf-hidden');
								return;
							}else{			
								field.requestHandler('stripe',result.id);
							}
			
						});
					}
				} 
			}
		},

		showCard: function(){
			if( this.$('.card-wrapper').length ){
				var card = new Card({
					form: ".fea-cc-fields",
					container: '.card-wrapper',
					formSelectors: { numberInput: "input.number", expiryInput: "input.exp", cvcInput: "input.cvc", nameInput: "input.name" },
					width: 300,
					formatting: !0,
					messages: { validDate: "valid\ndate", monthYear: "mm/yyyy" },
					placeholders: { number: "•••• •••• •••• ••••", name: "Full Name", expiry: "••/••", cvc: "•••" },
					masks: { cardNumber: "•" },
					debug: !1,
				});       
			}
		},

		paypalComplete: function(){
			let field = this;
			let key = field.get('key');
			$(window).on('storage', function (e) {
				var storageEvent = e.originalEvent;

				if ((storageEvent.key == 'feaToken-'+key) && (storageEvent.oldValue == '0') && (storageEvent.newValue != '0')) {  
					field.$input().val(storageEvent.newValue);
					paypalWindow.close();
					field.addSuccess();
				}
			});
		},
		
		initialize: async function(){
			this.paypalComplete();

			await this.showCard();

			//remove .card-skeleton
			this.$('.card-skeleton').remove();
		},

		
	});
	
	acf.registerFieldType( Field );

})(jQuery);
