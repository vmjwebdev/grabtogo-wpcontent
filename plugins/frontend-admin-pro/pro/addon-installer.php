<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function fea_is_plugin_installed( $slug ) {
	if ( ! function_exists( 'get_plugins' ) ) {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$all_plugins = get_plugins();
	if ( $all_plugins ) {
		foreach ( $all_plugins as $plugin ) {
			if ( $plugin['TextDomain'] == 'frontend-admin-' .$slug ) {
				return true;
			}
		}
	}
	return false;

}

function fea_is_plugin_active( $slug ) {
	switch ( $slug ) {
		case 'payments':
			if( class_exists('Frontend_Admin_Payments') ) return true;
			break;
		case 'pdf':
			if( class_exists('Frontend_Admin_PDF') ) return true;
			break;
		default:
			return false;
	}
}

function fea_addon_slug( $slug ) {
	if ( ! function_exists( 'get_plugins' ) ) {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$all_plugins = get_plugins();
	if ( $all_plugins ) {
		foreach ( $all_plugins as $path => $plugin ) {
			if ( $plugin['TextDomain'] == $slug ) {
				return $path;
			}
		}
	}
	return false;

}

function fea_install_plugin( $plugin_zip ) {
	include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	wp_cache_flush();

	$upgrader  = new Plugin_Upgrader();
	$installed = $upgrader->install( $plugin_zip );

	return $installed;
}

