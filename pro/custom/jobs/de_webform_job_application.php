<?php
if ( empty( $_REQUEST[ 'item' ] ) || ! get_post( ( int ) $_REQUEST[ 'item' ] ) ) {
	wp_safe_redirect( home_url() );
	die();
} else {
	$p = get_post( ( int ) $_REQUEST[ 'item' ] );
	
	if ( $post->ID != get_option( $p->post_type . '_job_application' ) ) {
		wp_safe_redirect( get_permalink( get_option( 'de_page_for_' . $p->post_type ) ) );
		die();
	}
}

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
					<?php
					direct_editable( 'text', 'wpcontent', array( 'postId' => $post->ID ) );

					if ( ! empty( $de_webform_errors ) )
						echo '<p>' . implode( '<br />', $de_webform_errors ) . '</p>';
					if ( ! empty( $de_webform_messages ) ) {
						echo '<p>' . implode( '', $de_webform_messages ) . '</p>';
					} else {
					?>
						<section id="form">
							<form method="post" enctype="multipart/form-data">
								<p>
								<label for="job"><em>&nbsp;</em><?php _e( 'Job' ); ?></label>
								<textarea id="job" name="job" class="required" readonly><?php echo esc_attr( $de_webform_values[ 'job' ] ); ?></textarea>
								</p>
								<p>
								<label for="name"><em>*</em><?php _e( 'Name' ); ?></label>
								<input id="name" name="name" type="text" class="required" value="<?php echo esc_attr( $de_webform_values[ 'name' ] ); ?>">
								</p>
								<p>
								<label for="email"><em>*</em><?php _e( 'Email' ); ?></label>
								<input id="email" name="email" type="email" class="required email" value="<?php echo esc_attr( $de_webform_values[ 'email' ] ); ?>">
								</p>
								<p>
								<label for="phone"><em>*</em><?php _e( 'Phone' ); ?></label>
								<input id="phone" name="phone" type="tel" class="required" value="<?php echo esc_attr( $de_webform_values[ 'phone' ] ); ?>">
								</p>
								<p>
								<label for="application_letter"><em>&nbsp;</em><?php _e( 'Application letter' ); ?></label>
								<input id="application_letter" name="application_letter" type="file">
								</p>
								<p>
								<label for="cv"><em>&nbsp;</em>CV</label>
								<input id="cv" name="cv" type="file">
								</p>
								<p>
								<label for="comments"><em>&nbsp;</em><?php _e( 'Comments' ); ?></label>
								<textarea id="comments" name="comments"><?php echo esc_textarea( $de_webform_values[ 'comments' ] ); ?></textarea>
								</p>
								<p><em>*</em><span class="legend"><?php _e( 'Required field' ); ?></span>
								</p>
								<p>
								<input id="submit" name="submit" value="<?php _e( 'Send' ); ?>" type="submit">
								</p>
							</form>
						</section>
					<?php
					}

					wp_link_pages( $page_link_args );
					?>
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
