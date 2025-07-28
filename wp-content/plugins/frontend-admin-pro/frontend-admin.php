<?php

/**
 * Plugin Name: Frontend Admin (Premium)
 * Plugin URI: https://wordpress.org/plugins/acf-frontend-form-element/
 * Description: This awesome plugin allows you to easily display admin forms to the frontend of your site so your clients can easily edit content on their own from the frontend.
 * Version:     3.24.8

 * Update URI: https://api.freemius.com
 * Author:      Shabti Kaplan
 * Author URI:  https://www.dynamiapps.com/
 * Text Domain: acf-frontend-form-element
 * Domain Path: /languages/
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class MockFreemius {
    public function __call( $name, $arguments ) {
        // Check if the method name matches a pattern or specific method name.
        if ($name === 'is__premium_only') {
            return true; // Assuming is__premium_only should return true.
        }

        // Default behavior for other methods.
        return true;
    }

    // Define properties for direct property access.
    public $is_premium = true;
    public $is_paying = true;
    public $is_trial = false;
    public $has_active_license = true;
}
function fea_freemius() {
    global $fea_freemius;
    if (isset($fea_freemius)) {
        return $fea_freemius;
    }

    // Use the MockFreemius class.
    $fea_freemius = new MockFreemius();

    return $fea_freemius;
}
if ( !function_exists( 'feap_fs' ) ) {
    function feap_fs() {
        return false;
    }

}
if ( !class_exists( 'Front_End_Admin_Pro' ) ) {
    if ( !function_exists( 'fea_freemius' ) ) {
        function fea_freemius() {
            global $fea_freemius;
            if ( isset( $fea_freemius ) ) {
                return $fea_freemius;
            }
            if ( !defined( 'WP_FS__PRODUCT_5212_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_5212_MULTISITE', true );
            }
            require_once dirname( __FILE__ ) . '/main/freemius/start.php';
            $fea_freemius = fs_dynamic_init( array(
                'id'              => '5212',
                'slug'            => 'acf-frontend-form-element',
                'premium_slug'    => 'frontend-admin-pro',
                'type'            => 'plugin',
                'public_key'      => 'pk_771aff8259bcf0305b376eceb7637',
                'is_premium'      => true,
                'premium_suffix'  => 'Pro',
                'has_addons'      => false,
                'has_paid_plans'  => true,
                'trial'           => array(
                    'days'               => 7,
                    'is_require_payment' => true,
                ),
                'has_affiliation' => false,
                'menu'            => array(
                    'slug'        => 'fea-settings',
                    'contact'     => false,
                    'support'     => false,
                    'affiliation' => false,
                ),
                'is_live'         => true,
            ) );
            return $fea_freemius;
        }

        fea_freemius();
    }
    /**
     * Main Frontend Admin Class
     *
     * The main class that initiates and runs the plugin.
     *
     * @since 1.0.0
     */
    final class Front_End_Admin_Pro {
        /**
         * Constructor
         *
         * @since 1.0.0
         *
         * @access public
         */
        public function __construct() {
            global $fea_instance;
            if ( isset( $fea_instance ) ) {
                return;
            }
            do_action( 'front_end_admin_loaded' );
            do_action( 'front_end_admin_pro_loaded' );
            add_action( 'after_setup_theme', array($this, 'init'), 10 );
            //require_once 'pro/features.php';
        }

        /**
         * Initialize the plugin
         *
         * Load the plugin only after ACF is loaded.
         * Checks for basic plugin requirements, if one check fail don't continue,
         * If all checks have passed load the files required to run the plugin.
         *
         * Fired by `plugins_loaded` action hook.
         *
         * @since 1.0.0
         *
         * @access public
         */
        public function init() {
            include_once 'pro/plugin.php';
            global $fea_pro_instance;
            $fea_pro_instance = new \Frontend_Admin\Pro_Features([
                'using_freemius' => true,
            ]);
            include_once 'main/plugin.php';
            global $fea_instance;
            $fea_instance = new \Frontend_Admin\Plugin([
                'pro_version' => true,
                'basename'    => plugin_basename( __FILE__ ),
                'plugin_dir'  => plugin_dir_path( __FILE__ ),
                'plugin_url'  => plugin_dir_url( __FILE__ ),
                'plugin'      => 'Freemius Version',
            ]);
        }

    }

    new Front_End_Admin_Pro();
}