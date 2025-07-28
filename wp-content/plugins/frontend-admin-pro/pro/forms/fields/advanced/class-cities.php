<?php
namespace Frontend_Admin\Field_Types;

if ( ! class_exists( 'cities' ) ) :

	class cities extends Field_Base {



		/*
		*  __construct
		*
		*  This function will setup the field type data
		*
		*  @type    function
		*  @date    5/03/2014
		*  @since   5.0.0
		*
		*  @param   n/a
		*  @return  n/a
		*/

		function initialize() {
			// vars
			$this->name     = 'cities';
			$this->label    = __( 'Cities', 'acf-frontend-form-element' );
			$this->category = __( 'General', 'acf-frontend-form-element' );
			$this->defaults = array(
				'allow_null'      => 0,
				'multiple'        => 0,
				'ui'              => 1,
				'no_data_collect' => 1,
			);

			$this->json_url = 'https://raw.githubusercontent.com/russ666/all-countries-and-cities-json/master/countries.min.json';

			// extra
			add_action( 'wp_ajax_acf/fields/cities/query', array( $this, 'ajax_query' ) );
			add_action( 'wp_ajax_nopriv_acf/fields/cities/query', array( $this, 'ajax_query' ) );

		}


		/*
		*  ajax_query
		*
		*  description
		*
		*  @type    function
		*  @date    24/10/13
		*  @since   5.0.0
		*
		*  @param   $post_id (int)
		*  @return  $post_id (int)
		*/

		function ajax_query() {
			// validate
			if ( ! feadmin_verify_ajax() ) {
				die();
			}

			// get choices
			$response = $this->get_ajax_query( $_POST );

			// return
			acf_send_ajax_results( $response );

		}


		/*
		*  get_ajax_query
		*
		*  This function will return an array of data formatted for use in a select2 AJAX response
		*
		*  @type    function
		*  @date    15/10/2014
		*  @since   5.0.9
		*
		*  @param   $options (array)
		*  @return  (array)
		*/

		function get_ajax_query( $options = array() ) {	

			if( empty( $options['countries'] ) ){
				return false;
			}

			// load field
			$field = acf_get_field( $options['field_key'] );
			if ( ! $field ) {
				return false;
			}



			// vars
			$results = array();

			if( $this->json_url ){
				$countries = file_get_contents( $this->json_url );
				
				$countries = json_decode( $countries, true );
			}else{
				return false;
			}

			foreach( $countries as $country => $cities ){
				if( ! in_array( $country, $options['countries'] ) ){
					continue;
				}
				$country_cities = [];
				foreach( $cities as $city ){
					if( ! empty( $options['s'] ) && strpos( strtolower( $city ), strtolower( $options['s'] ) ) === false ){
						continue;
					}
					$country_cities[] = array(
						'id'   => $city,
						'text' => $city,
					);
				}
				$results[] = array(
					'id'   => $country,
					'children' => $country_cities,
				);
				
			}

			// vars
			$response = array(
				'results' => $results,
				'limit'   => 0,
			);

			// return
			return $response;

		}






		/*
		*  render_field()
		*
		*  Create the HTML interface for your field
		*
		*  @param   $field - an array holding all the field's data
		*
		*  @type    action
		*  @since   3.6
		*  @date    23/01/13
		*/

		function render_field( $field ) {
			if ( empty( $field['placeholder'] ) ) {
				$field['placeholder'] = __( 'Select City', 'acf-frontend-form-element' );
			}

			// Change Field into a select
			$field['allow_null'] = 1;
			$field['type']       = 'select';
			$field['ui']         = 1;
			$field['ajax']       = 1;
			
			$field['choices'] = [];

			if( ! $field['value'] ){
				$field['disabled'] = 'true';
			}else{
				$field['choices'] = array(
					$field['value'] => $field['value'],
				);
			}


			// render
			acf_render_field( $field );


		}


		/*
		*  render_field_settings()
		*
		*  Create extra options for your field. This is rendered when editing a field.
		*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
		*
		*  @type    action
		*  @since   3.6
		*  @date    23/01/13
		*
		*  @param   $field  - an array holding all the field's data
		*/

		function render_field_settings( $field ) {
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Placeholder', 'acf-frontend-form-element' ),
					'instructions' => '',
					'name'         => 'placeholder',
					'type'         => 'text',
					'placeholder'  => __( 'Select City', 'acf-frontend-form-element' ),
				)
			);



		}


	}




endif; // class_exists check


