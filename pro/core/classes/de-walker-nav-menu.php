<?php
class De_Walker_Nav_Menu extends Walker_Nav_Menu {
	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		global $post;
		global $wp_query;

		if ( empty( $item->url ) || empty( $item->title ) )
			return;
		
		// Hide hidden page menuitem in view mode or if $_SESSION[ 'de_mode' ] != 'edit-show-hidden'
		if ( $item->type == 'post_type' && ! empty( $item->object_id ) && de_is_hidden( $item->object_id ) && ( ! ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_de_frontend' ) ) || empty( $_SESSION[ 'de_mode' ] ) || $_SESSION[ 'de_mode' ] != 'edit-show-hidden' ) )
			return;
		
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		$class_names = $value = '';

		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;
		// Add our class for hidden menuitems
		if ( $item->type == 'post_type' && ! empty( $item->object_id ) && de_is_hidden( $item->object_id ) ) {
			$classes[] = 'direct-hidden';
		}
		// Add 'direct-show-all' class if 'Show all' option is checked
		if ( ! empty( $_SESSION[ 'de_mode' ] ) && $_SESSION[ 'de_mode' ] == 'edit-show-hidden' ) {
			$classes[] = 'direct-show-all';
		}

		if ( empty( $args->item_hide_classes ) ) {
			$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
		}
		if ( ! empty( $args->item_class_current ) && array_intersect( array( 'current-menu-item', 'current-menu-ancestor' ), $classes ) ) {
			$class_names = join( ' ', array_filter( array( $class_names, $args->item_class_current ) ) );
		}
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';
		
		$id = '';
		
		if ( empty( $args->item_hide_id ) ) {
			$id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args );
			$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';
		}

		$output .= $indent . '<li' . $id . $value . $class_names .'>';

		$attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
		$attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
		$attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
		$attributes .= ( $item->type == 'post_type' && ! empty( $item->object_id ) ) ? ' href="'   . esc_attr( get_permalink( $item->object_id ) ) .'"' : ( ! empty( $item->url ) ? ' href="'   . esc_attr( $item->url ) .'"' : '' );
		if ( ! empty( $args->link_class_current ) && array_intersect( array( 'current-menu-item', 'current-menu-ancestor' ), $classes ) ) {
			$attributes .= ' class="' . $args->link_class_current . '"';
		}

		$item_output = $args->before;
		$item_output .= '<a'. $attributes .'>';
		$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
		$item_output .= '</a>';
		$item_output .= $args->after;

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}
	
	function end_el( &$output, $item, $depth = 0, $args = array() ) {
		if ( empty( $item->url ) || empty( $item->title ) )
			return;
		
		if ( $item->type == 'post_type' && ! empty( $item->object_id ) && de_is_hidden( $item->object_id ) && ( ! ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_de_frontend' ) ) || empty( $_SESSION[ 'de_mode' ] ) || $_SESSION[ 'de_mode' ] != 'edit-show-hidden' ) )
			return;
		
		$output .= "</li>\n";
	}
}
