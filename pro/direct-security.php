<?php
// Security functions
function de_security_use_honeypot() {
	if ( ! has_action( 'wp_head', 'de_security_print_custom_css' ) ) {
		add_action( 'wp_head', 'de_security_print_custom_css' );
	}
}

function de_security_print_custom_css() {
	if ( get_option( 'de_honeypot' ) ) {
	?>
	<style>
		.question {
			position: absolute;
			left: -10000px;
		}
	</style>
	<?php
	}
}
