<?php
// Constants
define( 'DE_SECURITY_TIME_PERIOD', 5 );
define( 'DE_SECURITY_LOCKOUT_PERIOD', 60 );
define( 'DE_SECURITY_ATTEMPTS_NUMBER', 10 );

// Limit login attempts
function de_security_login_check() {
	global $wpdb;

	$value = de_security_get_ip();

	$table_name = $wpdb->prefix . 'de_login_attempts';
	$data = $wpdb->get_row( $wpdb->prepare( "SELECT *, (CASE when last_login is not NULL and DATE_ADD(last_login, INTERVAL " . DE_SECURITY_LOCKOUT_PERIOD . " MINUTE)>NOW() then 1 else 0 end) as denied FROM " . $table_name . " WHERE IP = '%s'", $value ) );

	if ( $data && $data->attempts >= DE_SECURITY_ATTEMPTS_NUMBER ) {
		if( $data->denied ) {
			return false;
		} else { 
			de_security_clear_login_attempts( $value );
			return true; 
		}
	}

	return true;
}

// Increase number of attempts
function de_security_add_login_attempt() {
	global $wpdb;

	$value = de_security_get_ip();

	$table_name = $wpdb->prefix . 'de_login_attempts';
	$data = $wpdb->get_row( $wpdb->prepare( "SELECT *, (CASE when last_login is not NULL and DATE_ADD(last_login, INTERVAL " . DE_SECURITY_TIME_PERIOD . " MINUTE)>NOW() then 1 else 0 end) as added FROM " . $table_name . " WHERE ip = '%s'", $value ) );
	if( $data && $data->added ) {
		$attempts = $data->attempts + 1;        
		$wpdb->query( $wpdb->prepare( "UPDATE " . $table_name . " SET attempts=" . $attempts . ", last_login=NOW() WHERE IP = '%s'", $value ) );
	} else {
		$wpdb->query( $wpdb->prepare( "INSERT INTO " . $table_name . " (attempts, IP, last_login) values (1, '%s', NOW())", $value ) );
	}
}

// Successful login
function de_security_clear_login_attempts() {
	global $wpdb;

	$value = de_security_get_ip();

	$table_name = $wpdb->prefix . 'de_login_attempts';
	$wpdb->query( $wpdb->prepare( "DELETE FROM " . $table_name . " WHERE IP = '%s'", $value ) );
}

// Get user IP
function de_security_get_ip() {
	//Just get the headers if we can or else use the SERVER global
	if ( function_exists( 'apache_request_headers' ) ) {
		$headers = apache_request_headers();
	} else {
		$headers = $_SERVER;
	}

	//Get the forwarded IP if it exists
	if ( array_key_exists( 'X-Forwarded-For', $headers ) && filter_var( $headers['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
		$the_ip = $headers['X-Forwarded-For'];
	} elseif ( array_key_exists( 'HTTP_X_FORWARDED_FOR', $headers ) && filter_var( $headers['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
		$the_ip = $headers['HTTP_X_FORWARDED_FOR'];
	} else {
		$the_ip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 );
	}

	return $the_ip;
}

// Honeypot
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
