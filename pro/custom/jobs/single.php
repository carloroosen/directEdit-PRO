<?php
/**
 * Single Post Template
 *
 * This template is the default page template. It is used to display content when someone is viewing a
 * singular view of a post ('post' post_type).
 * @link http://codex.wordpress.org/Post_Types#Post
 *
 * @package WooFramework
 * @subpackage Template
 */

get_header();
?>
       
    <!-- #content Starts -->
	<?php woo_content_before(); ?>
    <div id="content" class="col-full">
    
    	<div id="main-sidebar-container">    

            <!-- #main Starts -->
            <?php woo_main_before(); ?>
            <section id="main">                       
<?php
	woo_loop_before();
	
	if (have_posts()) { $count = 0;
		while (have_posts()) { the_post(); $count++;
			
			 $page_link_args = apply_filters( 'woothemes_pagelinks_args', array( 'before' => '<div class="page-link">' . __( 'Pages:', 'woothemes' ), 'after' => '</div>' ) );

			 woo_post_before();
			?>
			<article <?php post_class(); ?>>
			<?php
				woo_post_inside_before();
			?>
				<header>
					<?php direct_editable( 'text', 'wptitle', array( 'postId' => $post->ID, 'container' => 'h2', 'attr' => array( 'class' => 'archive_header' ) ) ); ?>
				</header>

				<section class="entry">
					<?php direct_editable( 'text', 'wpcontent', array( 'postId' => $post->ID ) ); ?>
					<?php if ( $post->ID && get_permalink( get_option( $post->post_type . '_job_application' ) ) ) { ?>
					<p>
						<a href="<?php echo get_permalink( get_option( $post->post_type . '_job_application' ) ) . '?item=' . $post->ID; ?>"><?php _e( 'Apply now' ); ?></a>
					</p>
					<?php } ?>
				</section><!-- /.entry -->
				<div class="fix"></div>
			<?php
				woo_post_inside_after();
			?>
			</article><!-- /.post -->
			<?php
				woo_post_after();
				$comm = get_option( 'woo_comments' );
				if ( ( $comm == 'page' || $comm == 'both' ) && is_page() ) { comments_template(); }

		}
	}
	
	woo_loop_after();
?>     
            </section><!-- /#main -->
            <?php woo_main_after(); ?>
    
            <?php get_sidebar(); ?>

		</div><!-- /#main-sidebar-container -->         

		<?php get_sidebar('alt'); ?>

    </div><!-- /#content -->
	<?php woo_content_after(); ?>

<?php get_footer(); ?>
