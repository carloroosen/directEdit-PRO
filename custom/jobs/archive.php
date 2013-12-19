<?php get_header(); ?>

<header>
	<?php direct_editable( 'text', 'wptitle', array( 'postId' => $direct_queried_object->ID, 'container' => 'h1' ) ); ?>
</header>
<section id="content">
	<div id="inner-content-wrapper">
		<?php $count = 0; ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<?php direct_editable( 'link', 'post', array( 'postId' => $post->ID, 'attr' => array( 'id' => 'link-' . $count ) ) ); ?>
				<article class="article-list-item">
					<header>
						<?php direct_editable( 'text', 'wptitle', array( 'postId' => $post->ID, 'container' => 'h1', 'attr' => array( 'id' => 'titleWp-' . $count ) ) ); ?>
					</header>
					<?php direct_editable( 'text', 'wpexcerpt', array( 'options' => 'rich', 'postId' => $post->ID, 'container' => 'div', 'attr' => array( 'id' => 'excerptWp-' . $count ) ) ); ?>
				</article>
			</a>
			<?php $count ++; ?>
		<?php endwhile; ?>
	</div>
</section>

<?php get_footer(); ?>
