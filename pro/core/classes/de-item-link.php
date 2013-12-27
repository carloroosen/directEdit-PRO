<?php
class De_Item_Link extends De_Item {
	public function __construct( $store, $settings ) {
		parent::__construct( $store, $settings );
		
		if ( ! $this->get_setting( 'type' ) || $this->get_setting( 'type' ) != 'link' ) {
			$this->delete_setting( 'type' );
			if ( $this->store == 'post' ) {
				$this->set_setting( 'options', 'link-post' );
			} else {
				$this->set_setting( 'options', 'link' );
			}
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
		if ( $this->get_setting( 'postId' ) && de_is_hidden( $this->get_setting( 'postId' ) ) ) {
			$attr = $this->get_setting( 'attr' );
			$attr[ 'class' ] = ( isset( $attr[ 'class' ] ) ? $attr[ 'class' ] . ' direct-hidden' : 'direct-hidden' );
			$this->set_setting( 'attr', $attr );
		}
		// Add 'direct-show-all' class if 'Show all' option is checked
		if ( ! empty( $_SESSION[ 'de_show_all' ] ) ) {
			$attr = $this->get_setting( 'attr' );
			$attr[ 'class' ] = ( isset( $attr[ 'class' ] ) ? $attr[ 'class' ] . ' direct-show-all' : 'direct-show-all' );
			$this->set_setting( 'attr', $attr );
		}
	}

	public function output( $content = null ) {
		$attr = array();
		
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
		}
		
		$content_partial =  $this->output_partial( $content );
		$result = '<a' . self::attr_to_string( $attr ) . ' href="' . $content_partial[ 'url' ] . '">';
		
		return $result;
	}
	
	public function output_partial( $content = null ) {
		if ( ! strlen( $content ) ) {
			if ( ! $this->get_setting( 'default' ) ) {
				$content = '#';
			} else {
				$content = $this->get_setting( 'default' );
			}
		}
		
		$content = de_encode_emails( $content );
		
		return array( 'url' => $content );
	}
}
