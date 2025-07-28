<?php
/**
 *  Package: WP File Manager Pro
 *  Common funcion Class
 *  Class: wp_file_manager_common_function
 */
if(!class_exists('wp_file_manager_common_function')) {

    class wp_file_manager_common_function {  
        
        /**
         * Get Short Code Data
         */
        public function get_shortcode_info($attrs){
            global $wpdb;
            $shortcode_attributes = '';  
            if(!empty( $attrs['id'])){
                $shortcode_data = $wpdb->get_row(
                    $wpdb->prepare(
                        'SELECT * FROM ' . $wpdb->prefix . 'wpfm_shortcodes WHERE shotcode_key = %s', 
                        $attrs['id']
                    )
                );	
                if(!empty($shortcode_data)){
                    $shortcode_attributes = unserialize($shortcode_data->attributes);        
                    $shortcode_attributes = apply_filters( 'fm_shortcode_attr_'.$shortcode_data->shotcode_key, $shortcode_attributes );
                    $shortcode_attributes['shotcode_key'] = $attrs['id'];
                    $shortcode_attributes['ac_path'] = $shortcode_attributes['access_folder'] ?? '';
                    $opt = get_option('wp_filemanager_options');
                }
            }
          return $shortcode_attributes ;
        }
    }
}