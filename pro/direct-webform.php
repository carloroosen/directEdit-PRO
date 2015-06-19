<?php
// Global form variables
global $de_webform_id;
global $de_webform_errors;
global $de_webform_messages;
global $de_webform_values;
global $de_webform_search;
global $de_webform_replace;
global $de_webform_use_admin_email;
global $de_webform_use_user_email;
global $de_webform_success_page;
global $de_webform_success_message;
global $de_webform_admin_attachments;
$de_webform_admin_attachments = array();
global $de_webform_user_attachments;
$de_webform_user_attachments = array();

add_action( 'add_meta_boxes', 'de_webform_add_template_metabox' );
add_action( 'init', 'de_webform_capabilities' );
add_action( 'init', 'de_webform_create_post_types', 2 );
add_action( 'template_redirect', 'de_webform_use_honeypot' );
add_action( 'de_webform_form_setup', 'de_webform_setup' );
add_action( 'de_webform_form_validate', 'de_webform_validate' );
add_action( 'de_webform_form_action', 'de_webform_action' );
add_action( 'save_post', 'de_webform_save_template', 10, 2 );
add_action( 'template_include', 'de_webform_set_template', 0 );
add_action( 'template_include', 'de_webform_process', 20 );

function de_webform_add_template_metabox() {
	add_meta_box( 'de_webform_general', __( 'General', 'direct-edit' ), 'de_webform_general_metabox', 'de_webform', 'normal', 'core' );
	add_meta_box( 'de_webform_email_admin', __( 'Admin Email', 'direct-edit' ), 'de_webform_email_admin_metabox', 'de_webform', 'normal', 'core' );
	add_meta_box( 'de_webform_email_user', __( 'User Email', 'direct-edit' ), 'de_webform_email_user_metabox', 'de_webform', 'normal', 'core' );
	add_meta_box( 'postparentdiv', __( 'Form Template', 'direct-edit' ), 'de_webform_template_metabox', 'de_webform', 'side', 'core' );
}

function de_webform_general_metabox( $post ) {
	global $wp_post_types;

	$postId = $post->ID;
	
	$successPage = get_post_meta( $postId, 'de_success_page', true );
	$successMessage = get_post_meta( $postId, 'de_success_message', true );
	
	echo '<fieldset>';
	echo '<label for="de_success_page">' . __( 'Success page', 'direct-edit' ) . '</label>';
	echo '<br />';
	echo '<input type="text" id="de_success_page" name="de_success_page" value="' . esc_attr( $successPage ) . '" size="25" />';
	echo '<br />';
	echo '<br />';
	_e( 'or', 'direct-edit' );
	echo '<br />';
	echo '<br />';
	echo '<label for="de_success_message">' . __( 'Success message', 'direct-edit' ) . '</label>';
	echo '<br />';
	echo '<textarea id="de_success_message" name="de_success_message" style="width: 100%; height: 100px;">' . esc_textarea( $successMessage ) . '</textarea>';
	echo '</fieldset>';
}

function de_webform_email_admin_metabox( $post ) {
	$postId = $post->ID;
	$newPage = ( basename( $_SERVER['PHP_SELF'] ) == 'post-new.php' );
	
	$useAdminEmail = get_post_meta( $postId, 'de_use_admin_email', true );
	$adminFromUseGlobal = ( $newPage ? 1 : get_post_meta( $postId, 'de_admin_from_use_global', true ) );
	//$adminEmailFrom = ( $newPage ? get_option( 'admin_email' ) : get_post_meta( $postId, 'de_admin_email_from', true ) );
	$adminEmailFrom = ( $newPage ? '' : get_post_meta( $postId, 'de_admin_email_from', true ) );
	$adminToUseGlobal = ( $newPage ? 1 : get_post_meta( $postId, 'de_admin_to_use_global', true ) );
	//$adminEmailTo = get_post_meta( $postId, 'de_admin_email_to', true );
	$adminEmailTo = ( $newPage ? '' : get_post_meta( $postId, 'de_admin_email_to', true ) );
	$adminToBccUseGlobal = ( $newPage ? 1 : get_post_meta( $postId, 'de_admin_to_bcc_use_global', true ) );
	$adminEmailToBcc = ( $newPage ? '' : get_post_meta( $postId, 'de_admin_email_to_bcc', true ) );
	$adminEmailSubject = get_post_meta( $postId, 'de_admin_email_subject', true );
	$adminEmailBodyHtml = get_post_meta( $postId, 'de_admin_email_body_html', true );
	$adminEmailBody = get_post_meta( $postId, 'de_admin_email_body', true );
	$adminAttachUploads = get_post_meta( $postId, 'de_admin_attach_uploads', true );
	
	echo '<input type="hidden" name="de_use_admin_email" value="0" />';
	echo '<input type="checkbox" id="de_use_admin_email" name="de_use_admin_email" value="1"' . ( $useAdminEmail ? ' checked="checked"' : '' ) . ' />';
	echo ' <label for="de_use_admin_email">' . __( 'Use admin email', 'direct-edit' ) . '</label>';
	echo '<br />';
	echo '<br />';
	echo '<label>' . __( 'From', 'direct-edit' ) . '</label>';
	echo '<br />';
	echo '<input type="hidden" name="de_admin_from_use_global" value="0" /><input type="checkbox" id="de_admin_from_use_global" name="de_admin_from_use_global" value="1" ' . checked( $adminFromUseGlobal, 1, false ) . ' onclick="if(jQuery(\'#de_admin_from_use_global\').prop(\'checked\')) {jQuery(\'#de_admin_email_from_span\').hide();} else {jQuery(\'#de_admin_email_from_span\').show();}" /> Use global admin email';
	echo '<br />';
	echo '<span id="de_admin_email_from_span"' . ( $adminFromUseGlobal ? ' style="display: none;"' : '' ) . '>';
	echo '<input type="text" id="de_admin_email_from" name="de_admin_email_from" value="' . esc_attr( $adminEmailFrom ) . '" size="25" />';
	echo '<br />';
	echo '</span>';
	echo '<label>' . __( 'To', 'direct-edit' ) . '</label>';
	echo '<br />';
	echo '<input type="hidden" name="de_admin_to_use_global" value="0" /><input type="checkbox" id="de_admin_to_use_global" name="de_admin_to_use_global" value="1" ' . checked( $adminToUseGlobal, 1, false ) . ' onclick="if(jQuery(\'#de_admin_to_use_global\').prop(\'checked\')) {jQuery(\'#de_admin_email_to_span\').hide();} else {jQuery(\'#de_admin_email_to_span\').show();}" /> Use global admin email';
	echo '<br />';
	echo '<span id="de_admin_email_to_span"' . ( $adminToUseGlobal ? ' style="display: none;"' : '' ) . '>';
	echo '<input type="text" id="de_admin_email_to" name="de_admin_email_to" value="' . esc_attr( $adminEmailTo ) . '" size="25" />';
	echo '<br />';
	echo '</span>';
	echo '<label>' . __( 'Bcc', 'direct-edit' ) . '</label>';
	echo '<br />';
	echo '<input type="hidden" name="de_admin_to_bcc_use_global" value="0" /><input type="checkbox" id="de_admin_to_bcc_use_global" name="de_admin_to_bcc_use_global" value="1" ' . checked( $adminToBccUseGlobal, 1, false ) . ' onclick="if(jQuery(\'#de_admin_to_bcc_use_global\').prop(\'checked\')) {jQuery(\'#de_admin_email_to_bcc_span\').hide();} else {jQuery(\'#de_admin_email_to_bcc_span\').show();}" /> Use global bcc';
	echo '<br />';
	echo '<span id="de_admin_email_to_bcc_span"' . ( $adminToBccUseGlobal ? ' style="display: none;"' : '' ) . '>';
	echo '<input type="text" id="de_admin_email_to_bcc" name="de_admin_email_to_bcc" value="' . esc_attr( $adminEmailToBcc ) . '" size="25" />';
	echo '<br />';
	echo '</span>';
	echo '<label for="de_admin_email_subject">' . __( 'Subject', 'direct-edit' ) . '</label>';
	echo '<br />';
	echo '<input type="text" id="de_admin_email_subject" name="de_admin_email_subject" value="' . esc_attr( $adminEmailSubject ) . '" size="25" />';
	echo '<br />';
	echo '<br />';
	echo '<input type="hidden" name="de_admin_email_body_html" value="0" />';
	echo '<input type="checkbox" id="de_admin_email_body_html" name="de_admin_email_body_html" value="1"' . ( $adminEmailBodyHtml ? ' checked="checked"' : '' ) . ' />';
	echo ' <label for="de_admin_email_body_html">' . __( 'Use html content type', 'direct-edit' ) . '</label>';
	echo '<br />';
	echo '<br />';
	echo '<label for="de_admin_email_body">' . __( 'Body', 'direct-edit' ) . '</label>';
	echo '<br />';
	echo '<textarea id="de_admin_email_body" name="de_admin_email_body" style="width: 100%; height: 200px;">' . esc_textarea( $adminEmailBody ) . '</textarea>';
	echo '<br />';
	echo '<br />';
	echo '<input type="hidden" name="de_admin_attach_uploads" value="0" />';
	echo '<input type="checkbox" id="de_admin_attach_uploads" name="de_admin_attach_uploads" value="1"' . ( $adminAttachUploads ? ' checked="checked"' : '' ) . ' />';
	echo ' <label for="de_admin_attach_uploads">' . __( 'Attach uploads', 'direct-edit' ) . '</label>';
}

function de_webform_email_user_metabox( $post ) {
	$postId = $post->ID;
	$newPage = ( basename( $_SERVER['PHP_SELF'] ) == 'post-new.php' );
	
	$useUserEmail = get_post_meta( $postId, 'de_use_user_email', true );
	$userFromUseGlobal = ( $newPage ? 1 : get_post_meta( $postId, 'de_admin_from_use_global', true ) );
	//$userEmailFrom = ( $newPage ? get_option( 'admin_email' ) : get_post_meta( $postId, 'de_user_email_from', true ) );
	$userEmailFrom = ( $newPage ? '' : get_post_meta( $postId, 'de_user_email_from', true ) );
	$userToUseGlobal = ( $newPage ? 1 : get_post_meta( $postId, 'de_user_to_use_global', true ) );
	$userEmailTo = get_post_meta( $postId, 'de_user_email_to', true );
	$userToBccUseGlobal = ( $newPage ? 1 : get_post_meta( $postId, 'de_user_to_bcc_use_global', true ) );
	$userEmailToBcc = ( $newPage ? '' : get_post_meta( $postId, 'de_user_email_to_bcc', true ) );
	$userEmailSubject = get_post_meta( $postId, 'de_user_email_subject', true );
	$userEmailBodyHtml = get_post_meta( $postId, 'de_user_email_body_html', true );
	$userEmailBody = get_post_meta( $postId, 'de_user_email_body', true );
	$userAttachUploads = get_post_meta( $postId, 'de_user_attach_uploads', true );
	
	echo '<input type="hidden" name="de_use_user_email" value="0" />';
	echo '<input type="checkbox" id="de_use_user_email" name="de_use_user_email" value="1"' . ( $useUserEmail ? ' checked="checked"' : '' ) . ' />';
	echo ' <label for="de_use_user_email">' . __( 'Use user email', 'direct-edit' ) . '</label>';
	echo '<br />';
	echo '<br />';
	echo '<label>' . __( 'From', 'direct-edit' ) . '</label>';
	echo '<br />';
	echo '<input type="hidden" name="de_user_from_use_global" value="0" /><input type="checkbox" id="de_user_from_use_global" name="de_user_from_use_global" value="1" ' . checked( $userFromUseGlobal, 1, false ) . ' onclick="if(jQuery(\'#de_user_from_use_global\').prop(\'checked\')) {jQuery(\'#de_user_email_from_span\').hide();} else {jQuery(\'#de_user_email_from_span\').show();}" /> Use global admin email';
	echo '<br />';
	echo '<span id="de_user_email_from_span"' . ( $userFromUseGlobal ? ' style="display: none;"' : '' ) . '>';
	echo '<input type="text" id="de_user_email_from" name="de_user_email_from" value="' . esc_attr( $userEmailFrom ) . '" size="25" />';
	echo '<br />';
	echo '</span>';
	echo '<label for="de_user_email_to">' . __( 'To', 'direct-edit' ) . '</label>';
	echo '<br />';
	echo '<input type="hidden" name="de_user_to_use_global" value="0" /><input type="checkbox" id="de_user_to_use_global" name="de_user_to_use_global" value="1" ' . checked( $userToUseGlobal, 1, false ) . ' onclick="if(jQuery(\'#de_user_to_use_global\').prop(\'checked\')) {jQuery(\'#de_user_email_to_span\').hide();} else {jQuery(\'#de_user_email_to_span\').show();}" /> Use global admin email';
	echo '<br />';
	echo '<span id="de_user_email_to_span"' . ( $userToUseGlobal ? ' style="display: none;"' : '' ) . '>';
	echo '<input type="text" id="de_user_email_to" name="de_user_email_to" value="' . esc_attr( $userEmailTo ) . '" size="25" />';
	echo '<br />';
	echo '</span>';
	echo '<label>' . __( 'Bcc', 'direct-edit' ) . '</label>';
	echo '<br />';
	echo '<input type="hidden" name="de_user_to_bcc_use_global" value="0" /><input type="checkbox" id="de_user_to_bcc_use_global" name="de_user_to_bcc_use_global" value="1" ' . checked( $userToBccUseGlobal, 1, false ) . ' onclick="if(jQuery(\'#de_user_to_bcc_use_global\').prop(\'checked\')) {jQuery(\'#de_user_email_to_bcc_span\').hide();} else {jQuery(\'#de_user_email_to_bcc_span\').show();}" /> Use global bcc';
	echo '<br />';
	echo '<span id="de_user_email_to_bcc_span"' . ( $userToBccUseGlobal ? ' style="display: none;"' : '' ) . '>';
	echo '<input type="text" id="de_user_email_to_bcc" name="de_user_email_to_bcc" value="' . esc_attr( $userEmailToBcc ) . '" size="25" />';
	echo '<br />';
	echo '</span>';
	echo '<label for="de_user_email_subject">' . __( 'Subject', 'direct-edit' ) . '</label>';
	echo '<br />';
	echo '<input type="text" id="de_user_email_subject" name="de_user_email_subject" value="' . esc_attr( $userEmailSubject ) . '" size="25" />';
	echo '<br />';
	echo '<br />';
	echo '<input type="hidden" name="de_user_email_body_html" value="0" />';
	echo '<input type="checkbox" id="de_user_email_body_html" name="de_user_email_body_html" value="1"' . ( $userEmailBodyHtml ? ' checked="checked"' : '' ) . ' />';
	echo ' <label for="de_user_email_body_html">' . __( 'Use html content type', 'direct-edit' ) . '</label>';
	echo '<br />';
	echo '<br />';
	echo '<label for="de_user_email_body">' . __( 'Body', 'direct-edit' ) . '</label>';
	echo '<br />';
	echo '<textarea id="de_user_email_body" name="de_user_email_body" style="width: 100%; height: 200px;">' . esc_textarea( $userEmailBody ) . '</textarea>';
	echo '<br />';
	echo '<br />';
	echo '<input type="hidden" name="de_user_attach_uploads" value="0" />';
	echo '<input type="checkbox" id="de_user_attach_uploads" name="de_user_attach_uploads" value="1"' . ( $userAttachUploads ? ' checked="checked"' : '' ) . ' />';
	echo ' <label for="de_user_attach_uploads">' . __( 'Attach uploads', 'direct-edit' ) . '</label>';
}

function de_webform_template_metabox( $post ) {
	if ( 0 != count( de_webform_get_templates( $post->post_type ) ) ) {
		$template = get_post_meta( $post->ID, 'de_webform_template', true );
		?>
<label class="screen-reader-text" for="de_webform_template"><?php _e( 'Form Template', 'direct-edit' ) ?></label><select name="de_webform_template" id="de_webform_template">
<option value=""><?php _e( 'Select form template', 'direct-edit' ); ?></option>
<?php de_webform_template_dropdown( $post->post_type, $template ); ?>
</select>
		<?php
	}
}

function de_webform_get_templates( $post_type ) {
	$templates = wp_get_theme()->get_files( 'php', 2 );
	$form_templates = array();

	if ( is_array( $templates ) ) {
		$base = array( trailingslashit( get_stylesheet_directory() ), trailingslashit( get_stylesheet_directory() ) );

		foreach ( $templates as $template ) {
			$basename = str_replace( $base, '', $template );
			if ( $basename != 'functions.php' ) {
				// look for templates in '{$post_type}' folder
				if ( 0 !== strpos( $basename, $post_type ) )
					continue;

				$template_data = implode( '', file( $template ) );

				$name = '';
				if ( preg_match( '|Form Template:(.*)$|mi', $template_data, $name ) )
					$name = _cleanup_header_comment($name[ 1 ]);

				if ( !empty( $name ) ) {
					$form_templates[ trim( $name ) ] = $basename;
				}
			}
		}
	}

	return $form_templates;
}

function de_webform_template_dropdown( $post_type, $default = '' ) {
	$templates = de_webform_get_templates( $post_type );
	ksort( $templates );
	foreach ( array_keys( $templates ) as $template ) {
		if ( $default == $templates[ $template ] )
			$selected = " selected='selected'";
		else
			$selected = '';
		echo "\n\t<option value='".$templates[ $template ]."' $selected>$template</option>";
	}
}

function de_webform_capabilities() {
	// Add dE capabilities
	$admin = get_role( 'administrator' );
	if ( $admin && empty( $admin->capabilities[ 'edit_de_webform' ] ) ) {
		$admin->add_cap( 'edit_de_webform', true );
	}
	if ( $admin && empty( $admin->capabilities[ 'delete_de_webform' ] ) ) {
		$admin->add_cap( 'delete_de_webform', true );
	}
}

function de_webform_create_post_types() {
	register_post_type( 'de_webform',
		array(
			'labels' => array(
				'name' => __( 'Webforms', 'direct-edit' ),
				'singular_name' => __( 'Webform', 'direct-edit' )
			),
			'public' => true,
			'rewrite' => array( 'slug' => 'webforms', 'direct-edit' ),
			'supports' => array( 'title', 'editor', 'author', 'page-attributes' ),
			'capabilities' => array(
				'edit_posts' => 'edit_de_webform',
				'edit_others_posts' => 'edit_de_webform',
				'publish_posts' => 'publish_posts',
				'read_private_posts' => 'read_private_posts',
				'read' => 'read',
				'delete_posts' => 'delete_de_webform',
				'delete_private_posts' => 'delete_de_webform',
				'delete_published_posts' => 'delete_de_webform',
				'delete_others_posts' =>  'delete_de_webform',
				'edit_private_posts' => 'edit_de_webform',
				'edit_published_posts' => 'edit_de_webform',
				'edit_post' => 'edit_de_webform',
				'delete_post' => 'delete_de_webform',
				'read_post' => 'read'
			)
		)
	);
}

function de_webform_use_honeypot() {
	global $direct_queried_object;

	if ( isset( $direct_queried_object ) && $direct_queried_object->post_type == 'de_webform' && function_exists( 'de_security_use_honeypot' ) ) {
		de_security_use_honeypot();
	}
}

function de_webform_setup( $post ) {
	global $user_ID;
	global $de_webform_values;
	global $de_webform_search;
	global $de_webform_replace;
	global $de_webform_success_page;
	global $wpdb;
	
	if ( $post->ID == get_option( 'de_login_form' ) || de_is_language_post( $post->ID, get_option( 'de_login_form' ) ) ) {
		/*
		 * Limit login attempts
		 */
		if ( get_option( 'de_limit_login_attempts' ) && function_exists( 'de_security_login_check' ) && ! de_security_login_check() ) {
			wp_redirect( home_url() );
			die();
		}
		
		/*
		 * If wp-login form redirect switched off we do redirect to wp-login
		 */
		if ( ! get_option( 'de_wp_login_redirect' ) ) {
			wp_redirect( de_get_login_form_permalink() );
			die();
		}
		
		if ( ! empty( $_REQUEST[ 'action' ] ) ) {
			if ( $_REQUEST[ 'action' ] == 'password-recovery' ) {
				/*
				 * Form default values
				 */
				$de_webform_values[ 'email' ] = '';

				/*
				 * If a user is logged in, he is redirected to the homepage
				 */
				if ( $user_ID ) {
					de_webform_conditional_redirect( home_url() );
				} else {
					/*
					 * If there is key & email params, we are looking for a user with the defined email and activation key
					 * If the user exists, we perform login
					 * If login form has Success page, we perform a redirect to it, else we perform a redirect to the homepage
					 */
					if( ! empty( $_REQUEST[ 'key' ] ) && ! empty( $_REQUEST[ 'email' ] ) ) {
						$key = $_REQUEST[ 'key' ];
						$email = $_REQUEST[ 'email' ];
						$userData = $wpdb->get_row( $wpdb->prepare( "SELECT ID, user_login, user_email FROM $wpdb->users WHERE user_activation_key = %s AND user_email = %s", $key, $email ) );
						if( ! empty( $userData ) ) {
							$user = get_userdata( $userData->ID );
							
							wp_set_current_user( $userData->ID, $userData->user_login );
							wp_set_auth_cookie( $userData->ID );
							do_action( 'wp_login', $userData->user_login );

							$wpdb->update( $wpdb->users, array( 'user_activation_key' => '' ), array( 'user_login' => $userData->user_login ) );
							
							/*
							 * New password form redirect
							 */
							wp_redirect( add_query_arg( 'action', 'set-new-password', de_get_login_form_permalink() ) );
							exit;
						}
					}
				}
			} elseif( $_REQUEST[ 'action' ] == 'set-new-password' ) {
				if ( ! $user_ID ) {
					wp_redirect( de_get_login_form_permalink() );
					die();
				}
			}
		} else {
			/*
			 * Form default values
			 */
			$de_webform_values[ 'email' ] = '';

			/*
			 * If a user is logged in, he is redirected to the page with his settings
			 */
			if ( $user_ID ) {
				de_webform_conditional_redirect( home_url() );
			}
		}
	}
}

function de_webform_validate( $post ) {
	global $wpdb;
	global $user_ID;
	global $de_webform_errors;
	global $de_webform_values;
	
	if ( $post->ID == get_option( 'de_login_form' ) || de_is_language_post( $post->ID, get_option( 'de_login_form' ) ) ) {
		if ( ! empty( $_REQUEST[ 'action' ] ) ) {
			if ( $_REQUEST[ 'action' ] == 'password-recovery' ) {
				$de_webform_values[ 'email' ] = sanitize_text_field( $_POST[ 'email' ] );

				if( empty( $de_webform_values[ 'email' ] ) || ! filter_var( $de_webform_values[ 'email' ], FILTER_VALIDATE_EMAIL ) || email_exists( $de_webform_values[ 'email' ] ) == false ) {
					$de_webform_errors[ 'email' ] = __( 'Wrong email address.', 'direct-edit' );
				}
			} elseif( $_REQUEST[ 'action' ] == 'set-new-password' ) {
				$user_data = get_userdata( $user_ID );
				
				$de_webform_values[ 'password_new' ] = sanitize_text_field( $_POST[ 'password_new' ] );
				
				if( empty( $de_webform_values[ 'password_new' ] ) ) {
					$de_webform_errors[ 'password_new' ] = __( 'You have no password specified.', 'direct-edit' );
				}
				if( get_option( 'de_strong_passwords' ) && de_check_password_strength( $de_webform_values[ 'password_new' ], $user_data->user_login ) != 4 ) {
					$de_webform_errors[ 'password_new' ] = __( 'Please make the password a strong one.', 'direct-edit' );
				}
			}
		} else {
			$de_webform_values[ 'email' ] = sanitize_text_field( $_POST[ 'email' ] );
			$password = sanitize_text_field( $_POST[ 'password' ] );

			if( empty( $de_webform_values[ 'email' ] ) || ! filter_var( $de_webform_values[ 'email' ], FILTER_VALIDATE_EMAIL ) || email_exists( $de_webform_values[ 'email' ] ) == false ) {
				$de_webform_errors[ 'email' ] = __( 'You have no email specified.', 'direct-edit' );
				if ( get_option( 'de_limit_login_attempts' ) && function_exists( 'de_security_add_login_attempt' ) ) {
					de_security_add_login_attempt();
				}
			}
			if( empty( $password ) ) {
				$de_webform_errors[ 'password' ] = __( 'You have no password specified.', 'direct-edit' );
				if ( get_option( 'de_limit_login_attempts' ) && function_exists( 'de_security_add_login_attempt' ) ) {
					de_security_add_login_attempt();
				}
			}
		}
	}
}

function de_webform_action( $post ) {
	global $wpdb;
	global $user_ID;
	global $de_webform_errors;
	global $de_webform_values;
	global $de_webform_search;
	global $de_webform_replace;
	global $de_webform_success_message;
	global $de_webform_success_page;
	global $de_webform_use_user_email;
	
	if ( $post->ID == get_option( 'de_login_form' ) || de_is_language_post( $post->ID, get_option( 'de_login_form' ) ) ) {
		if ( ! empty( $_REQUEST[ 'action' ] ) ) {
			if ( $_REQUEST[ 'action' ] == 'password-recovery' ) {
				$email = $de_webform_values[ 'email' ];
				$userData = get_user_by( 'email', $email );
				$login = $userData->user_login;

				/*
				 * Generate an autologin key
				 */
				$key = wp_generate_password( 20, false );
				$wpdb->update( $wpdb->users, array( 'user_activation_key' => $key ), array( 'user_login' => $login ) );

				/*
				 * Email values
				 */
				$de_webform_search[] = '{link}';
				$de_webform_replace[] = add_query_arg( array( 'action' => 'password-recovery', 'key' => urlencode( $key ), 'email' => urlencode( $email ) ), de_get_login_form_permalink() );
				
				$de_webform_success_page = '';
				$de_webform_success_message = __( 'An email with a login link has been sent to {email}.', 'direct-edit' );
				$de_webform_use_user_email = 1;
			} elseif( $_REQUEST[ 'action' ] == 'set-new-password' ) {
				wp_set_password( $de_webform_values[ 'password_new' ], $user_ID );
				
				$de_webform_success_page = de_get_login_form_permalink();
			}
		} else {
			$email = $de_webform_values[ 'email' ];
			$password = sanitize_text_field( $_POST[ 'password' ] );
			$user = get_user_by( 'email', $email );
			$username = $user->user_login;

			$loginData[ 'user_login' ] = $username;  
			$loginData[ 'user_password' ] = $password;  
			$loginData[ 'remember' ] = false;  

			/*
			 * Login attempt
			 */
			$userVerify = wp_signon( $loginData );   

			if ( is_wp_error( $userVerify ) ) {
				$de_webform_errors[] = $userVerify->get_error_message();
				if ( get_option( 'de_limit_login_attempts' ) && function_exists( 'de_security_add_login_attempt' ) ) {
					de_security_add_login_attempt();
				}
			}
			
			if ( current_user_can( 'edit_de_frontend' ) ) {
				$de_webform_success_page = add_query_arg( 'v', time(), $de_webform_success_page );
			}
		}
	}
}

function de_webform_save_template( $post_id, $post ) {
	if ( $post->post_type == 'de_webform' ) {
		if ( ! current_user_can( 'edit_de_webform' ) )
			return false;

		if ( basename( $_SERVER['PHP_SELF'] ) == 'post.php' || basename( $_SERVER['PHP_SELF'] ) == 'post-new.php' ) {
			update_post_meta( $post->ID, 'de_webform_template', $_POST['de_webform_template'] );
			
			update_post_meta( $post->ID, 'de_success_page', $_POST['de_success_page'] );
			update_post_meta( $post->ID, 'de_success_message', $_POST['de_success_message'] );
			
			update_post_meta( $post->ID, 'de_use_admin_email', $_POST['de_use_admin_email'] );
			update_post_meta( $post->ID, 'de_admin_from_use_global', $_POST['de_admin_from_use_global'] );
			update_post_meta( $post->ID, 'de_admin_email_from', $_POST['de_admin_email_from'] );
			update_post_meta( $post->ID, 'de_admin_to_use_global', $_POST['de_admin_to_use_global'] );
			update_post_meta( $post->ID, 'de_admin_email_to', $_POST['de_admin_email_to'] );
			update_post_meta( $post->ID, 'de_admin_to_bcc_use_global', $_POST['de_admin_to_bcc_use_global'] );
			update_post_meta( $post->ID, 'de_admin_email_to_bcc', $_POST['de_admin_email_to_bcc'] );
			update_post_meta( $post->ID, 'de_admin_email_subject', $_POST['de_admin_email_subject'] );
			update_post_meta( $post->ID, 'de_admin_email_body_html', $_POST['de_admin_email_body_html'] );
			update_post_meta( $post->ID, 'de_admin_email_body', $_POST['de_admin_email_body'] );
			update_post_meta( $post->ID, 'de_admin_attach_uploads', $_POST['de_admin_attach_uploads'] );
			
			update_post_meta( $post->ID, 'de_use_user_email', $_POST['de_use_user_email'] );
			update_post_meta( $post->ID, 'de_user_from_use_global', $_POST['de_user_from_use_global'] );
			update_post_meta( $post->ID, 'de_user_email_from', $_POST['de_user_email_from'] );
			update_post_meta( $post->ID, 'de_user_to_use_global', $_POST['de_user_to_use_global'] );
			update_post_meta( $post->ID, 'de_user_email_to', $_POST['de_user_email_to'] );
			update_post_meta( $post->ID, 'de_user_to_bcc_use_global', $_POST['de_user_to_bcc_use_global'] );
			update_post_meta( $post->ID, 'de_user_email_to_bcc', $_POST['de_user_email_to_bcc'] );
			update_post_meta( $post->ID, 'de_user_email_subject', $_POST['de_user_email_subject'] );
			update_post_meta( $post->ID, 'de_user_email_body_html', $_POST['de_user_email_body_html'] );
			update_post_meta( $post->ID, 'de_user_email_body', $_POST['de_user_email_body'] );
			update_post_meta( $post->ID, 'de_user_attach_uploads', $_POST['de_user_attach_uploads'] );
		}
	}
}

function de_webform_set_template( $template ) {
	global $direct_queried_object;
	
	if ( $direct_queried_object && $direct_queried_object->post_type == 'de_webform' && get_post_meta( $direct_queried_object->ID, 'de_webform_template', true ) && file_exists( get_stylesheet_directory() . '/' . get_post_meta( $direct_queried_object->ID, 'de_webform_template', true ) ) ) {
		$template = get_stylesheet_directory() . '/' . get_post_meta( $direct_queried_object->ID, 'de_webform_template', true );
	}
	
	return $template;
}

function de_webform_process( $template ) {
	global $post;
	global $de_webform_errors;
	global $de_webform_messages;
	global $de_webform_search;
	global $de_webform_replace;
	global $de_webform_use_admin_email;
	global $de_webform_use_user_email;
	global $de_webform_success_page;
	global $de_webform_success_message;
	global $de_webform_admin_attachments;
	global $de_webform_user_attachments;

	$de_webform_search = array();
	$de_webform_replace = array();

	if ( is_object( $post ) && $post->post_type == 'de_webform' ) {
		// Retrieve form params
		$postId = $post->ID;
		$de_webform_success_page = get_post_meta( $postId, 'de_success_page', true );
		$de_webform_success_message = get_post_meta( $postId, 'de_success_message', true );
		$de_webform_use_admin_email = get_post_meta( $postId, 'de_use_admin_email', true );
		$adminEmailFrom = ( get_post_meta( $postId, 'de_admin_from_use_global', true ) ? get_option( 'de_global_admin_email' ) : get_post_meta( $postId, 'de_admin_email_from', true ) );
		$adminEmailTo = ( get_post_meta( $postId, 'de_admin_to_use_global', true ) ? get_option( 'de_global_admin_email' ) : get_post_meta( $postId, 'de_admin_email_to', true ) );
		$adminEmailToBcc = ( get_post_meta( $postId, 'de_admin_to_bcc_use_global', true ) ? get_option( 'de_global_admin_email_bcc' ) : get_post_meta( $postId, 'de_admin_email_to_bcc', true ) );
		$adminEmailSubject = get_post_meta( $postId, 'de_admin_email_subject', true );
		$adminEmailBodyHtml = get_post_meta( $postId, 'de_admin_email_body_html', true );
		$adminEmailBody = get_post_meta( $postId, 'de_admin_email_body', true );
		$adminEmailAttachUploads = get_post_meta( $postId, 'de_admin_attach_uploads', true );
		$de_webform_use_user_email = get_post_meta( $postId, 'de_use_user_email', true );
		$userEmailFrom = ( get_post_meta( $postId, 'de_user_from_use_global', true ) ? get_option( 'de_global_admin_email' ) : get_post_meta( $postId, 'de_user_email_from', true ) );
		$userEmailTo = ( get_post_meta( $postId, 'de_user_to_use_global', true ) ? get_option( 'de_global_admin_email' ) : get_post_meta( $postId, 'de_user_email_to', true ) );
		$userEmailToBcc = ( get_post_meta( $postId, 'de_user_to_bcc_use_global', true ) ? get_option( 'de_global_admin_email_bcc' ) : get_post_meta( $postId, 'de_user_email_to_bcc', true ) );
		$userEmailSubject = get_post_meta( $postId, 'de_user_email_subject', true );
		$userEmailBodyHtml = get_post_meta( $postId, 'de_user_email_body_html', true );
		$userEmailBody = get_post_meta( $postId, 'de_user_email_body', true );
		$userEmailAttachUploads = get_post_meta( $postId, 'de_user_attach_uploads', true );

		do_action( 'de_webform_form_setup', $post );
		
		if( $_SERVER[ 'REQUEST_METHOD' ] == 'POST' ) {
			// Check uploads
			$uploads_to_delete = array();
			if ( ! empty( $_FILES ) && is_array( $_FILES ) ) {
				include_once( ABSPATH . 'wp-admin/includes/file.php' );
				
				foreach ( $_FILES as $key => $file ) {
					if ( $file[ 'error' ] == UPLOAD_ERR_NO_FILE ) {
						continue;
					}elseif ( ! empty( $file[ 'error' ] ) ) {
						$de_webform_errors[ $key ] = __( 'File uploading error.', 'direct-edit' );
					} else {
						$result = wp_handle_upload( $file, array( 'test_form' => FALSE ) );
						
						if ( isset( $result[ 'file' ] ) ) {
							$uploads_to_delete[] = $result[ 'file' ];
							
							if ( $adminEmailAttachUploads ) {
								$de_webform_admin_attachments[] = $result[ 'file' ];
							}
							if ( $userEmailAttachUploads ) {
								$de_webform_user_attachments[] = $result[ 'file' ];
							}
						} else {
							$de_webform_errors[ $key ] = __( 'File uploading error.', 'direct-edit' );
						}
					}
				}
			}

			if ( get_option( 'de_honeypot' ) ) {
				// Honeyspot check
				if ( ! empty( $_POST[ 'question' ] ) ) {
					die();
				}
			}

			do_action( 'de_webform_form_validate', $post );

			if ( empty( $de_webform_errors ) ) {
				foreach( $_POST as $key => $value) {
					$de_webform_search[] = '{' . $key . '}';
					$de_webform_replace[] = trim( $value );
				}
				
				do_action( 'de_webform_form_action', $post );
				
				if ( empty( $de_webform_errors ) ) {
					// Admin email
					$adminEmailFrom = str_replace( $de_webform_search, $de_webform_replace, $adminEmailFrom );
					$adminEmailTo = str_replace( $de_webform_search, $de_webform_replace, $adminEmailTo );
					$adminEmailSubject = str_replace( $de_webform_search, $de_webform_replace, $adminEmailSubject );
					$adminEmailBody = str_replace( $de_webform_search, $de_webform_replace, $adminEmailBody );

					if ( $de_webform_use_admin_email ) {
						//if( filter_var( $adminEmailFrom, FILTER_VALIDATE_EMAIL ) && filter_var( $adminEmailTo, FILTER_VALIDATE_EMAIL ) ) {
							$blogname = wp_specialchars_decode( get_option('blogname'), ENT_QUOTES );
							$headers = 'From: ' . $blogname . ' <' . $adminEmailFrom . ">\r\n";
							if ( $adminEmailToBcc ) {
								$headers .= 'Bcc: ' . $adminEmailToBcc . "\r\n";
							}
							if ( $adminEmailBodyHtml )
								$headers .= 'Content-type: text/html' . "\r\n";
							if ( count( $de_webform_admin_attachments ) > 0 ) {
								wp_mail( $adminEmailTo, $adminEmailSubject, $adminEmailBody, $headers, $de_webform_admin_attachments );
							} else {
								wp_mail( $adminEmailTo, $adminEmailSubject, $adminEmailBody, $headers );
							}
						//}
					}

					// User email
					$userEmailFrom = str_replace( $de_webform_search, $de_webform_replace, $userEmailFrom );
					$userEmailTo = str_replace( $de_webform_search, $de_webform_replace, $userEmailTo );
					$userEmailSubject = str_replace( $de_webform_search, $de_webform_replace, $userEmailSubject );
					$userEmailBody = str_replace( $de_webform_search, $de_webform_replace, $userEmailBody );

					if ( $de_webform_use_user_email ) {
						//if( filter_var( $userEmailFrom, FILTER_VALIDATE_EMAIL ) && filter_var( $userEmailTo, FILTER_VALIDATE_EMAIL ) ) {
							$blogname = wp_specialchars_decode( get_option('blogname'), ENT_QUOTES );
							$headers = 'From: ' . $blogname . ' <' . $userEmailFrom . ">\r\n";
							if ( $userEmailToBcc ) {
								$headers .= 'Bcc: ' . $userEmailToBcc . "\r\n";
							}
							if ( $userEmailBodyHtml )
								$headers .= 'Content-type: text/html' . "\r\n";
							if ( count( $de_webform_user_attachments ) > 0 ) {
								wp_mail( $userEmailTo, $userEmailSubject, $userEmailBody, $headers, $de_webform_user_attachments );
							} else {
								wp_mail( $userEmailTo, $userEmailSubject, $userEmailBody, $headers );
							}
						//}
					}

					// Success page redirect
					if ( $de_webform_success_page ) {
						$link = str_replace( $de_webform_search, $de_webform_replace, $de_webform_success_page );
						$link = ( strpos( $link, 'http://' ) === false && strpos( $link, 'https://' ) === false ? get_bloginfo( 'url' ) . $link : $link );
						if ( $link )
							header( 'Location: ' . $link );
					} elseif( $de_webform_success_message ) {
						$de_webform_messages[] = str_replace( $de_webform_search, $de_webform_replace, $de_webform_success_message );
					}
				}
			}
			
			// Delete files after sending them
			foreach ( $uploads_to_delete as $upload_to_delete ) {
				@unlink( $upload_to_delete );
			}
		}
	}
	
	return $template;
}

// Redirect helper
function de_webform_conditional_redirect( $location, $form_id = '' ) {
	global $de_webform_id;
	
	if ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_de_frontend' ) ) {
		$de_webform_id = $form_id;
		add_action( 'wp_print_footer_scripts', 'de_webform_disable', 10 );
	} else {
		wp_redirect( $location );
		exit;
	}
}

function de_webform_disable() {
	global $de_webform_id;
	
	?>
<script>
	jQuery(document).ready(function() {
		jQuery('form<?php echo ( $de_webform_id ? '#' . $de_webform_id : '' ); ?>').submit(function() {return false});
	});
</script>
	<?php
}
