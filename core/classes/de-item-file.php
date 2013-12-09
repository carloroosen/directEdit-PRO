<?php
class De_Item_File extends De_Item {
	public function __construct( $store, $settings ) {
		parent::__construct( $store, $settings );
		
		if ( ! $this->get_setting( 'type' ) || $this->get_setting( 'type' ) != 'file' ) {
			$this->delete_setting( 'type' );
			$this->set_setting( 'options', 'file' );
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
		
		return array( 'url' => $content );
	}
}
