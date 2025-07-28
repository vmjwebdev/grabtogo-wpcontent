<?php
namespace Frontend_Admin\Field_Types;

if ( ! class_exists( 'countries' ) ) :

	class countries extends Field_Base {



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
			$this->name     = 'countries';
			$this->label    = __( 'Countries', 'acf-frontend-form-element' );
			$this->category = __( 'General', 'acf-frontend-form-element' );
			$this->defaults = array(
				'allow_null'      => 0,
				'multiple'        => 0,
				'ui'              => 1,
				'no_data_collect' => 1,
			);

			$this->json_url = 'https://raw.githubusercontent.com/russ666/all-countries-and-cities-json/master/countries.min.json';

			// extra
			add_action( 'wp_ajax_acf/fields/countries/query', array( $this, 'ajax_query' ) );
			add_action( 'wp_ajax_nopriv_acf/fields/countries/query', array( $this, 'ajax_query' ) );

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
				
			// load field
			$field = acf_get_field( $options['field_key'] );
			if ( ! $field ) {
				return false;
			}



			// vars
			$results = array();

			$countries = $this->get_countries();

			foreach( $countries as $country ){
				
				if( ! empty( $options['s'] ) && strpos( strtolower( $country ), strtolower( $options['s'] ) ) === false ){
					continue;
				}
				$results[] = array(
					'id'   => $country,
					'text' => $country,
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
		* get_countries

		*/

		function get_countries() {
			$countries = array("Afghanistan","Albania","Algeria","Andorra","Angola","Antigua and Barbuda","Argentina","Armenia","Aruba","Australia","Austria","Azerbaijan","Bahamas","Bahrain","Bangladesh","Barbados","Belarus","Belgium","Belize","Bolivia","Bosnia and Herzegovina","Botswana","Brazil","Brunei","Bulgaria","Cambodia","Cameroon","Canada","Cayman Islands","Chile","China","Colombia","Congo","Costa Rica","Croatia","Cuba","Cyprus","Czech Republic","Denmark","Dominican Republic","Ecuador","Egypt","El Salvador","Estonia","Faroe Islands","Finland","France","French Polynesia","Gabon","Georgia","Germany","Ghana","Greece","Greenland","Guadeloupe","Guam","Guatemala","Guinea","Haiti","Hashemite Kingdom of Jordan","Honduras","Hong Kong","Hungary","Iceland","India","Indonesia","Iran","Iraq","Ireland","Isle of Man","Israel","Italy","Jamaica","Japan","Kazakhstan","Kenya","Kosovo","Kuwait","Latvia","Lebanon","Libya","Liechtenstein","Luxembourg","Macedonia","Madagascar","Malaysia","Malta","Martinique","Mauritius","Mayotte","Mexico","Mongolia","Montenegro","Morocco","Mozambique","Myanmar [Burma]","Namibia","Nepal","Netherlands","New Caledonia","New Zealand","Nicaragua","Nigeria","Norway","Oman","Pakistan","Palestine","Panama","Papua New Guinea","Paraguay","Peru","Philippines","Poland","Portugal","Puerto Rico","Republic of Korea","Republic of Lithuania","Republic of Moldova","Romania","Russia","Saint Lucia","San Marino","Saudi Arabia","Senegal","Serbia","Singapore","Slovakia","Slovenia","South Africa","Spain","Sri Lanka","Sudan","Suriname","Swaziland","Sweden","Switzerland","Taiwan","Tanzania","Thailand","Trinidad and Tobago","Tunisia","Turkey","U.S. Virgin Islands","Ukraine","United Arab Emirates","United Kingdom","United States","Uruguay","Venezuela","Vietnam","Zambia","Zimbabwe");
			return $countries;
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
				$field['placeholder'] = __( 'Select Country', 'acf-frontend-form-element' );
			}

			// Change Field into a select
			$field['allow_null'] = 1;
			$field['type']       = 'select';
			$field['ui']         = 1;
			$field['ajax']       = 1;
			

			if( $field['value'] ){
				$field['choices'] = array(
					$field['value'] => $field['value'],
				);
			}else{
				$field['choices'] = [];
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
					'placeholder'  => __( 'Select Country', 'acf-frontend-form-element' ),
				)
			);
	

		}




	}




endif; // class_exists check


