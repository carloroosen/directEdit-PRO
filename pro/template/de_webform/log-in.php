<?php
/**
 * Form Template: Log in
 */

get_header();

if ( ! empty( $de_webform_errors ) )
	echo '<p>' . implode( '<br />', $de_webform_errors ) . '</p>';
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

		<form method="post">
			<p>
			<label for="email"><em>&nbsp;</em><?php _e( 'Email', 'direct-edit' ); ?></label>
			<br />
			<input id="email" name="email" type="email" class="required email" value="<?php echo esc_attr( $de_webform_values[ 'email' ] ); ?>" />
			</p>
			<p>
			<label for="password"><em>&nbsp;</em><?php _e( 'Password', 'direct-edit' ); ?></label>
			<br />
			<input id="password" name="password" type="password" class="required" />
			</p>
			<p>
			<input id="send" name="send" value="send" type="submit" />
			</p>
		</form>

	</div>
</section>
<?php
}

get_footer();
?>
