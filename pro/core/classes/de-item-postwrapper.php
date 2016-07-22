<?php
class De_Item_Postwrapper extends De_Item {
	public function __construct( $store, $settings ) {
		parent::__construct( $store, $settings );
		
		if ( ! $this->get_setting( 'type' ) || $this->get_setting( 'type' ) != 'postwrapper' ) {
			$this->delete_setting( 'type' );
			$this->set_setting( 'options', 'postwrapper' );
		}

		// Set proper Show/Hide button
		if ( $this->get_setting( 'buttonShow' ) && $this->get_setting( 'buttonShow' ) !== 'no' && $this->get_setting( 'buttonShow' ) !== 'false' ) {
			$this->set_setting( 'buttonShow', false );
		}
		if ( $this->get_setting( 'buttonHide' ) && $this->get_setting( 'buttonHide' ) !== 'no' && $this->get_setting( 'buttonHide' ) !== 'false' ) {
			$this->set_setting( 'buttonHide', false );
		}
		if ( $this->get_setting( 'buttonShowHide' ) && $this->get_setting( 'buttonShowHide' ) !== 'no' && $this->get_setting( 'buttonShowHide' ) !== 'false' ) {
			if ( $this->get_setting( 'postId' ) && de_is_hideable( $this->get_setting( 'postId' ) ) ) {
				if ( de_is_hidden( $this->get_setting( 'postId' ) ) ) {
					$this->set_setting( 'buttonShow', true );
				} else {
					$this->set_setting( 'buttonHide', true );
				}
			}
		}
			
		// Show Delete button if the post can be removed
		if ( $this->get_setting( 'buttonDelete' ) && $this->get_setting( 'buttonDelete' ) !== 'no' && $this->get_setting( 'buttonDelete' ) !== 'false' ) {
			if ( ! $this->get_setting( 'postId' ) || ! de_is_deleteable( $this->get_setting( 'postId' ) ) ) {
				$this->set_setting( 'buttonDelete', false );
			}
		}
		
		// Add 'direct-hidden' class for hidden items
		if ( $this->store == 'post' ) {
			if ( $this->get_setting( 'postId' ) && de_is_hidden( $this->get_setting( 'postId' ) ) ) {
				$attr = $this->get_setting( 'attr' );
				$attr[ 'class' ] = ( isset( $attr[ 'class' ] ) ? $attr[ 'class' ] . ' direct-hidden' : 'direct-hidden' );
				$this->set_setting( 'attr', $attr );
			}
			// Add 'direct-show-all' class if 'Show all' option is checked
			if ( ! empty( $_SESSION[ 'de_mode' ] ) && $_SESSION[ 'de_mode' ] == 'edit-show-hidden' ) {
				$attr = $this->get_setting( 'attr' );
				$attr[ 'class' ] = ( isset( $attr[ 'class' ] ) ? $attr[ 'class' ] . ' direct-show-all' : 'direct-show-all' );
				$this->set_setting( 'attr', $attr );
			}

			// Possibility to order posts manually
			global $wp_query;
			if ( $this->get_setting( 'postId' ) && in_the_loop() && $wp_query->get( 'orderby' ) == 'directedit' && ( empty( $_SESSION[ 'de_mode' ] ) || $_SESSION[ 'de_mode' ] != 'edit-show-hidden' ) && $this->get_setting( 'showOrder' ) ) {
				$de_options = $wp_query->get( 'directedit' );
				
				$de_options[ 'order' ][ 'count' ] ++;
				$this->set_setting( 'orderIndex', $de_options[ 'order' ][ 'index' ] );
				$this->set_setting( 'orderCount', $de_options[ 'order' ][ 'count' ] );
				
				$wp_query->set( 'directedit', $de_options );
			}
		}
	}

	public function output( $content = null ) {
		$attr = array();

		if ( $this->get_setting( 'container' ) ) {
			$container = $this->get_setting( 'container' );
		} else {
			$container = 'div';
		}
		if ( $this->get_setting( 'attr' ) && is_array( $this->get_setting( 'attr' ) ) ) {
			$attr = $this->get_setting( 'attr' );
		}

		// Show Direct Edit only for users who have proper permissions
		if ( De_Store::is_editable( $this ) ) {
			$attr[ 'data-reference' ] = $this->reference;
			if ( empty( $attr[ 'id' ] ) )
				$attr[ 'id' ] = $this->reference;
			$attr[ 'class' ] = ( isset( $attr[ 'class' ] ) ? $attr[ 'class' ] . ' direct-editable' : 'direct-editable' );
			$attr[ 'data-global-options' ] = $this->get_setting( 'options' );
			$attr[ 'data-local-options' ] = $this->build_local_options();
			if ( $this->store == 'post' ) {
				global $wp_query;
				if ( $this->get_setting( 'postId' ) && in_the_loop() && $wp_query->get( 'orderby' ) == 'directedit' && ( empty( $_SESSION[ 'de_mode' ] ) || $_SESSION[ 'de_mode' ] != 'edit-show-hidden' ) && $this->get_setting( 'showOrder' ) ) {
					$attr[ 'data-count' ] = get_post_meta( $this->get_setting( 'postId' ), $this->get_setting( 'orderIndex' ), true );
				}
			}
		}
		
		$result = '<' . $container . self::attr_to_string( $attr ) . '>';
		
		return $result;
	}
	
	public function output_partial( $content = null ) {
		return null;
	}
}
