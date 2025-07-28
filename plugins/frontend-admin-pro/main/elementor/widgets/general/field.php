<?php

namespace Frontend_Admin\Elementor\Widgets;

use  Elementor\Controls_Manager;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
	// Exit if accessed directly
}

/**

 *
 * @since 1.0.0
 */
class Field extends \Elementor\Widget_Base {

	public $form_defaults;
	/**
	 * Get widget name.
	 *
	 * Retrieve acf ele form widget name.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'fea_fields';
	}

	/**
	 * Check if the widget is dynamic.
	 *
	 * @since  1.0.0
	 * @access protected
	 *
	 * @return bool True if the widget is dynamic, false otherwise.
	 */
	protected function is_dynamic_content(): bool {
		return true;
	}


	/**
	 * Get widget title.
	 *
	 * Retrieve acf ele form widget title.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'ACF Fields', 'acf-frontend-form-element' );
	}

	 /**
	  * Get widget icon.
	  *
	  * Retrieve acf ele form widget icon.
	  *
	  * @since  1.0.0
	  * @access public
	  *
	  * @return string Widget icon.
	  */
	public function get_icon() {
		return 'eicon-form-horizontal frontend-icon';
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @since  2.1.0
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return array(
			'frontend editing',
			'fields',
			'acf',
			'acf form',
		);
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the acf ele form widget belongs to.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return array( 'frontend-admin-fields' );
	}

	/**
	 * Register acf ele form widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function register_controls() {
		$this->register_form_structure_controls();

		$this->register_style_tab_controls();

		do_action( 'frontend_admin/styles_controls', $this );

	}


	protected function register_form_structure_controls() {
		$this->start_controls_section(
			'fields_section',
			array(
				'label' => __( 'Field', 'acf-frontend-form-element' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);
		

		$this->add_control(
			'choose_from',
			array(
				'label'       => __( 'Choose From', 'acf-frontend-form-element' ),
				'type'        => Controls_Manager::SELECT,
				'label_block' => true,
				'options'     => array(
					'field_group' => __( 'Field Group', 'acf-frontend-form-element' ),
					'field'      => __( 'Field', 'acf-frontend-form-element' ),
				),
				'default'     => 'field_group',
			)
		);


			$this->add_control(
				'acf_field_group',
				array(
					'label'       => __( 'Select Field Group', 'acf-frontend-form-element' ),
					'type'        => 'fea_select',
					'label_block' => true,
					'action'	  => 'acf-field-groups',
					'options'     => [],
					'default'     => '',
					'multiple'    => true,
					'condition'   => array(
						'choose_from' => 'field_group',
					),
				)
			);

			$this->add_control(
				'acf_field',
				array(
					'label'       => __( 'Select Field', 'acf-frontend-form-element' ),
					'type'        => 'fea_select',
					'label_block' => true,
					'action'	  => 'acf-fields',
					'options'     => [],
					'default'     => '',
					'multiple'    => true,
					'condition'   => array(
						'choose_from' => 'field',
					),
				)
			);

			//display mode
			$this->add_control(
				'field_display_mode',
				array(
					'label'   => __( 'Display Mode', 'acf-frontend-form-element' ),
					'type'    => Controls_Manager::SELECT,
					'default' => 'edit',
					'options' => array(
						'edit'	=> __( 'Edit', 'acf-frontend-form-element' ),
						'read_only'	=> __( 'Read Only', 'acf-frontend-form-element' ),
						'hidden'	=> __( 'Hidden', 'acf-frontend-form-element' ),
					)
				)
			);
	
			//if read only, add "allow edit" option
			$this->add_control(
				'field_inline_edit',
				array(
					'label'        => __( 'Inline Edit', 'acf-frontend-form-element' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => __( 'Yes', 'acf-frontend-form-element' ),
					'label_off'    => __( 'No', 'acf-frontend-form-element' ),
					'return_value' => 'true',
					'default'      => '',
					'condition'    => array(
						'field_display_mode' => 'read_only',
					),
				)
			);
	
			//no value placeholder textarea
			$this->add_control(
				'no_values_message',
				array(
					'label'       => __( 'No Value Message', 'acf-frontend-form-element' ),
					'type'        => Controls_Manager::TEXTAREA,
					'label_block' => true,
					'placeholder' => __( 'Undefined Value', 'acf-frontend-form-element' ),
					'dynamic'     => array(
						'active' => true,
					),
					'condition'   => array(
						'field_display_mode' => 'read_only',
					),
				)
			);
		

			$this->custom_fields_control();
		
		$this->end_controls_section();

	}

	
	public function custom_fields_control( $repeater = false ) {
		$cf_save = 'post';
		if ( $this->get_name() != 'acf_ele_form' ) {
			$cf_save = str_replace( array( 'new_', 'edit_', 'duplicate_' ), '', $this->get_name() );
		}
		$controls_settings = array(
			'label'     => __( 'Save Custom Fields to...', 'acf-frontend-form-element' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => $cf_save,

		);

		$custom_fields_options = array(
			'submission' => __( 'Submission', 'acf-frontend-form-element' ),
			'post' => __( 'Post', 'acf-frontend-form-element' ),
			'user' => __( 'User', 'acf-frontend-form-element' ),
			'term' => __( 'Term', 'acf-frontend-form-element' ),
		);
		if ( isset( fea_instance()->pro_features ) ) {
			$custom_fields_options['options'] = __( 'Site Options', 'acf-frontend-form-element' );
			if ( class_exists( 'woocommerce' ) ) {
				$custom_fields_options['product'] = __( 'Product', 'acf-frontend-form-element' );
			}
		}
		$controls_settings['options'] = $custom_fields_options;
		$this->add_control( 'custom_fields_save', $controls_settings );

	}


	public function register_style_tab_controls() {
		if ( ! isset( fea_instance()->pro_features ) ) {

			$this->start_controls_section(
				'style_promo_section',
				array(
					'label' => __( 'Styles', 'acf-frontend-form-element' ),
					'tab'   => Controls_Manager::TAB_STYLE,
				)
			);

			$this->add_control(
				'styles_promo',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => __( '<p><a target="_blank" href="https://www.dynamiapps.com/"><b>Go Pro</b></a> to unlock styles.</p>', 'acf-frontend-form-element' ),
					'content_classes' => 'acf-fields-note',
				)
			);

			$this->end_controls_section();

		} else {
			do_action( 'frontend_admin/style_tab_settings', $this );
		}
	}

	

	/**
	 * Render fields widget output on the frontend.
	 *
	 * @since  1.0.0
	 * @access protected
	 */
	protected function render() {	

		global $fea_form, $fea_instance;

		$wg_id = $this->get_id();
		
		$settings = $this->get_settings_for_display();
		


		$chosen = $settings['choose_from'];

		if( 'field' == $chosen ){
			$field_ids = $settings['acf_field'] ?? [];

			if( is_string( $field_ids ) ){
				$field_ids = explode( ',', $field_ids );
			}

			foreach( $field_ids as $field_id ){
				$field = acf_get_field( $field_id );

				$this->render_field( $field, $settings );
			}
			return;
		}

		if( $chosen == 'field_group' ){
			$form_ids = $settings['acf_field_group'] ?? [];
		}

		foreach( $form_ids as $form_id ){
			$fields = acf_get_fields( $form_id );
			if( $fields ){
				foreach( $fields as $field ){
					if( false === $field ) continue; 
					$this->render_field( $field, $settings );
				}
			}
		}

	}

	public function render_field( $field, $settings ){
		global $fea_form, $fea_instance;
		$form_display = $fea_instance->form_display;

		if( $settings['field_display_mode'] == 'read_only' ){
			$field['frontend_admin_display_mode'] = 'read_only';
			$field['with_edit'] = $settings['field_inline_edit'];
			$field['no_values_message'] = $field['no_values_message'] ?? $settings['no_values_message'];
		}

		$field = $this->prepare_field( $field );
		if( ! $field ) return;
		if( $settings['field_display_mode'] == 'read_only' ){
			echo $fea_instance->dynamic_values->render_field_display( $field );
		}else{
			$form_display->render_field_wrap( $field );
			
			$fea_form['rendered_field'] = true;
		}
	}

	public function prepare_field( $field ){
		if( ! $field ) return false;
		global $fea_form, $fea_instance;
		$form_display = $fea_instance->form_display;

		$field['builder'] = 'elementor';
		
		$field = $form_display->get_field_data_type( $field, $fea_form );
				
		if( ! $field ) return false;

		if ( ! isset( $field['value'] )
			|| $field['value'] === null
		) {
			$field = $form_display->get_field_value( $field, $fea_form );
		}
		
		return $field;
	}


}
