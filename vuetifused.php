<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Vuetifused
 * Plugin URI:        https://vuetifused.com
 * Description:       A Vuetify infused WordPress admin experience. 
 * Version:           1.0.0
 * Author:            Austin Ginder
 * Author URI:        https://austinginder.com
 * License:           MIT License
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       vuetifused
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

function vuetifused_initialization() {
    
    if ( wp_doing_ajax() ) {
        return;
    }
    
    $current_user = wp_get_current_user();
    if ( empty( $current_user->ID ) ) {
        return;
    }

    $requested_page = $_SERVER['REQUEST_URI'];
    if ( strpos( $requested_page, '/wp-admin/post.php' ) !== false || strpos( $requested_page, '/wp-admin/customize.php' ) !== false || strpos( $requested_page, '/wp-admin/post-new.php' ) !== false ) {
        return;
    }

    $vuetifused_enabled = get_user_meta( $current_user->ID , 'vuetifused_enabled', true ); 
    if ( $vuetifused_enabled != "true" ) {
        return;
    }
    
    include plugin_dir_path( __FILE__ ) . 'template.php';
    die();
}
add_action( 'admin_init', 'vuetifused_initialization' );

function vuetifused_admin_toolbar( $admin_bar ) {

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $current_user       = wp_get_current_user();
    $vuetifused_enabled = get_user_meta( $current_user->ID , 'vuetifused_enabled', true ); 
    if ( $vuetifused_enabled == "true" ) {
        return;
    }

    $admin_bar->add_menu( [
        'id'    => 'wp-freighter-exit',
        'title' => '<span class="ab-icon dashicons dashicons-welcome-view-site"></span>Enable Vuetifused',
        'href'  => '/wp-admin/admin-ajax.php?action=vuetifused_ajax&command=enable',
    ] );

}
add_action( 'admin_bar_menu', 'vuetifused_admin_toolbar', 100 );

function vuetifused_ajax_actions() {

    if ( ! current_user_can( 'manage_options' ) ) { 
        return new WP_Error( 'permission_denied', 'Permission Denied', [ 'status' => 403 ] );
    }

    $command = $_GET['command'];
    $value   = $_GET['value'];
    echo $command;

    if ( $command == "enable" ) {
        $current_user = wp_get_current_user();
        update_user_meta( $current_user->ID, 'vuetifused_enabled', 'true' );
        wp_redirect( "/wp-admin/" );
    }

    if ( $command == "disable" ) {
        $current_user = wp_get_current_user();
        update_user_meta( $current_user->ID, 'vuetifused_enabled', 'false' );
        wp_redirect( "/wp-admin/" );
    }

    wp_die();
    return;

}
add_action( 'wp_ajax_vuetifused_ajax', 'vuetifused_ajax_actions' );

add_action( 'rest_api_init', 'vuetifused_register_rest_endpoints' );
function vuetifused_register_rest_endpoints() {

	register_rest_route(
		'vuetifused/v1', '/login', [
			'methods'       => 'POST',
			'callback'      => 'vuetifused_login_func',
			'show_in_index' => false
		]
    );

    register_rest_route(
		'vuetifused/v1', '/site/themes', [
			'methods'       => 'GET',
			'callback'      => 'vuetifused_themes_func',
			'show_in_index' => false
		]
    );

    register_rest_route(
		'vuetifused/v1', '/site/plugins', [
			'methods'       => 'GET',
			'callback'      => 'vuetifused_plugins_func',
			'show_in_index' => false
		]
    );

    register_rest_route(
		'vuetifused/v1', '/site/content/(?P<post_type>[a-zA-Z0-9-]+)/(?P<page>[\d]+)', [
			'methods'       => 'GET',
			'callback'      => 'vuetifused_content_func',
			'show_in_index' => false
		]
    );

    register_rest_route(
		'vuetifused/v1', '/manage/themes', [
			'methods'       => 'POST',
			'callback'      => 'vuetifused_manage_themes_func',
			'show_in_index' => false
		]
    );

    register_rest_route(
		'vuetifused/v1', '/manage/plugins', [
			'methods'       => 'POST',
			'callback'      => 'vuetifused_manage_plugins_func',
			'show_in_index' => false
		]
    );

}

function vuetifused_manage_plugins_func( $request ) {

    if ( ! current_user_can( 'manage_options' ) ) { 
        return new WP_Error( 'permission_denied', 'Permission Denied', [ 'status' => 403 ] );
    }

    $post = json_decode( file_get_contents( 'php://input' ) );
    if ( $post->plugin == "" ) {
        return;
    }
    $all_plugins = array_keys( get_plugins() );
    foreach ( $all_plugins as $plugin ) {
        if ( strpos( $plugin, $post->plugin ) !== false ) {
            $selected_plugin = $plugin;
        }
    }
    if ( $plugin == "" ) {
        return;
    }
    if ( $post->command == 'activate' ) {
		activate_plugin( $selected_plugin );
    }
	if ( $post->command == 'deactivate' ) {
		deactivate_plugins( $selected_plugin );
    }
    if ( $post->command == 'delete' ) {
		delete_plugins( [ $selected_plugin ] );
	}
    return;

}

function vuetifused_manage_themes_func( $request ) {

    if ( ! current_user_can( 'manage_options' ) ) { 
        return new WP_Error( 'permission_denied', 'Permission Denied', [ 'status' => 403 ] );
    }

    $post = json_decode( file_get_contents( 'php://input' ) );
    if ( $post->command == 'delete' && $post->theme != "" ) {
        require_once ABSPATH . 'wp-admin/includes/theme.php';
		delete_theme( $post->theme );
	}
	if ( $post->command == 'activate' && $post->theme != "" ) {
		switch_theme( $post->theme );
	}
    return;

}

function vuetifused_content_func( $request ) {

    if ( ! current_user_can( 'manage_options' ) ) { 
        return new WP_Error( 'permission_denied', 'Permission Denied', [ 'status' => 403 ] );
    }

    $page      = $request['page'];
    $offset    = ( $page - 1 ) * 100;
    $post_type = $request['post_type'];
    $count     = count ( get_posts( [ "post_type" => $post_type, "posts_per_page" => "-1", "fields" => "ids" ] ) );
    $pages     = get_posts( [ 
        "post_type"      => $post_type,
        "posts_per_page" => "100",
        "offset"         => $offset,
    ] );
    foreach ( $pages as $key => $page ) {
        $pages[ $key ]->thumbnail = get_the_post_thumbnail_url( $page->ID, 'medium' );
    }
    $response = [ 
        "results" => $pages,
        "count"   => $count
    ];
    return $response;
}

function vuetifused_login_func( WP_REST_Request $request ) {

    $post = json_decode( file_get_contents( 'php://input' ) );

	if ( $post->command == "signOut" ) {
		wp_logout();
	}

}

function vuetifused_plugins_func( WP_REST_Request $request ) {

    if ( ! current_user_can( 'manage_options' ) ) { 
        return new WP_Error( 'permission_denied', 'Permission Denied', [ 'status' => 403 ] );
    }

    $plugins     = [];
    $all_plugins = get_plugins();

    // Get active plugins
    $active_plugins = get_option('active_plugins');

    // Assemble array of name, version, and whether plugin is active (boolean)
    foreach ( $all_plugins as $key => $value ) {
        $is_active = ( in_array( $key, $active_plugins ) ) ? true : false;
        $plugins[ $key ] = [
            'name'        => $value['Name'],
            'slug'        => dirname( $key ),
            'description' => $value['Description'],
            'version'     => $value['Version'],
            'status'      => $is_active,
        ];
    }

    $must_use_plugins = wp_get_mu_plugins();
    foreach ( $must_use_plugins as $must_use_plugin ) {
        $slug      = str_replace( ".php", "", basename( $must_use_plugin ) );
        $plugins[] = [
            'name'    => "",
            'slug'    => $slug,
            'version' => "",
            'status'  => "must-use",
        ];
    }

    return array_values( $plugins );

}

function vuetifused_themes_func( WP_REST_Request $request ) {

    if ( ! current_user_can( 'manage_options' ) ) { 
        return new WP_Error( 'permission_denied', 'Permission Denied', [ 'status' => 403 ] );
    }

    $themes       = [];
    $all_themes   = get_themes();
    $active_theme = get_option('stylesheet');

    // Assemble array of name, version, and whether plugin is active (boolean)
    foreach ( $all_themes as $key => $value ) {
        $is_active = ( $active_theme == $value->stylesheet ) ? true : false;
        $themes[ $key ] = [
            'name'        => $value->Name,
            'slug'        => $value->stylesheet,
            'description' => $value->Description,
            'version'     => $value->Version,
            'screenshot'  => $value->get_screenshot(),
            'status'      => $is_active,
        ];
    }

    return array_values( $themes );

}

function vuetifused_head_content() {
    ob_start();
    do_action('wp_head');
    return ob_get_clean();
}

function vuetifused_header_content_extracted() {
    $output = "<script type='text/javascript'>\n/* <![CDATA[ */\n";
	$head   = vuetifused_head_content();
	preg_match_all('/(var wpApiSettings.+)/', $head, $results );
	if ( isset( $results ) && $results[0] ) {
		foreach( $results[0] as $match ) {
			$output = $output . $match . "\n";
		}
	}
	$output = $output . "</script>\n";
	echo $output;
}