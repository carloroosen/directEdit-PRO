<?php
/**
 * Form Template: {title}
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
			<input type="submit" />
			</p>
		</form>

	</div>
</section>
<?php
}

get_footer();
?>
