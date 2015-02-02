<?php
add_action( 'de_custom_jobs_setup', 'de_custom_jobs_create_form' );
add_action( 'de_add_page_options', 'de_custom_jobs_page_options' );
add_action( 'de_save_page_options', 'de_custom_jobs_save_page_options' );
add_action( 'de_webform_form_setup', 'de_custom_jobs_form_setup' );
add_action( 'de_webform_form_validate', 'de_custom_jobs_form_validate' );

function de_custom_jobs_create_form( $post_type_slug ) {
	if ( post_type_exists( 'de_webform' ) && ! ( get_option( 'de_' . $post_type_slug . '_job_application' ) && get_post( get_option( 'de_' . $post_type_slug . '_job_application' ) ) ) ) {
		$title = __( 'Job Application', 'direct-edit' );
		
		$webformPost = array(
			'post_title' => $title,
			'post_content' => '',
			'post_status' => 'publish',
			'post_date' => date('Y-m-d H:i:s'),
			'post_author' => $user_ID,
			'post_type' => 'de_webform',
			'post_category' => array( 0 )
		);

		$webformPostId = wp_insert_post( $webformPost );
		$slug = De_Url::register_url( $webformPostId, sanitize_title( $title ) );
		
		update_post_meta( $webformPostId, 'de_webform_template', 'custom/' . $post_type_slug . '/de_webform_job_application.php' );
		
		if ( De_Language_Wrapper::has_multilanguage() ) {
			De_Language_Wrapper::set_post_language( $webformPostId, De_Language_Wrapper::get_default_language() );
			De_Language_Wrapper::create_language_posts( $webformPostId );
			
			update_post_meta( $webformPostId, 'de_success_page', '/' . De_Language_Wrapper::get_default_language() . '/' );
			update_post_meta( $webformPostId, 'de_use_admin_email', 1 );
			update_post_meta( $webformPostId, 'de_admin_email_from', get_option( 'admin_email' ) );
			update_post_meta( $webformPostId, 'de_admin_email_to', get_option( 'admin_email' ) );
			update_post_meta( $webformPostId, 'de_admin_email_subject', __( 'Job Application' ) );
			update_post_meta( $webformPostId, 'de_admin_email_body_html', 1 );
			update_post_meta( $webformPostId, 'de_admin_email_body', '
<p>
<table>
<tr><td>' . __( 'job' ) . '</td><td>{job}</td><td></tr>
<tr><td>' . __( 'name' ) . '</td><td>{name}</td><td></tr>
<tr><td>' . __( 'email' ) . '</td><td><a mailto="{email}">{email}</a></td><td></tr>
<tr><td>' . __( 'phone' ) . '</td><td>{phone}</td><td></tr>
<tr><td>' . __( 'comments' ) . '</td><td>{comments}</td><td></tr>
</table>
</p>
			' );
			update_post_meta( $webformPostId, 'de_admin_attach_uploads', 1 );
			
			foreach( De_Language_Wrapper::get_language_posts( $webformPostId ) as $lang => $lang_post ) {
				if ( $lang_post->ID == $loginPostId )
					continue;
				
				$data = array(
					'ID' => $lang_post->ID,
					'post_title' => $title,
					'post_name' => sanitize_title( $title )
				);
				wp_update_post( $data );

				De_Url::register_url( $lang_post->ID, sanitize_title( $title ) );

				update_post_meta( $lang_post->ID, 'de_webform_template', 'custom/' . $post_type_slug . '/de_webform_job_application.php' );
				update_post_meta( $lang_post->ID, 'de_success_page', "/$lang/" );
				update_post_meta( $lang_post->ID, 'de_use_admin_email', 1 );
				update_post_meta( $lang_post->ID, 'de_admin_email_from', get_option( 'admin_email' ) );
				update_post_meta( $lang_post->ID, 'de_admin_email_to', get_option( 'admin_email' ) );
				update_post_meta( $lang_post->ID, 'de_admin_email_subject', __( 'Job Application' ) );
				update_post_meta( $lang_post->ID, 'de_admin_email_body_html', 1 );
				update_post_meta( $lang_post->ID, 'de_admin_email_body', '
<p>
<table>
<tr><td>' . __( 'job' ) . '</td><td>{job}</td><td></tr>
<tr><td>' . __( 'name' ) . '</td><td>{name}</td><td></tr>
<tr><td>' . __( 'email' ) . '</td><td><a mailto="{email}">{email}</a></td><td></tr>
<tr><td>' . __( 'phone' ) . '</td><td>{phone}</td><td></tr>
<tr><td>' . __( 'comments' ) . '</td><td>{comments}</td><td></tr>
</table>
</p>
				' );
				update_post_meta( $lang_post->ID, 'de_admin_attach_uploads', 1 );
			}
		} else {
			update_post_meta( $webformPostId, 'de_success_page', '/' );
			update_post_meta( $webformPostId, 'de_use_admin_email', 1 );
			update_post_meta( $webformPostId, 'de_admin_email_from', get_option( 'admin_email' ) );
			update_post_meta( $webformPostId, 'de_admin_email_to', get_option( 'admin_email' ) );
			update_post_meta( $webformPostId, 'de_admin_email_subject', __( 'Job Application' ) );
			update_post_meta( $webformPostId, 'de_admin_email_body_html', 1 );
			update_post_meta( $webformPostId, 'de_admin_email_body', '
<p>
<table>
<tr><td>' . __( 'job' ) . '</td><td>{job}</td><td></tr>
<tr><td>' . __( 'name' ) . '</td><td>{name}</td><td></tr>
<tr><td>' . __( 'email' ) . '</td><td><a mailto="{email}">{email}</a></td><td></tr>
<tr><td>' . __( 'phone' ) . '</td><td>{phone}</td><td></tr>
<tr><td>' . __( 'comments' ) . '</td><td>{comments}</td><td></tr>
</table>
</p>
			' );
			update_post_meta( $webformPostId, 'de_admin_attach_uploads', 1 );
		}
		
		update_option( 'de_' . $post_type_slug . '_job_application', $webformPostId );
	}
}

function de_custom_jobs_page_options() {
	global $direct_queried_object;
	
	if ( ! empty( $direct_queried_object->ID ) ) {
		if ( basename( de_get_current_template() ) == 'archive.php' ) {
		?>
			<div style="float:left; width:46%;padding:5px 2%;">
				<h5>Test archive</h5>
				<label>Test</label>
				<input type="text" name="test_archive" value="<?php echo esc_attr( get_post_meta( $direct_queried_object->ID, 'de_jobs_test_archive', true ) ); ?>" />
			</div>
			<div style="clear: both;"></div>
		<?php
		} elseif ( basename( de_get_current_template() ) == 'de_webform_job_application.php' ) {
		?>
			<div style="float:left; width:46%;padding:5px 2%;">
				<h5>Test webform</h5>
				<label>Test</label>
				<input type="text" name="test_webform" value="<?php echo esc_attr( get_post_meta( $direct_queried_object->ID, 'de_jobs_test_webform', true ) ); ?>" />
			</div>
			<div style="clear: both;"></div>
		<?php
		} elseif ( basename( de_get_current_template() ) == 'single.php' ) {
		?>
			<div style="float:left; width:46%;padding:5px 2%;">
				<h5>Test single</h5>
				<label>Test</label>
				<input type="text" name="test_single" value="<?php echo esc_attr( get_post_meta( $direct_queried_object->ID, 'de_jobs_test_single', true ) ); ?>" />
			</div>
			<div style="clear: both;"></div>
		<?php
		}
	}
}

function de_custom_jobs_save_page_options() {
	if ( ! empty( $_POST[ 'direct-page-options' ][ 'postId' ] ) ) {
		$post = get_post( $_POST[ 'direct-page-options' ][ 'postId' ] );
	}
	if ( ! empty( $_POST[ 'direct-page-options' ][ 'templateName' ] ) ) {
		$template = $_POST[ 'direct-page-options' ][ 'templateName' ];
	}

	if ( ! empty( $template ) && ! empty( $post ) && ! empty( $post->ID ) ) {
		if ( basename( $template ) == 'archive.php' ) {
			update_post_meta( $post->ID, 'de_jobs_test_archive', $_POST[ 'direct-page-options' ][ 'test_archive' ] );
		} elseif ( basename( $template ) == 'de_webform_job_application.php' ) {
			update_post_meta( $post->ID, 'de_jobs_test_webform', $_POST[ 'direct-page-options' ][ 'test_webform' ] );
		} else {
			update_post_meta( $post->ID, 'de_jobs_test_single', $_POST[ 'direct-page-options' ][ 'test_single' ] );
		}
	}
}

function de_custom_jobs_form_setup( $post ) {
	global $de_webform_values;

	if ( ! empty( $_REQUEST[ 'item' ] ) && get_post( ( int ) $_REQUEST[ 'item' ] ) ) {
		$p = get_post( ( int ) $_REQUEST[ 'item' ] );

		if ( $post->ID == get_option( $p->post_type . '_job_application' ) ) {
			/*
			 * Form default values
			 */
			$de_webform_values[ 'job' ] = $p->post_title;
			$de_webform_values[ 'name' ] = '';
			$de_webform_values[ 'email' ] = '';
			$de_webform_values[ 'phone' ] = '';
			$de_webform_values[ 'comments' ] = '';
		} else {
			wp_safe_redirect( get_permalink( get_option( 'de_page_for_' . $p->post_type ) ) );
			die();
		}
	}
}

function de_custom_jobs_form_validate( $post ) {
	global $wpdb;
	global $de_webform_errors;
	global $de_webform_values;

	if ( ! empty( $_REQUEST[ 'item' ] ) && get_post( ( int ) $_REQUEST[ 'item' ] ) ) {
		$p = get_post( ( int ) $_REQUEST[ 'item' ] );

		if ( $post->ID == get_option( $p->post_type . '_job_application' ) ) {
			$de_webform_values[ 'job' ] = sanitize_text_field( $_POST[ 'job' ] );
			$de_webform_values[ 'name' ] = sanitize_text_field( $_POST[ 'name' ] );
			$de_webform_values[ 'email' ] = sanitize_text_field( $_POST[ 'email' ] );
			$de_webform_values[ 'phone' ] = sanitize_text_field( $_POST[ 'phone' ] );
			$de_webform_values[ 'comments' ] = sanitize_text_field( $_POST[ 'comments' ] );
			
			if( empty( $de_webform_values[ 'job' ] ) )
				$de_webform_errors[ 'job' ] = __( 'Job field is empty.' );
			if( empty( $de_webform_values[ 'name' ] ) )
				$de_webform_errors[ 'name' ] = __( 'Name field is empty.' );
			if( empty( $de_webform_values[ 'email' ] ) || ! filter_var( $de_webform_values[ 'email' ], FILTER_VALIDATE_EMAIL ) )
				$de_webform_errors[ 'email' ] = __( 'Email field is empty.' );
			if( empty( $de_webform_values[ 'phone' ] ) )
				$de_webform_errors[ 'phone' ] = __( 'Phone field is empty.' );
		}
	}
}
