<?php
class De_Item_Image extends De_Item {
	public function __construct( $store, $settings ) {
		parent::__construct( $store, $settings );
		
		if ( ! $this->get_setting( 'type' ) || $this->get_setting( 'type' ) != 'image' ) {
			$this->delete_setting( 'type' );
			$this->set_setting( 'options', 'image' );
		}
	}

	public function output( $content = null ) {
		$content_partial =  $this->output_partial( $content );
		return $content_partial[ 'content' ];
	}
	
	public function output_partial( $content = null ) {
		return array( 'content' => $this->output_partial_image( $content ) );
	}
	
	public function get_src( $id ) {
		global $de_snippet_image;
		
		$content = '';
		
		if ( $id ) {
			$data = wp_get_attachment_metadata( $id );
		}
		
		$s = $this->get_setting( 'default' );
		if( $s && empty( $data ) ) {
			if ( is_array( $s ) ) {
				if( isset( $s[ 'src' ] ) )
					$content = $s[ 'src' ];
			} else {
				$content = $s;
			}
		}

		if ( ! empty( $data ) ) {
			$wp_upload_dir = wp_upload_dir();
			$file = $data[ 'file' ];
			$info = pathinfo( $file );
			$dir = $info['dirname'];
			if ( $this->get_setting( 'useCopy' ) && ! empty( $data[ 'sizes' ][ $de_snippet_image->mode ][ 'copies' ][ $this->get_setting( 'useCopy' ) ] ) ) {
				$content = $wp_upload_dir[ 'baseurl' ] . '/' . $dir . '/' . $data[ 'sizes' ][ $de_snippet_image->mode ][ 'copies' ][ $this->get_setting( 'useCopy' ) ][ 'file' ];
			} else {
				$content = $wp_upload_dir[ 'baseurl' ] . '/' . $dir . '/' . $data[ 'sizes' ][ $de_snippet_image->mode ][ 'file' ];
			}
			if ( De_Store::is_editable( $this ) )
				$content .= '?v=' . time();
		}
		
		return $content;
	}
}
