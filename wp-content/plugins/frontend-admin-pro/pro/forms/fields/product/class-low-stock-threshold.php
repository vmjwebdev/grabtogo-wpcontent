<?php
namespace Frontend_Admin\Field_Types;

if ( ! class_exists( 'low_stock_threshold' ) ) :

	class low_stock_threshold extends number {



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
			$this->name     = 'low_stock_threshold';
			$this->label    = __( 'Low Stock Threshold', 'acf-frontend-form-element' );
			$this->category = __( 'Product Inventory', 'acf-frontend-form-element' );
			$this->defaults = array(
				'default_value' => '',
				'min'           => '0',
				'max'           => '',
				'step'          => '',
				'placeholder'   => '',
				'prepend'       => '',
				'append'        => '',
			);
			add_filter( 'acf/load_field/type=number', array( $this, 'load_low_stock_threshold_field' ), 2 );
			add_filter( 'acf/pre_update_value/type=' . $this->name, array( $this, 'pre_update_value' ), 9, 4 );
		}

		function load_low_stock_threshold_field( $field ) {
			if ( ! empty( $field['custom_low_stock'] ) ) {
				$field['type'] = 'low_stock_threshold';
			}
			return $field;
		}

		function prepare_field( $field ) {
			if ( isset( $GLOBALS['form_fields'] ) ) {
				$fields                     = $GLOBALS['form_fields'];
				$field['conditional_logic'] = array(
					array(
						array(
							'field'    => $fields['product_types'],
							'operator' => '==',
							'value'    => 'simple',
						),
						array(
							'field'    => $fields['manage_stock'],
							'operator' => '==',
							'value'    => '1',
						),
					),
					array(
						array(
							'field'    => $fields['product_types'],
							'operator' => '==',
							'value'    => 'variable',
						),
						array(
							'field'    => $fields['manage_stock'],
							'operator' => '==',
							'value'    => '1',
						),
					),
				);
			}
			$field['type'] = 'number';

			return $field;
		}

		public function load_value( $value, $post_id = false, $field = false ) {
			$value = get_post_meta( $post_id, '_low_stock_amount', true );
			return $value;
		}

		function load_field( $field ) {
			 $field['name'] = $field['type'];
			return $field;
		}
		function pre_update_value( $checked, $value, $post_id, $field ) {
			if( $this->name !== $field['type'] ){
				return $checked;
			} update_metadata( 'post', $post_id, '_low_stock_amount', $value );
			return true;
		}

		public function update_value( $value, $post_id = false, $field = false ) {
			return null;
		}

	}



endif; // class_exists check


