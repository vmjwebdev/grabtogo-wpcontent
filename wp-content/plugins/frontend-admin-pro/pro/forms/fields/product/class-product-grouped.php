<?php
namespace Frontend_Admin\Field_Types;

if ( ! class_exists( 'product_grouped' ) ) :

	class product_grouped extends product_linked {



		  /*
		  *  __construct
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
			$this->name     = 'product_grouped';
			$this->label    = __( 'Grouped Products', 'acf-frontend-form-element' );
			$this->category = __( 'Linked Products', 'acf-frontend-form-element' );
			$this->defaults = array(
				'post_type'       => array( 'product' ),
				'taxonomy'        => array(),
				'exclude_current' => 1,
				'min'             => 0,
				'max'             => 0,
				'filters'         => array( 'search', 'taxonomy' ),
				'elements'        => array(),
				'return_format'   => 'object',
				'add_edit_post'   => 0,
				'add_post_button' => __( 'Add Product', 'acf-frontend-form-element' ),
				'form_width'      => 600,
			);
			add_filter( 'acf/pre_update_value/type=' . $this->name, array( $this, 'pre_update_value' ), 9, 4 );
		}

		function prepare_field( $field ) {
			if ( isset( $GLOBALS['form_fields'] ) ) {
				$fields = $GLOBALS['form_fields'];

				  $field['conditional_logic'] = array(
					  array(
						  array(
							  'field'    => $fields['product_types'],
							  'operator' => '==',
							  'value'    => 'grouped',
						  ),
					  ),
				  );
			}

			return $field;
		}

		function load_value( $value, $post_id, $field ) {
			$product = wc_get_product( $post_id );

			if ( $product ) {
				  $value = $product->get_children( 'edit' );
			}

			return $value;
		}

				function pre_update_value( $checked, $value, $post_id, $field ) {
			if( $this->name !== $field['type'] ){
				return $checked;
			}
			
			if ( empty( $post_id ) || ! is_numeric( $post_id ) ) {
				return true;
			}

			$product = wc_get_product( $post_id );

			if ( $product ) {
				  $product->set_children( $value );
				  $product->save();
			}
			return true;

		}
		function update_value( $value, $post_id, $field ) {
			   return null;
		}


	}



endif; // class_exists check


