<?php
namespace Frontend_Admin\Field_Types;

if ( ! class_exists( 'site_tagline' ) ) :

	class site_tagline extends text {



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
			$this->name     = 'site_tagline';
			$this->label    = __( 'Site Tagline', 'acf-frontend-form-element' );
			$this->category = __( 'Site', 'acf-frontend-form-element' );
			$this->defaults = array(
				'default_value' => '',
				'maxlength'     => '',
				'placeholder'   => '',
				'prepend'       => '',
				'append'        => '',
			);
			add_filter( 'acf/load_field/type=text', array( $this, 'load_site_tagline_field' ) );
			add_filter( 'acf/pre_update_value/type=' . $this->name, array( $this, 'pre_update_value' ), 9, 4 );

		}

		function load_site_tagline_field( $field ) {
			if ( ! empty( $field['custom_site_tagline'] ) ) {
				$field['type'] = 'site_tagline';
			}
			return $field;
		}

		public function load_value( $value, $post_id = false, $field = false ) {
			$value = get_option( 'blogdescription' );
			return $value;
		}

		function load_field( $field ) {
			 $field['name'] = $field['type'];
			return $field;
		}
		function pre_update_value( $checked, $value, $post_id, $field ) {
			if( $this->name !== $field['type'] ){
				return $checked;
			} update_option( 'blogdescription', $value );
			return true;
		}

		public function update_value( $value, $post_id = false, $field = false ) {
			return null;
		}

		public function prepare_field( $field ) {
			$field['type'] = 'text';
			// return
			return $field;
		}

	}



endif;


