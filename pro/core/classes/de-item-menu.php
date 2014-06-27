<?php
class De_Item_Menu extends De_Item {
	// We need other params there. No $store, but $params for direct_menu() function.
	public function __construct( $params, $settings ) {
		global $de_global_options;
		global $post_type;
		global $post;
		
		$this->settings = $settings;

		// Check that the defined global option exists
		if ( ! empty( $this->settings[ 'options' ] ) && empty( $de_global_options[ "{$this->settings[ 'options' ]}" ] ) ) {
			unset( $this->settings[ 'options' ] );
		}
		
		// Some default settings
		$this->settings[ 'directMenuParams' ] = $params;
		
		// Does menu exist?
		$menu = wp_get_nav_menu_object( $params[ 'menu' ] );
		if ( ! $menu && $params[ 'theme_location' ] && ( $locations = get_nav_menu_locations() ) && isset( $locations[ $params[ 'theme_location' ] ] ) )
			$menu = wp_get_nav_menu_object( $locations[ $params[ 'theme_location' ] ] );
		if ( $menu && ! is_wp_error( $menu ) ) {
			$this->settings[ 'menu' ] = $menu->slug;
		}

		if ( De_Store::is_editable( $this ) ) {
			De_Items::add( $this );
		}
	}

	// We don't need output_partial() for menus.
	public function output() {
		global $direct_walker;
		
		$params = $this->get_setting( 'directMenuParams' );
		
		if ( De_Store::is_editable( $this ) ) {
			// Editable menu
			$result = '';

			if ( $this->get_setting( 'attr' ) && is_array( $this->get_setting( 'attr' ) ) ) {
				$attr = $this->get_setting( 'attr' );
			}
			
			// Use direct_menu() params
			$useful_params = array( 'depth', 'startLevel' );
			foreach( $params as $key => $value ) {
				if ( in_array( $key, $useful_params ) ) {
					$this->set_setting( $key, $value );
				}
			}

			$attr[ 'data-reference' ] = $this->reference;
			$attr[ 'data-global-options' ] = $this->get_setting( 'options' );
			$attr[ 'data-local-options' ] = $this->build_local_options();
			
			if ( ! empty( $params[ 'container' ] ) && $params[ 'container' ] == 'div' ) {
				if ( isset( $params[ 'container_id' ] ) ) {
					$attr[ 'id' ] = $params[ 'container_id' ];
				} else {
					$attr[ 'id' ] = $this->reference;
				}
				$attr[ 'class' ] = ( isset( $params[ 'container_class' ] ) ? $params[ 'container_class' ] . ' direct-editable-menu' : 'direct-editable-menu' );
				$result .= '<div' . self::attr_to_string( $attr ) . '><ul></ul></div>';
			} else {
				if ( isset( $params[ 'menu_id' ] ) ) {
					$attr[ 'id' ] = $params[ 'menu_id' ];
				} else {
					$attr[ 'id' ] = $this->reference;
				}
				$attr[ 'class' ] = ( isset( $params[ 'menu_class' ] ) ? $params[ 'menu_class' ] . ' direct-editable-menu' : 'direct-editable-menu' );
				$result .= '<ul' . self::attr_to_string( $attr ) . '></ul>';
			}
			
			if ( $params[ 'echo' ] === false ) {
				return $result;
			} else {
				echo $result;
			}
		} else {
			$result = '';
			
			// Use our walker if menu exists only
			if ( $this->get_setting( 'menu' ) ) {
				$params[ 'walker' ] = $direct_walker;
			}
			
			$result = wp_nav_menu( $params );
			return $result;
		}
	}
}
