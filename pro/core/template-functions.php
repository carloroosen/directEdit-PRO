<?php
// Global output variables
global $de_snippet_image;
$de_snippet_image = new De_Snippet_Image();
global $de_snippet_list;
$de_snippet_list = new De_Snippet_List();

global $direct_image;
global $direct_list_item;

global $direct_walker;
$direct_walker = new De_Walker_Nav_Menu(); 

// General-purpose function.
// Possible field types: text, image, link, list, file
// Possible $store values: postmeta, usermeta, option, wptitle, wpcontent, wpexcerpt
// wptitle does not work with image, link, list and file.
function direct_editable( $type, $store, $settings = array(), $echo = true ) {
	$result = '';
	
	$type = strtolower( $type );
	$class = 'De_Item_' . ucfirst( $type );
	try {
		$item = new $class( $store, $settings );
		$content = De_Store::read( $item );

		if ( empty( $content ) && $item->get_setting( 'saveDefault' ) && $item->get_setting( 'saveDefault' ) !== 'false' && $item->get_setting( 'saveDefault' ) !== 'no' ) {
			$item->set_setting( 'alwaysSave', true );
		}

		$result = $item->output( $content );
	} catch ( Exception $e ) {
	}

	if ( !$echo ) {
		return $result;
	}

	echo $result;
}

// dE list functions
function direct_list_have_items() {
	global $de_snippet_list;
	
	return $de_snippet_list->have_items();
}

function direct_list_the_item() {
	global $de_snippet_list;
	
	$de_snippet_list->the_item();
}

function direct_list_rewind_items() {
	global $de_snippet_list;
	
	$de_snippet_list->rewind_items();
}

// dE image functions
function direct_image_copy( $copy, $store = null, $settings = array() ) {
	global $de_snippet_image;

	$result = '';
	
	if ( $de_snippet_image->in_the_snippet ) {
		$settings = array_merge( $de_snippet_image->owner->settings, $settings );
		if ( empty( $store ) ) {
			$store = $de_snippet_image->owner->store;
		}
	}

	if ( $store ) {
		$settings[ 'disableEdit' ] = true;
		$settings[ 'useCopy' ] = $copy;
		
		$item = new De_Item_Image( $store, $settings );
		$content = De_Store::read( $item );
		$result = $item->get_src( $content );
	}
	
	return $result;
}

// Miscellaneous functions
function direct_copyright( $year, $echo = true ) {
	$y = ( int ) date( 'Y' );
	$y_ = ( int ) $year;
	$footer = ($y_ && $y > $y_) ? $y_ . ' - ' . $y : $y ;
	
	if ( ! $echo ) {
		return $footer;
	}

	echo $footer;
}

function direct_menu( $params, $settings = array() ) {
	$result = '';
	
	try {
		$item = new De_Item_Menu( $params, $settings );

		$result = $item->output();
	} catch ( Exception $e ) {
	}

	return $result;
}

function direct_bloginfo( $show, $echo = true, $post_id = null ) {
	global $direct_queried_object;
	
	$result = '';
	
	if ( ! $post_id && $direct_queried_object && $direct_queried_object->ID ) {
		$post_id = $direct_queried_object->ID;
	}

	if ( $post_id && $post = get_post( $post_id ) ) {
		switch( $show ) {
			case 'title':
				$result = get_post_meta( $post_id, 'de_title', true );
				if ( ! $result ) {
					$result = esc_attr( wptexturize( $post->post_title . ' | ' . get_bloginfo( 'name' ) ) );
				}
			break;
			case 'description':
				$result = esc_attr( get_post_meta( $post_id, 'de_description', true ) );
			break;
			case 'keywords':
				$result = esc_attr( get_post_meta( $post_id, 'de_keywords', true ) );
			break;
			case 'slug':
				$result = get_post_meta( $post_id, 'de_slug', true );
				if ( ! $result ) {
					$result = $post->post_name;
				}
			break;
			case 'url':
				$result = get_permalink( $post_id );
			break;
			case 'navigation_label':
				$result = get_post_meta( $post_id, 'de_navigation_label', true );
				if ( ! $result ) {
					$result = $post->post_title;
				}
			break;
			default:
				// No default value
			break;
		}
	} else {
		// If no post ID, return default WP values
		switch( $show ) {
			case 'title':
				$result = wptexturize( get_bloginfo( 'name' ) );
			break;
			case 'description':
				// No default value
			break;
			case 'slug':
				// No default value
			break;
			case 'url':
				// No default value
			break;
			default:
				// No default value
			break;
		}
	}
	
	if ( ! $echo ) {
		return $result;
	}

	echo $result;
}

function direct_post_id( $post_name ) {
	global $wpdb;
	$id = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_name = '" . $post_name . "'" );
	return $id;
}
