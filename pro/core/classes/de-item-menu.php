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
		$m = '';
		if ( isset( $params[ 'menu' ] ) ) {
			$m = $params[ 'menu' ];
		}
		$menu = wp_get_nav_menu_object( $m );
		if ( ! $menu && $params[ 'theme_location' ] && ( $locations = get_nav_menu_locations() ) && isset( $locations[ $params[ 'theme_location' ] ] ) )
			$menu = wp_get_nav_menu_object( $locations[ $params[ 'theme_location' ] ] );
		if ( $menu && ! is_wp_error( $menu ) ) {
			$this->settings[ 'menu' ] = $menu->slug;
		}

		/* Menu editor is hidden. Probably it will be removed at all in future versions. */
		/*
		if ( De_Store::is_editable( $this ) ) {
			De_Items::add( $this );
		}
		*/
	}

	// We don't need output_partial() for menus.
	public function output( $content = null ) {
		global $direct_walker;
		
		$params = $this->get_setting( 'directMenuParams' );

		/* Menu editor is hidden. Probably it will be removed at all in future versions. */
		/*	
		if ( De_Store::is_editable( $this ) ) {
			// Editable menu
			$result = '';

			if ( $this->get_setting( 'attr' ) && is_array( $this->get_setting( 'attr' ) ) ) {
				$attr = $this->get_setting( 'attr' );
			}
			
			// Use direct_menu() params
			$useful_params = array( 'depth', 'startLevel', 'menu_id', 'menu_class' );
			foreach( $params as $key => $value ) {
				if ( in_array( $key, $useful_params ) ) {
					$this->set_setting( $key, $value );
				}
			}

			$attr[ 'data-reference' ] = $this->reference;
			$attr[ 'data-global-options' ] = $this->get_setting( 'options' );
			$attr[ 'data-local-options' ] = $this->build_local_options();
			
			if ( isset( $params[ 'container_id' ] ) ) {
				$attr[ 'id' ] = $params[ 'container_id' ];
			} else {
				$attr[ 'id' ] = $this->reference;
			}
			$attr[ 'class' ] = ( isset( $params[ 'container_class' ] ) ? $params[ 'container_class' ] . ' direct-editable-menu' : 'direct-editable-menu' );
			$result .= '<div' . self::attr_to_string( $attr ) . '><ul></ul></div>';
			
			if ( $params[ 'echo' ] === false ) {
				return $result;
			} else {
				echo $result;
			}
		} else {
		*/
			$result = '';
			
			// Use our walker if menu exists only
			if ( $this->get_setting( 'menu' ) ) {
				$params[ 'walker' ] = $direct_walker;
			}
			
			$result = wp_nav_menu( $params );
			return $result;
		/*
		}
		*/
	}
}
