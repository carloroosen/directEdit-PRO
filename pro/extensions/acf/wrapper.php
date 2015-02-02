<?php
de_pro_include( DIRECT_PATH . 'pro/extensions/acf/classes/de-store-acf.php' );

add_filter( 'acf/load_value/type=text', 'de_acf_load_value', 10, 3 );

function de_acf_load_value( $value, $post_id, $field ) {
	if ( is_admin() ) {
		$result = $value;
	} else {
		$post = get_post( $post_id );
	
		if ( current_user_can( 'edit_post', $post->ID ) || current_user_can( 'edit_de_frontend' ) ) {
			$result = '';
			
			$class = 'De_Item_Text';
			try {
				$settings = array(
					'container' => 'span',
					'format' => 'plain',
					'postId' => $post->ID,
					'fieldKey' => $field[ 'key' ],
					'unwrap' => true
				);
				$item = new $class( 'acf', $settings );

				$result = $item->output( $value );
			} catch ( Exception $e ) {
			}
		} else {
			$result = de_encode_emails( $value );
		}
	}
	
	return $result;
}
