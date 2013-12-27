<?php get_header(); ?>

<header>
	<?php direct_editable( 'text', 'wptitle', array( 'postId' => $post->ID, 'container' => 'h1' ) ); ?>
</header>
<section id="content">
	<div id="inner-content-wrapper">
		<?php direct_editable( 'text', 'wpcontent', array( 'postId' => $post->ID, 'container' => 'div' ) ); ?>
	</div>
</section>

<?php get_footer(); ?>
