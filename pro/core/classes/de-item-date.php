<?php
class De_Item_Date extends De_Item {
	public function __construct( $store, $settings ) {
		parent::__construct( $store, $settings );
		
		if ( ! $this->get_setting( 'type' ) || $this->get_setting( 'type' ) != 'date' ) {
			$this->delete_setting( 'type' );
			$this->set_setting( 'options', 'date' );
		}
	}

	public function output( $content = null ) {
		$attr = array();
		
		if ( $this->get_setting( 'container' ) ) {
			$container = $this->get_setting( 'container' );
		} else {
			$container = 'time';
		}
		
		$locale = get_locale();
		if ( empty( $locale ) ) {
			$locale = 'en_US';
		}
		$localize = $this->get_setting( 'localize' );
		if ( $localize && ! empty( $localize[ $locale ] ) ) {
			$format = de_datepicker_to_php( $localize[ $locale ] );
		} elseif ( $this->get_setting( 'format' ) ) {
			$format = de_datepicker_to_php( $this->get_setting( 'format' ) );
		} else {
			$format = get_option( 'date_format' );
		}
		if ( $this->get_setting( 'attr' ) && is_array( $this->get_setting( 'attr' ) ) ) {
			$attr = $this->get_setting( 'attr' );
		}
		
		$content_partial =  $this->output_partial( $content );
		
		// Show Direct Edit only for users who have proper permissions
		if ( De_Store::is_editable( $this ) ) {
			$attr[ 'data-reference' ] = $this->reference;
			if ( empty( $attr[ 'id' ] ) )
				$attr[ 'id' ] = $this->reference;
			$attr[ 'class' ] = ( isset( $attr[ 'class' ] ) ? $attr[ 'class' ] . ' direct-editable' : 'direct-editable' );
			$attr[ 'data-global-options' ] = $this->get_setting( 'options' );
			$attr[ 'data-local-options' ] = $this->build_local_options();
			$attr[ 'data-date' ] = $content_partial[ 'content' ];
			$attr[ 'data-date-datepicker' ] = mysql2date( 'd/m/Y H:i', $content_partial[ 'content' ] );
		}
		$attr[ 'datetime' ] = mysql2date( 'Y-m-d', $content_partial[ 'content' ] );
		
		$result = '<' . $container . self::attr_to_string( $attr ) . '>' . mysql2date( $format, $content_partial[ 'content' ], true ) . '</' . $container . '>';
		
		return $result;
	}
	
	public function output_partial( $content = null ) {
		if ( ! strlen( $content ) ) {
			if ( $this->get_setting( 'default' ) ) {
				$content = $this->get_setting( 'default' );
			} else {
				$content = current_time( 'mysql' );
			}
		}
		
		$content = mysql2date( 'Y-m-d H:i:s', $content );
		
		return array( 'content' => $content );
	}
}
