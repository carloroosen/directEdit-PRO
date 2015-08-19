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

// Use it instead of get_posts(), if you want to order posts manually
function direct_get_posts( $args = array() ) {
	global $wp_query;
	
	$wp_query = new WP_Query( $args );
	$wp_query->parse_query( $args );

	if ( $args[ 'orderby' ] == 'directedit' ) {
		if ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_de_frontend' ) ) {
			add_filter( 'post_limits', 'de_post_limits', 10, 2 );
		}

		$index = 'direct_order';
		if( count( $wp_query->tax_query->queried_terms ) == 1 ) {		
			foreach( $wp_query->tax_query->queried_terms as $key => $value ) {
				if ( count( $value[ 'terms' ] ) == 1 ) {
					$t = get_term_by( $value[ 'field' ], $value[ 'terms' ][ 0 ], $key );
					if ( $t ) {
						$index = $index . '_' . $t->taxonomy . '_' . $t->slug;
					}
				}
			}
		}
		$wp_query->set( 'directedit', array( 'order' => array( 'index' => $index, 'count' => 0 ) ) );
		add_filter( 'posts_join', 'de_posts_join', 10, 2 );
	
		add_filter( 'posts_orderby', 'de_posts_orderby', 10, 2 );
	}

	$wp_query->get_posts();
	remove_filter( 'post_limits', 'de_post_limits', 10 );
	remove_filter( 'posts_join', 'de_posts_join', 10 );
	remove_filter( 'posts_orderby', 'de_posts_orderby', 10 );
	//echo '<pre>';
	//print_r( $wp_query );
	return $wp_query;
}

function de_post_limits( $limit, $query ) {
	return '';
}

function de_posts_join( $join, $query ) {
	global $wpdb;

	$de_options = $query->get( 'directedit' );
	$index = $de_options[ 'order' ][ 'index' ];
	
	$join .= " LEFT JOIN " . $wpdb->prefix . "postmeta ON ( " . $wpdb->prefix . "posts.ID = " . $wpdb->prefix . "postmeta.post_id AND " . $wpdb->prefix . "postmeta.meta_key = '$index' )";
	return $join;
}

function de_posts_orderby( $order, $query ) {
	global $wpdb;
	
	if ( $order ) {
		$order = $wpdb->prefix . 'postmeta.meta_value+0 ASC, ' . $order;
	} else {
		$order = $wpdb->prefix . 'postmeta.meta_value+0 ASC';
	}

	return $order;
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
