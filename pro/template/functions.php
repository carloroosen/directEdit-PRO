<?php
add_action( 'init', 'de_register_menus' );

function de_register_menus() {
	register_nav_menu( 'direct_main', __( 'Main Menu' ) );
}
