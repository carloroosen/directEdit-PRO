<?php get_header(); ?>

<header>
	<?php direct_editable( 'text', 'wptitle', array( 'postId' => $post->ID, 'container' => 'h1' ) ); ?>
</header>
<section id="content">
	<div id="inner-content-wrapper">
		<?php direct_editable( 'text', 'wpcontent', array( 'postId' => $post->ID, 'container' => 'div' ) ); ?>
		<?php direct_editable( 'text', 'postmeta', array( 'options' => 'title', 'postId' => $post->ID, 'key' => 'de_subtitle', 'container' => 'h2' ) ); ?>
		<?php direct_editable( 'text', 'postmeta', array( 'options' => 'rich', 'postId' => $post->ID, 'key' => 'de_text', 'container' => 'div' ) ); ?>
	</div>
</section>

<?php get_footer(); ?>
