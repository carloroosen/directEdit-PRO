<?php
/**
 * Form Template: Log in
 */

get_header();

if ( ! empty( $de_webform_errors ) ) {
	// Usually we hide error messages in the login form.
	if ( ! empty( $_REQUEST[ 'action' ] ) ) {
		echo '<p>' . implode( '<br />', $de_webform_errors ) . '</p>';
	}
}
if ( ! empty( $de_webform_messages ) ) {
	echo '<p>' . implode( '', $de_webform_messages ) . '</p>';
} else {
?>
<header>
	<?php direct_editable( 'text', 'wptitle', array( 'postId' => $post->ID, 'container' => 'h1' ) ); ?>
</header>
<section id="content">
	<div id="inner-content-wrapper">
		<?php direct_editable( 'text', 'wpcontent', array( 'postId' => $post->ID, 'container' => 'div' ) ); ?>

		<?php
		if ( ! empty( $_REQUEST[ 'action' ] ) ) {
			if ( $_REQUEST[ 'action' ] == 'password-recovery' ) {
		?>
			<form method="post">
				<?php if ( get_option( 'de_honeypot' ) ) { ?>
				<input class="question" name="question" type="text">
				<?php  } ?>
				<p>
				<label for="email"><em>&nbsp;</em><?php _e( 'Email', 'direct-edit' ); ?></label>
				<input id="email" name="email" type="email" class="required email" value="<?php echo esc_attr( $de_webform_values[ 'email' ] ); ?>" />
				</p>
				<p>
				<input id="send" name="send" value="send" type="submit" />
				</p>
			</form>
		<?php
			} elseif( $_REQUEST[ 'action' ] == 'set-new-password' ) {
		?>
			<form method="post">
				<p>
				<label for="password"><em>&nbsp;</em><?php _e( 'New password', 'direct-edit' ); ?></label>
				<input id="password" name="password" type="password" class="required" />
				</p>
				<p>
				<label for="password_retyped"><em>&nbsp;</em><?php _e( 'Retype new password', 'direct-edit' ); ?></label>
				<input id="password_retyped" name="password_retyped" type="password" class="required" />
				</p>
				<p>
				<span id="password-strength"></span>
				</p>
				<p>
				<input id="send" name="send" value="send" type="submit" disabled="disabled" />
				</p>
			</form>
		<?php
			}
		} else {
		?>
			<form method="post">
				<p>
				<label for="email"><em>&nbsp;</em><?php _e( 'Email', 'direct-edit' ); ?></label>
				<input id="email" name="email" type="email" class="required email" value="<?php echo esc_attr( $de_webform_values[ 'email' ] ); ?>" />
				</p>
				<p>
				<label for="password"><em>&nbsp;</em><?php _e( 'Password', 'direct-edit' ); ?></label>
				<input id="password" name="password" type="password" class="required" />
				</p>
				<p><a href="<?php echo add_query_arg( 'action', 'password-recovery', de_get_login_form_permalink() ); ?>"><?php _e( 'Lost your password?', 'direct-edit' ); ?></a></p>
				<p>
				<input id="send" name="send" value="send" type="submit" />
				</p>
			</form>
		<?php
		}
		?>
	</div>
</section>
<?php
}

get_footer();
?>
