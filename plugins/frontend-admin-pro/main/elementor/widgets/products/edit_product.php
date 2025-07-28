<?php
namespace Frontend_Admin\Elementor\Widgets;





/**

 *
 * @since 1.0.0
 */
class Edit_Product_Widget extends ACF_Form {

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
		return __( 'Edit Product Form', 'acf-frontend-form-element' );
	}

		/**
	 * Get widget defaults.
	 *
	 * Retrieve acf form widget defaults.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return string Widget defaults.
	 */
	public function get_form_defaults() {
		return array(
			'custom_fields_save' => 'product',
			'form_title'         => '',
			'submit'             => __( 'Submit', 'acf-frontend-form-element' ),
			'success_message'    => __( 'Your product has been edited successfully.', 'acf-frontend-form-element' ),
			'field_type'         => 'title',
			'save_to_product'  => 'edit_product',
			'fields'             => [
				[ 'field_type' => 'product_title', 'field_label' => 'Title' ],
				[ 'field_type' => 'description', 'field_label' => 'Description' ],
				[ 'field_type' => 'price', 'field_label' => 'Price' ],
				[ 'field_type' => 'main_image', 'field_label' => 'Image' ],
			]
		);
	}


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
		return 'edit_product';
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
		return array( 'frontend-admin-products' );
	}


}
