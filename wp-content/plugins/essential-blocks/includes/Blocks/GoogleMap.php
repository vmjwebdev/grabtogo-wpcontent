<?php

/**
 * Functions to register client-side assets (scripts and stylesheets) for the
 * Gutenberg block.
 *
 * @package essential-blocks
 */

/**
 * Registers all block assets so that they can be enqueued through Gutenberg in
 * the corresponding context.
 *
 * @see https://wordpress.org/gutenberg/handbook/designers-developers/developers/tutorials/block-tutorial/applying-styles-with-stylesheets/
 */

namespace EssentialBlocks\Blocks;

use EssentialBlocks\Core\Block;

class GoogleMap extends Block
{
    protected $frontend_scripts = [ 'essential-blocks-google-map-frontend', 'essential-blocks-google-map-script' ];

    /**
     * Unique name of the block.
     *
     * @return string
     */
    public function get_name()
    {
        return 'google-map';
    }

    private $api_key = '';

    public function __construct()
    {
        $settings = get_option( 'eb_settings', [  ] );

        if ( is_array( $settings ) && ! empty( $settings[ 'googleMapApi' ] ) ) {
            $this->api_key = $settings[ 'googleMapApi' ];
        }
    }

    /**
     * Register all other scripts
     *
     * @return void
     */
    public function register_scripts()
    {
        $this->assets_manager->register(
            'google-map-frontend',
            $this->path() . '/frontend.js',
        );

        if ( ! empty( $this->api_key ) ) {
            // Only for editor
            if ( is_admin() ) {
                $this->assets_manager->enqueue(
                    'google-map-script-editor',
                    'https://maps.googleapis.com/maps/api/js?key=' . $this->api_key . '&callback=Function.prototype&libraries=places&cache=' . wp_rand( 10, 1000 ),
                    [  ],
                    [
                        'is_js' => true
                     ]
                );
            }
            // For frontend
            $this->assets_manager->register(
                'google-map-script',
                'https://maps.googleapis.com/maps/api/js?key=' . $this->api_key . '&callback=Function.prototype&libraries=places&cache=' . wp_rand( 10, 1000 ),
                [  ],
                [
                    'is_js' => true
                 ]
            );
        }
    }

    public function render_callback( $attributes, $content )
    {
        if ( empty( $this->api_key ) ) {
            if ( get_current_user_id() ) {
                $html = __( 'Please add your Google Map API to display Google Maps Block', 'essential-blocks' );

                return $html;
            } else {
                return;
            }
        }

        return $content;
    }
}
