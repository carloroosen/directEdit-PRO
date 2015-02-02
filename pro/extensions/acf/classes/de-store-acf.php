<?php
class De_Store_Acf {
	public static function is_editable( De_Item $item ) {
		return current_user_can( 'edit_de_frontend' );
	}
	
	public static function read( De_Item $item ) {
		return get_field( $item->get_setting( 'fieldName' ), $item->get_setting( 'postId' ), ! De_Store::is_editable( $item ) );
	}
	
	public static function write( De_Item $item, $field, $content ) {
		update_field( ( $item->get_setting( 'fieldKey' ) ? $item->get_setting( 'fieldKey' ) : $item->get_setting( 'fieldName' ) ), $content, $item->get_setting( 'postId' ) );

		return $content;
	}
}
