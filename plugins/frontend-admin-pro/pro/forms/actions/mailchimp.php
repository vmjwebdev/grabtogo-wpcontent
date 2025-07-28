<?php
namespace Frontend_Admin\Actions;

use Frontend_Admin\Plugin;
use Frontend_Admin;
use Frontend_Admin\Classes\ActionBase;
use Frontend_Admin\Forms\Actions;
use Elementor\Controls_Manager;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! class_exists( 'SendMailchimp' ) ) :

	class SendMailchimp extends ActionBase {


		public $site_domain = '';

		public function get_name() {
			return 'mailchimp';
		}

		public function get_label() {
			return __( 'Mailchimp', 'acf-frontend-form-element' );
		}


		public function action_options() {
			return array(
				array(
					'key'               => 'api_key',
					'label'             => __( 'Mailchimp API', 'acf-frontend-form-element' ),
					'name'              => 'mailchimp_api_key',
					'type'              => 'text',
					'instructions'      => __( 'Enter your Mailchimp API key.', 'acf-frontend-form-element' ),
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '70',
						'class' => '',
						'id'    => '',
					),
					'placeholder'       => __( 'Mailchimp API Key', 'acf-frontend-form-element' ),
					'default_value'     => get_option( 'frontend_admin_mailchimp_api', '' ),
					'prepend'           => '',
					'append'            => '',
					'maxlength'         => '',
				),
				array(
					'key'               => 'lists',
					'label'             => __( 'Mailchimp Lists/Audiences', 'acf-frontend-form-element' ),
					'name'              => 'mailchimp_lists',
					'type'              => 'select',
					'instructions'      => __( 'Enter your Mailchimp Lists/Audiences, comma seperated.', 'acf-frontend-form-element' ),
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'api_key',
								'operator' => '!=',
								'value'    => '',
							),
						),
					),
					'choices'           => array(),
					'wrapper'           => array(
						'width' => '70',
						'class' => '',
						'id'    => '',
					),
					'allow_null'        => 1,
					'multiple'          => 1,
					'ui'                => 1,
					// 'disabled' => 1,
					'ajax'              => 1,
					'ajax_action'       => 'frontend_admin/actions/mailchimp_lists/query',
					// 'after_input' => '<span class="fea-loader"></span>',
					'placeholder'       => __( 'Mailchimp Lists', 'acf-frontend-form-element' ),
				),
			);
		}

		public function register_settings_section( $widget ) {
			return;
		}

		public function run( $form ) {
			if ( ! empty( $form['submit_actions'] ) ) {
				$actions = $form['submit_actions'];
				if ( $actions ) {
					$mailchimps = array();
					foreach ( $actions as $action ) {
						if ( $action['fea_block_structure'] == 'mailchimp' ) {
							$mailchimps[] = $action;
						}
					}
				}
			}

			if ( empty( $mailchimps ) ) {
				return;
			}

			$record = $form['record'];

			$values = array( 'status' => 'subscribed' );

			if ( empty( $record['mailchimp'] ) ) {
				return;
			}

			if ( ! is_array( $record['mailchimp'] ) || empty( $record['mailchimp']['email'] ) ) {
				return;
			}
			foreach ( $record['mailchimp'] as $field_key => $mc_field ) {
				$mc_field = explode( ':', $mc_field );
				if ( isset( $mc_field[1] ) ) {
					$mcf_group = $mc_field[0];
					$mcf_name  = $mc_field[1];
					$mc_field  = $record['fields'][ $mcf_group ][ $mcf_name ];

					if ( $field_key == 'status' ) {
						if ( ! $mc_field['_input'] ) {
							$_field = acf_get_field( $mc_field['key'] );
							if ( ! $_field || empty( $_field['save_unsubscribed'] ) ) {
								return;
							}
							$values['status'] = 'unsubscribed';
						}
					} else {
						 $values[ $field_key ] = $mc_field['_input'];
					}
				}
			}

			if ( empty( $values['email'] ) ) {
				return;
			}

			foreach ( $mailchimps as $mailchimp ) {
				if ( empty( $mailchimp['api_key'] ) || empty( $mailchimp['lists'] ) ) {
					continue;
				}

				$api_key = $mailchimp['api_key'];
				$lists   = $mailchimp['lists'];

				/**
			 * Forms mailchimp request arguments.
			 *
			 * Filters the request arguments delivered by the form mailchimp when executing
			 * an ajax request.
			 *
			 * @since 1.0.0
			 *
			 * @param array    $record   The submission's recorded data sent through the mailchimp .
			 */

				// Create mailchimp API url
				$data_center = substr( $api_key, strpos( $api_key, '-' ) + 1 );

				// Member info
				$data = array(
					'email_address' => $values['email'],
					'status'        => $values['status'],
					'merge_fields'  => array(
						'FNAME' => '',
						'LNAME' => '',
					),
				);

				if ( isset( $values['first_name'] ) ) {
					$data['merge_fields']['FNAME'] = $values['first_name'];
				}
				if ( isset( $values['last_name'] ) ) {
					$data['merge_fields']['LNAME'] = $values['last_name'];
				}

				foreach ( $lists as $list ) {
					$data = apply_filters( 'frontend_admin/forms/mailchimp/request_data', $data, $form );

					$url    = 'https://' . $data_center . '.api.mailchimp.com/3.0/lists/' . $list . '/members';
					$result = json_decode( $this->curl_connect( $url, 'POST', $api_key, $data ) );

					$response = '';
					if ( isset( $result->status ) ) {
						if ( is_numeric( $result->status ) ) {
							switch ( $result->status ) {
								case 200:
									$response = __( 'Success, newsletter subcribed using mailchimp API', 'acf-frontend-form-element' );
									break;
								case 214:
									$response = __( 'Already Subscribed', 'acf-frontend-form-element' );
									break;
								default:
									$response = __( 'Oops, please try again.', 'acf-frontend-form-element' ) . '<br>[msg_code=' . $result->status . ']';
									break;
							}
							error_log( print_r( $result, true ) );
						}
					} else {
						error_log( print_r( $result, true ) );
					}

					do_action( 'frontend_admin/forms/mailchimp/response', $response, $list, $form );
				}
			}

		}

		function api_field( $field ) {
			if ( $field['value'] ) {
				global $api_key;
				$api_key = $field['value'];
			}
		}

		function load_lists( $field ) {
			if ( $field['value'] ) {
				global $api_key;
				if ( $api_key ) {
					$data = array(
						'fields' => 'id,name', // total_items, _links
					);
					foreach ( $field['value'] as $list_id ) {
						 $url    = 'https://' . substr( $api_key, strpos( $api_key, '-' ) + 1 ) . '.api.mailchimp.com/3.0/lists/' . $list_id;
						 $result = json_decode( $this->curl_connect( $url, 'GET', $api_key, $data ) );
						if ( ! empty( $result->id ) ) {
							$field['choices'][ $result->id ] = $result->name;
						}
					}
				}

				$api_key = '';
			}
			return $field;
		}

		function curl_connect( $url, $request_type, $api_key, $data = array() ) {
			if ( $request_type == 'GET' ) {
				$url .= '?' . http_build_query( $data );
			}

			$mch     = curl_init();
			$headers = array(
				'Content-Type: application/json',
				'Authorization: Basic ' . base64_encode( 'user:' . $api_key ),
			);
			curl_setopt( $mch, CURLOPT_URL, $url );
			curl_setopt( $mch, CURLOPT_HTTPHEADER, $headers );
			curl_setopt( $mch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $mch, CURLOPT_CUSTOMREQUEST, $request_type );
			curl_setopt( $mch, CURLOPT_TIMEOUT, 10 );
			curl_setopt( $mch, CURLOPT_SSL_VERIFYPEER, false );

			if ( $request_type != 'GET' ) {
				curl_setopt( $mch, CURLOPT_USERPWD, 'user:' . $api_key );
				curl_setopt( $mch, CURLOPT_POST, true );
				curl_setopt( $mch, CURLOPT_POSTFIELDS, json_encode( $data ) );
			}

			return curl_exec( $mch );
		}

		function ajax_query() {
			if ( ! feadmin_verify_ajax() ) {
				die();
			}

			// defaults
			$options = acf_parse_args(
				$_POST,
				array(
					'post_id' => 0,
					's'       => '',
					'api_key' => '',
					'paged'   => 1,
				)
			);

			if ( $options['api_key'] == '' ) {
				return;
			}

			$api_key = $options['api_key'];

			// Query String Perameters are here
			// for more reference please vizit http://developer.mailchimp.com/documentation/mailchimp/reference/lists/
			$data = array(
				'fields' => 'lists', // total_items, _links
				'count'  => 20, // the number of lists to return, default - all
				'offset' => ( $options['paged'] * 20 ) - 20,
			);

			$url    = 'https://' . substr( $api_key, strpos( $api_key, '-' ) + 1 ) . '.api.mailchimp.com/3.0/lists/';
			$result = json_decode( $this->curl_connect( $url, 'GET', $api_key, $data ) );

			if ( empty( $result->lists ) ) {
				return;
			}

			// vars
			$results = array();
			$s       = null;

			// search
			if ( $options['s'] !== '' ) {

				// strip slashes (search may be integer)
				$s = strval( $options['s'] );
				$s = wp_unslash( $s );

			}

			// loop
			foreach ( $result->lists as $list ) {

				// append
				$results[] = array(
					'id'   => $list->id,
					'text' => $list->name,
				);

			}

			// vars
			$response = array(
				'results' => $results,
			);

			acf_send_ajax_results( $response );

		}

		function api_setting( $fields ) {
			$fields['frontend_admin_mailchimp_api'] = array(
				'label'        => __( 'Mailchimp API Key', 'acf-frontend-form-element' ),
				'type'         => 'text',
				'instructions' => '',
				'required'     => 0,
				'wrapper'      => array(
					'width' => '50.1',
					'class' => '',
					'id'    => '',
				),
			);
			return $fields;
		}
		public function __construct() {
			 add_action( 'wp_ajax_frontend_admin/actions/mailchimp_lists/query', array( $this, 'ajax_query' ) );
			add_action( 'acf/render_field/name=mailchimp_api_key', array( $this, 'api_field' ) );
			add_action( 'acf/prepare_field/name=mailchimp_lists', array( $this, 'load_lists' ) );
			add_filter( 'frontend_admin/api_settings', array( $this, 'api_setting' ) );
		}

	}
	fea_instance()->remote_actions['mailchimp'] = new SendMailchimp();

endif;
