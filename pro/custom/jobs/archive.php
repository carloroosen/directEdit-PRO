<?php
/**
 * Archive Template
 *
 * The archive template is a placeholder for archives that don't have a template file. 
 * Ideally, all archives would be handled by a more appropriate template according to the
 * current page context (for example, `tag.php` for a `post_tag` archive).
 *
 * @package WooFramework
 * @subpackage Template
 */

 global $woo_options;
 global $more; $more = 0;
 global $direct_queried_object;
 get_header();
?>      
    <!-- #content Starts -->
	<?php woo_content_before(); ?>
    <div id="content" class="col-full">
    
    	<div id="main-sidebar-container">    
		
            <!-- #main Starts -->
            <?php woo_main_before(); ?>
            <section id="main" class="col-left">
            
				<?php
				woo_loop_before();
				
				direct_editable( 'text', 'wptitle', array( 'postId' => $direct_queried_object->ID, 'container' => 'h1', 'attr' => array( 'class' => 'archive_header' ) ) );
				
				// Display the description for this archive, if it's available.
				direct_editable( 'text', 'wpcontent', array( 'postId' => $direct_queried_object->ID ) );

				if (have_posts()) { $count = 0;
				?>

				<div class="fix"></div>

				<?php
					while (have_posts()) { the_post(); $count++;

						 $page_link_args = apply_filters( 'woothemes_pagelinks_args', array( 'before' => '<div class="page-link">' . __( 'Pages:', 'woothemes' ), 'after' => '</div>' ) );

						 woo_post_before();
						?>
						<article <?php post_class(); ?>>
						<?php
							woo_post_inside_before();
						?>
							<?php direct_editable( 'link', 'post', array( 'postId' => $post->ID, 'attr' => array( 'id' => 'link-' . $count ) ) ); ?>
								<header>
									<?php direct_editable( 'text', 'wptitle', array( 'postId' => $post->ID, 'container' => 'h2', 'attr' => array( 'class' => 'archive_header' ) ) ); ?>
								</header>

								<section class="entry">
									<?php
										direct_editable( 'text', 'wpexcerpt', array( 'postId' => $post->ID ) );
									?>
								</section><!-- /.entry -->
							</a>
							<div class="fix"></div>
						<?php
							woo_post_inside_after();
						?>
						</article><!-- /.post -->
						<?php
							woo_post_after();
							$comm = get_option( 'woo_comments' );
							if ( ( $comm == 'page' || $comm == 'both' ) && is_page() ) { comments_template(); }

					} // End WHILE Loop
				} else {
					get_template_part( 'content', 'noposts' );
				} // End IF Statement

				woo_loop_after();

				woo_pagenav();
				?>
                    
            </section><!-- /#main -->
            <?php woo_main_after(); ?>
    
            <?php get_sidebar(); ?>
    
		</div><!-- /#main-sidebar-container -->         

		<?php get_sidebar( 'alt' ); ?>       

    </div><!-- /#content -->
	<?php woo_content_after(); ?>
		
<?php get_footer(); ?>
