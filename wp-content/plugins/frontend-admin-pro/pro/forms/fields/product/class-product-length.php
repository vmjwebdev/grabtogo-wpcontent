<?php
namespace Frontend_Admin\Field_Types;

if ( ! class_exists( 'product_length' ) ) :

	class product_length extends shipping_attributes {



		  /*
		  *  initialize
		  *
		  *  This function will setup the field type data
		  *
		  *  @type      function
		  *  @date      5/03/2014
		  *  @since      5.0.0
		  *
		  *  @param      n/a
		  *  @return      n/a
		  */

		function initialize() {
			// vars
			$this->name     = 'product_length';
			$this->label    = __( 'Length', 'acf-frontend-form-element' );
			$this->category = __( 'Product Shipping', 'acf-frontend-form-element' );
			$this->attr     = 'length';
			$this->defaults = array(
				'default_value' => '',
				'min'           => '0',
				'max'           => '',
				'step'          => '0.01',
				'placeholder'   => '',
				'prepend'       => '',
				'append'        => '',
			);
			add_filter( 'acf/pre_update_value/type=' . $this->name, array( $this, 'pre_update_value' ), 9, 4 );
		}

	}



endif; // class_exists check


