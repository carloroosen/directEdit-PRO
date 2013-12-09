<?php
class De_Item_List extends De_Item {
	public $list;
	
	public function __construct( $store, $settings ) {
		parent::__construct( $store, $settings );
		
		if ( ! $this->get_setting( 'type' ) || $this->get_setting( 'type' ) != 'list' ) {
			$this->delete_setting( 'type' );
			$this->set_setting( 'options', 'list' );
		}
	}

	public function output( $content = null ) {
		$container = 'div';
		$attr = array();
		
		if ( $this->get_setting( 'container' ) ) {
			$container = $this->get_setting( 'container' );
		}
		if ( $this->get_setting( 'attr' ) && is_array( $this->get_setting( 'attr' ) ) ) {
			$attr = $this->get_setting( 'attr' );
		}
		
		// Show Direct Edit only for users who have proper permissions
		if ( De_Store::is_editable( $this ) ) {
			$attr[ 'data-reference' ] = $this->reference;
			if ( empty( $attr[ 'id' ] ) )
				$attr[ 'id' ] = $this->reference;
			$attr[ 'data-definition' ] = $this->list;
			$attr[ 'class' ] = ( isset( $attr[ 'class' ] ) ? $attr[ 'class' ] . ' direct-editable' : 'direct-editable' );
			$attr[ 'data-global-options' ] = $this->get_setting( 'options' );
			$attr[ 'data-local-options' ] = $this->build_local_options();
		}
		
		$content_partial =  $this->output_partial( $content );
		$result = '<' . $container . self::attr_to_string( $attr ) . '>' . $content_partial[ 'content' ] . '</' . $container . '>';
		
		return $result;
	}
	
	// In fact we don't use $content here. Output is based on $this->list.
	public function output_partial( $content = null ) {
		global $de_snippet_list;
		
		$content = $de_snippet_list->snippet( $this, $this->get_setting( 'snippet' ) );
		
		return array( 'content' => $content );
	}
}
