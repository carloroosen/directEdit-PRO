<?php
add_action( 'add_meta_boxes', 'de_metaboxes_add', 0 );
add_action( 'admin_head', 'de_hide_editor' );
add_action( 'admin_init', 'de_disable_for_subscribers' );
add_action( 'admin_menu', 'de_adjust_menus' );
add_action( 'admin_menu', 'de_plugin_menu' );
add_action( 'do_meta_boxes', 'de_remove_more_metaboxes' );
add_action( 'pre_get_posts', 'de_remove_metaboxes' );
add_action( 'save_post','de_metaboxes_save', 1000, 2 );
add_action( 'user_new_form', 'de_no_user_notification' );

add_filter( 'get_sample_permalink_html', 'de_replace_permalink', 10, 2 );
add_filter( 'page_row_actions', 'de_remove_row_actions', 10, 1 );

function de_metaboxes_add() {
	global $current_user;
	global $post;

	if ( ! ( in_array( 'editor', $current_user->roles ) && get_option( 'de_tweak_backend' ) ) && $post && $post->ID ) {
		if ( get_option( 'de_smart_urls' ) && get_option( 'permalink_structure' ) == '/%postname%/' ) {
			add_meta_box( 'deSlug', __( 'Direct Edit Slug', 'direct-edit' ), 'de_slug_meta_box', $post->post_type, 'normal', 'core' );
		}
		add_meta_box( 'deWpHooks', __( 'Hooks on standard wp-functions ', 'direct-edit' ), 'de_wp_hooks_meta_box', $post->post_type, 'normal', 'core' );
	}
}

function de_slug_meta_box( $post ) {
	$postId = $post->ID;
	
	$de_slug = get_post_meta( $postId, 'de_slug', true );
	
	echo '<fieldset>';
	echo '<label for="de_slug">' . __( 'Slug', 'direct-edit' ) . '</label>';
	echo '<br />';
	echo '<input type="text" id="de_slug" name="de_slug" value="' . $de_slug . '" size="25" />';
	echo '</fieldset>';
}

function de_wp_hooks_meta_box( $post ) {
	$postId = $post->ID;
	
	if ( get_post_meta( $postId, 'de_wp_hooks', true ) ) {
		$de_wp_hooks = unserialize( base64_decode( get_post_meta( $postId, 'de_wp_hooks', true ) ) );
	} else {
		$de_wp_hooks = array();
	}
	
	echo '<fieldset>';
	echo '<label for="de_wp_hooks_title">' . __( 'Title', 'direct-edit' ) . '</label>';
	echo '<br />';
	echo '<select id="de_wp_hooks_title" name="de_wp_hooks[title]">';
	echo '<option value=""' . ( empty( $de_wp_hooks[ 'title' ] ) ? ' selected="selected"' : '' ) . '>Use default settings</option>';
	echo '<option value="-1"' . ( $de_wp_hooks[ 'title' ] == -1 ? ' selected="selected"' : '' ) . '>Disable</option>';
	echo '<option value="1"' . ( $de_wp_hooks[ 'title' ] == 1 ? ' selected="selected"' : '' ) . '>Enable</option>';
	echo '</select>';
	echo '<br />';
	echo '<label for="de_wp_hooks_content">' . __( 'Content', 'direct-edit' ) . '</label>';
	echo '<br />';
	echo '<select id="de_wp_hooks_content" name="de_wp_hooks[content]">';
	echo '<option value=""' . ( empty( $de_wp_hooks[ 'content' ] ) ? ' selected="selected"' : '' ) . '>Use default settings</option>';
	echo '<option value="-1"' . ( $de_wp_hooks[ 'content' ] == -1 ? ' selected="selected"' : '' ) . '>Disable</option>';
	echo '<option value="1"' . ( $de_wp_hooks[ 'content' ] == 1 ? ' selected="selected"' : '' ) . '>Enable</option>';
	echo '</select>';
	echo '<br />';
	echo '<label for="de_wp_hooks_excerpt">' . __( 'Excerpt', 'direct-edit' ) . '</label>';
	echo '<br />';
	echo '<select id="de_wp_hooks_excerpt" name="de_wp_hooks[excerpt]">';
	echo '<option value=""' . ( empty( $de_wp_hooks[ 'excerpt' ] ) ? ' selected="selected"' : '' ) . '>Use default settings</option>';
	echo '<option value="-1"' . ( $de_wp_hooks[ 'excerpt' ] == -1 ? ' selected="selected"' : '' ) . '>Disable</option>';
	echo '<option value="1"' . ( $de_wp_hooks[ 'excerpt' ] == 1 ? ' selected="selected"' : '' ) . '>Enable</option>';
	echo '</select>';
	echo '</fieldset>';
}

function de_hide_editor() {
	global $current_user;
	global $post;

	if ( ( in_array( 'editor', $current_user->roles ) && get_option( 'de_tweak_backend' ) ) && $post && $post->ID ) {
		?>
		<style>
			h2 { display:none; }
			#notice { display:none; }
			#post-body-content { display:none; }
			#misc-publishing-actions { display:none; }
		</style>
		<?php
	}
}

function de_disable_for_subscribers() {
	if ( get_option( 'de_tweak_backend' ) ) {
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_users' ) && ! defined( 'DOING_AJAX' ) ) {
			wp_redirect( site_url() );
			exit;
		}
	}
}

function de_adjust_menus() {
	global $menu;
	global $submenu;
	global $current_user;

	if ( get_option( 'de_tweak_backend' ) ) {
		$restricted = array( __( 'Links' ), __( 'Comments' ) );
		if ( ! in_array( 'administrator', $current_user->roles ) ) {
			$restricted = array( __( 'Media' ), __( 'Dashboard' ), __( 'Posts' ), __( 'Pages' ), __( 'List items' ), __( 'Plugins' ), __( 'Profile' ), __( 'Tools' ), __( 'Settings' ) );
			foreach( get_post_types( array('show_ui' => true ), 'objects' ) as $postType ) {
				if ( in_array( $postType->name, array( 'attachment', 'de_webform', 'de_list_item' ) ) )
					continue;
				
				$restricted[] = __( $postType->labels->singular_name );
			}
		}

		end ($menu);
		while ( prev( $menu ) ){
			$value = explode( ' ', $menu[ key( $menu ) ][ 0 ] );
			if( in_array( $menu[ key( $menu ) ][ 0 ], $restricted ) || in_array( $value[ 0 ] != NULL ? $value[ 0 ] : "", $restricted ) ) {
				unset( $menu[ key( $menu ) ] );
			}
		}

		if ( ! in_array( 'administrator', $current_user->roles ) && ! empty( $submenu[ 'themes.php' ] ) ) {
			$restrictedSub = array( __( 'Themes' ), __( 'Widgets' ) );
			end ( $submenu[ 'themes.php' ] );
			while ( prev( $submenu[ 'themes.php' ] ) ) {
				$value = explode( ' ', $submenu[ 'themes.php' ][ key( $submenu[ 'themes.php' ] ) ][ 0 ] );
				if( in_array( $submenu[ 'themes.php' ][ key( $submenu[ 'themes.php' ] ) ][ 0 ], $restrictedSub ) || in_array( $value[ 0 ] != NULL ? $value[ 0 ] : "", $restrictedSub ) ) {
					unset( $submenu[ 'themes.php' ][ key( $submenu[ 'themes.php' ] ) ] );
				}
			}
			
			// Remove "Editor" submenu
			remove_action('admin_menu', '_add_themes_utility_last', 101);
		}
	}
}

function de_plugin_menu() {
	add_options_page( __( 'Direct Edit Options', 'direct-edit' ), __( 'Direct Edit', 'direct-edit' ), 'manage_options', 'direct-edit', 'de_plugin_page' );
}

function de_plugin_page() {
	global $wpdb;
	global $options;
	global $user_ID;

	// Check permissions
	if ( ! current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'direct-edit' ) );
	}

	// Save options
	if ( isset( $_REQUEST['action'] ) ) {
		if ( get_option( 'de_options_custom_page_types' ) )
			$options = unserialize( base64_decode( get_option( 'de_options_custom_page_types' ) ) );
		else
			$options = array();

		if ( 'automatic_updates' == $_REQUEST['action'] ) {
			check_admin_referer( 'de_nonce_automatic_updates', '_de_nonce' );
			
			update_option( 'automatic_updates_key', $_POST[ 'automatic_updates_key' ] );
			
			add_settings_error( 'direct-edit', 'de-updated', __( 'Settings saved.', 'direct-edit' ), 'updated' );
		} elseif ( 'create_theme' == $_REQUEST['action'] ) {
			check_admin_referer( 'de_nonce_create_theme', '_de_nonce' );
			
			$url = wp_nonce_url( basename( $_SERVER['PHP_SELF'] ) . '?page=direct-edit', 'de_nonce_create_theme', '_de_nonce' );
			if ( false === ( $creds = request_filesystem_credentials( $url, '', false, get_stylesheet_directory(), array( 'action' => 'create_theme', 'theme_name' => sanitize_text_field( $_POST[ 'theme_name' ] ) ) ) ) ) {
				return;
			}
			if ( ! WP_Filesystem( $creds ) ) {
				request_filesystem_credentials( $url, '', true, get_stylesheet_directory(), array( 'action' => 'create_theme', 'theme_name' => sanitize_text_field( $_POST[ 'theme_name' ] ) ) );
				return;
			}

			global $wp_filesystem;
			$plugin_path = str_replace( ABSPATH, $wp_filesystem->abspath(), DIRECT_PATH );
			$theme_name = sanitize_key( $_POST[ 'theme_name' ] );
			$target = trailingslashit( implode( DIRECTORY_SEPARATOR, array( get_theme_root(), $theme_name ) ) );

			// Create theme dir
			if ( ! $wp_filesystem->is_dir( $target ) ) {
				$wp_filesystem->mkdir( $target );

				// Copy theme files
				if ( empty( $_POST[ 'theme_child' ] ) ) {
					// style.css
					$template = $wp_filesystem->get_contents( $plugin_path . 'pro/template/style.css' );
					$template = str_replace( array( '{theme_name}' ), array( sanitize_text_field( $_POST[ 'theme_name' ] ) ), $template );
					$wp_filesystem->put_contents( $target . 'style.css', $template );

					// functions.php
					$template = $wp_filesystem->get_contents( $plugin_path . 'pro/template/functions.php' );
					$wp_filesystem->put_contents( $target . 'functions.php', $template );

					// Header, footer, front-page.php, home.php ( for blog page ), index.php
					$template = $wp_filesystem->get_contents( $plugin_path . 'pro/template/header.php' );
					$wp_filesystem->put_contents( $target . 'header.php', $template );
					$template = $wp_filesystem->get_contents( $plugin_path . 'pro/template/footer.php' );
					$template = str_replace( array( '{year}' ), array( date( 'Y' ) ), $template );
					$wp_filesystem->put_contents( $target . 'footer.php', $template );
					$template = $wp_filesystem->get_contents( $plugin_path . 'pro/template/front-page.php' );
					$wp_filesystem->put_contents( $target . 'front-page.php', $template );
					$template = $wp_filesystem->get_contents( $plugin_path . 'pro/template/home.php' );
					$wp_filesystem->put_contents( $target . 'home.php', $template );
					$template = $wp_filesystem->get_contents( $plugin_path . 'pro/template/page.php' );
					$wp_filesystem->put_contents( $target . 'page.php', $template );
					$template = $wp_filesystem->get_contents( $plugin_path . 'pro/template/index.php' );
					$wp_filesystem->put_contents( $target . 'index.php', $template );

					// 404.php
					$template = $wp_filesystem->get_contents( $plugin_path . 'pro/template/404.php' );
					$wp_filesystem->put_contents( $target . '404.php', $template );
				} else {
					$template = $wp_filesystem->get_contents( $plugin_path . 'pro/template/style-child.css' );
					$template_current = wp_get_theme();
					$template = str_replace( array( '{theme_name}', '{template_name}', '{template_uri}' ), array( sanitize_text_field( $_POST[ 'theme_name' ] ), $template_current->get( 'Template' ), get_template_directory_uri() ), $template );
					$wp_filesystem->put_contents( $target . 'style.css', $template );
				}
				
				// Create auxiliary dirs and copy login form template
				$wp_filesystem->mkdir( $target . 'de_webform' );
				$wp_filesystem->mkdir( $target . 'snippets' );
				if ( get_option( 'de_wp_login_redirect' ) ) {
					$template = $wp_filesystem->get_contents( $plugin_path . 'pro/template/de_webform/log-in.php' );
					$wp_filesystem->put_contents( $target . 'de_webform/log-in.php', $template );
				}
				
				// Create custom page templates
				foreach ( $options as $option ) {
					$template = $wp_filesystem->get_contents( $plugin_path . 'pro/template/archive-custom_post_type.php' );
					$template = str_replace( array( '{name}' ), array( sanitize_title( $option->name ) ), $template );
					$wp_filesystem->put_contents( $target . 'archive-de_' . sanitize_title( $option->name ) . '.php', $template );
					$template = $wp_filesystem->get_contents( $plugin_path . 'pro/template/single-custom_post_type.php' );
					$wp_filesystem->put_contents( $target . 'single-de_' . sanitize_title( $option->name ) . '.php', $template );
				}
				
				// Switch theme
				switch_theme( $theme_name );
			}
			
			add_settings_error( 'direct-edit', 'de-updated', __( 'Settings saved.', 'direct-edit' ), 'updated' );
		} elseif ( 'copy_files' == $_REQUEST['action'] ) {
			check_admin_referer( 'de_nonce_copy_files', '_de_nonce' );
			
			$url = wp_nonce_url( basename( $_SERVER['PHP_SELF'] ) . '?page=direct-edit', 'de_nonce_copy_files', '_de_nonce' );
			if ( false === ( $creds = request_filesystem_credentials( $url, '', false, get_stylesheet_directory(), array( 'action' => 'copy_files' ) ) ) ) {
				return;
			}
			if ( ! WP_Filesystem( $creds ) ) {
				request_filesystem_credentials( $url, '', true, get_stylesheet_directory(), array( 'action' => 'copy_files' ) );
				return;
			}

			global $wp_filesystem;
			$plugin_path = str_replace( ABSPATH, $wp_filesystem->abspath(), DIRECT_PATH );
			$theme_path = trailingslashit( get_stylesheet_directory() );

			if ( ! file_exists( $theme_path . 'direct-edit' ) ) {
				de_copy( $plugin_path . 'theme', $theme_path . 'direct-edit' );
			}
			
			add_settings_error( 'direct-edit', 'de-updated', __( 'Settings saved.', 'direct-edit' ), 'updated' );
		} elseif ( 'remove_files' == $_REQUEST['action'] ) {
			check_admin_referer( 'de_nonce_remove_files', '_de_nonce' );
			
			$url = wp_nonce_url( basename( $_SERVER['PHP_SELF'] ) . '?page=direct-edit', 'de_nonce_remove_files', '_de_nonce' );
			if ( false === ( $creds = request_filesystem_credentials( $url, '', false, get_stylesheet_directory(), array( 'action' => 'remove_files' ) ) ) ) {
				return;
			}
			if ( ! WP_Filesystem( $creds ) ) {
				request_filesystem_credentials( $url, '', true, get_stylesheet_directory(), array( 'action' => 'remove_files' ) );
				return;
			}

			global $wp_filesystem;
			$plugin_path = str_replace( ABSPATH, $wp_filesystem->abspath(), DIRECT_PATH );
			$theme_path = trailingslashit( get_stylesheet_directory() );

			if ( $wp_filesystem->exists( $theme_path . 'direct-edit' ) ) {
				de_rmdir( $theme_path . 'direct-edit' );
			}
			
			add_settings_error( 'direct-edit', 'de-updated', __( 'Settings saved.', 'direct-edit' ), 'updated' );
		} elseif ( 'create_pages' == $_REQUEST['action'] ) {
			check_admin_referer( 'de_nonce_create_pages', '_de_nonce' );
			
			update_option( 'show_on_front', 'page' );
			
			// Create home page
			$newPost = array(
				'post_title' => __( 'Home', 'direct-edit' ),
				'post_content' => '',
				'post_status' => 'publish',
				'post_date' => date('Y-m-d H:i:s'),
				'post_author' => $user_ID,
				'post_type' => 'page',
				'post_category' => array( 0 )
			);

			$newPostId = wp_insert_post( $newPost );
			De_Url::register_url( $newPostId, sanitize_title( __( 'home', 'direct-edit' ) ) );

			if ( De_Language_Wrapper::has_multilanguage() ) {
				De_Language_Wrapper::set_post_language( $newPostId, De_Language_Wrapper::get_default_language() );
				De_Language_Wrapper::create_language_posts( $newPostId );
				
				foreach( De_Language_Wrapper::get_language_posts( $newPostId ) as $lang => $lang_post ) {
					if ( $lang_post->ID == $newPostId )
						continue;
					
					$data = array(
						'ID' => $lang_post->ID,
						'post_title' => __( 'Home', 'direct-edit' ),
						'post_name' => sanitize_title( __( 'home', 'direct-edit' ) )
					);
					wp_update_post( $data );

					De_Url::register_url( $lang_post->ID, sanitize_title( __( 'home', 'direct-edit' ) ) );
				}
			}

			update_option( 'page_on_front', $newPostId );
			
			// Create blog page
			$newPost = array(
				'post_title' => __( 'Blog', 'direct-edit' ),
				'post_content' => '',
				'post_status' => 'publish',
				'post_date' => date('Y-m-d H:i:s'),
				'post_author' => $user_ID,
				'post_type' => 'page',
				'post_category' => array( 0 )
			);

			$newPostId = wp_insert_post( $newPost );
			De_Url::register_url( $newPostId, sanitize_title( __( 'blog', 'direct-edit' ) ) );

			if ( De_Language_Wrapper::has_multilanguage() ) {
				De_Language_Wrapper::set_post_language( $newPostId, De_Language_Wrapper::get_default_language() );
				De_Language_Wrapper::create_language_posts( $newPostId );
				
				foreach( De_Language_Wrapper::get_language_posts( $newPostId ) as $lang => $lang_post ) {
					if ( $lang_post->ID == $newPostId )
						continue;
					
					$data = array(
						'ID' => $lang_post->ID,
						'post_title' => __( 'Blog', 'direct-edit' ),
						'post_name' => sanitize_title( __( 'blog', 'direct-edit' ) )
					);
					wp_update_post( $data );

					De_Url::register_url( $lang_post->ID, sanitize_title( __( 'blog', 'direct-edit' ) ) );
				}
			}
			
			update_option( 'page_for_posts', $newPostId );
			
			add_settings_error( 'direct-edit', 'de-updated', __( 'Settings saved.', 'direct-edit' ), 'updated' );
		} elseif ( 'create_front_page_template' == $_REQUEST['action'] ) {
			check_admin_referer( 'de_nonce_create_front_page_template', '_de_nonce' );
			
			$url = wp_nonce_url( basename( $_SERVER['PHP_SELF'] ) . '?page=direct-edit', 'de_nonce_create_front_page_template', '_de_nonce' );
			if ( false === ( $creds = request_filesystem_credentials( $url, '', false, get_stylesheet_directory(), array( 'action' => 'create_front_page_template', 'template_name' => sanitize_text_field( $_REQUEST[ 'template_name' ] ) ) ) ) ) {
				return;
			}
			if ( ! WP_Filesystem( $creds ) ) {
				request_filesystem_credentials( $url, '', true, get_stylesheet_directory(), array( 'action' => 'create_front_page_template', 'template_name' => sanitize_text_field( $_REQUEST[ 'template_name' ] ) ) );
				return;
			}

			global $wp_filesystem;
			$plugin_path = str_replace( ABSPATH, $wp_filesystem->abspath(), DIRECT_PATH );
			$target = trailingslashit( get_stylesheet_directory() );
			
			if( $wp_filesystem->exists( $plugin_path . 'pro/custom/front-page/' . sanitize_text_field( $_REQUEST[ 'template_name' ] ) ) ) {
				if ( ! $wp_filesystem->exists( $target . 'custom' ) ) {
					$wp_filesystem->mkdir( $target . 'custom' );
				}
				if ( $wp_filesystem->exists( $target . 'custom/front-page' ) ) {
					de_rmdir( $target . 'custom/front-page' );
				}
				$wp_filesystem->mkdir( $target . 'custom/front-page' );
				de_copy( $plugin_path . 'pro/custom/front-page/' . sanitize_text_field( $_REQUEST[ 'template_name' ] ), $target . 'custom/front-page' );
				
				// Setup hook
				if ( $wp_filesystem->exists( $target . 'custom/front-page/functions.php' ) ) {
					include $target . 'custom/front-page/functions.php';
					do_action( 'de_custom_front_page_' . sanitize_text_field( $_REQUEST[ 'template_name' ] ) . '_setup' );
				}
			}

			add_settings_error( 'direct-edit', 'de-updated', __( 'Settings saved.', 'direct-edit' ), 'updated' );
		} elseif ( 'create' == $_REQUEST['action'] ) {
			check_admin_referer( 'de_nonce_create', '_de_nonce' );
			
			$option = new stdClass();
			$option->name = sanitize_text_field( $_POST[ 'custom_page_type' ] );
			if ( empty( $options[ $option->name ] ) ) {
				$options[ $option->name ] = $option;
				
				// Create list page
				$newPost = array(
					'post_title' => __( ucfirst( sanitize_title( $option->name ) ), 'direct-edit' ),
					'post_content' => '',
					'post_status' => 'publish',
					'post_date' => date('Y-m-d H:i:s'),
					'post_author' => $user_ID,
					'post_type' => 'page',
					'post_category' => array( 0 )
				);

				$newPostId = wp_insert_post( $newPost );
				De_Url::register_url( $newPostId, sanitize_key( $option->name ) );
				update_option( 'de_page_for_de_' . sanitize_key( $option->name ), $newPostId );

				if ( De_Language_Wrapper::has_multilanguage() ) {
					De_Language_Wrapper::set_post_language( $newPostId, De_Language_Wrapper::get_default_language() );
					De_Language_Wrapper::create_language_posts( $newPostId );
					
					foreach( De_Language_Wrapper::get_language_posts( $newPostId ) as $lang => $lang_post ) {
						if ( $lang_post->ID == $newPostId )
							continue;
						
						$data = array(
							'ID' => $lang_post->ID,
							'post_title' => __( ucfirst( sanitize_title( $option->name ) ), 'direct-edit' ),
							'post_name' => sanitize_key( $option->name )
						);
						wp_update_post( $data );

						De_Url::register_url( $lang_post->ID, sanitize_key( $option->name ) );
						
						De_Language_Wrapper::de_post_type_add( 'de_' . sanitize_key( $option->name ) );
					}
				}

				// Update rewrite rules
				foreach( $options as $option ) {
					register_post_type( 'de_' . sanitize_key( $option->name ),
						array(
							'labels' => array(
								'name' => __( ucfirst( $option->name ), 'direct-edit' )
							),
							'public' => true,
							'hierarchical' => true,
							'supports' => array( 'title', 'editor', 'page-attributes' ),
							'rewrite' => array( 'slug' => sanitize_key( $option->name ) ),
							'has_archive' => true
						)
					);
				}
				flush_rewrite_rules();

				// Save de options
				update_option( 'de_options_custom_page_types', base64_encode( serialize( $options ) ) );

				$url = wp_nonce_url( basename( $_SERVER['PHP_SELF'] ) . '?page=direct-edit', 'de_nonce_create', '_de_nonce' );
				if ( false === ( $creds = request_filesystem_credentials( $url, '', false, get_stylesheet_directory(), array( 'action' => 'create', 'custom_page_type' => sanitize_text_field( $_POST[ 'custom_page_type' ] ) ) ) ) ) {
					return;
				}
				if ( ! WP_Filesystem( $creds ) ) {
					request_filesystem_credentials( $url, '', true, get_stylesheet_directory(), array( 'action' => 'create', 'custom_page_type' => sanitize_text_field( $_POST[ 'custom_page_type' ] ) ) );
					return;
				}

				global $wp_filesystem;
				$plugin_path = str_replace( ABSPATH, $wp_filesystem->abspath(), DIRECT_PATH );
				$theme_path = trailingslashit( get_stylesheet_directory() );

				if ( ! $wp_filesystem->exists( $theme_path . 'archive-de_' . sanitize_key( $option->name ) . '.php' ) ) {
					$template = $wp_filesystem->get_contents( $plugin_path . 'pro/template/archive-custom_post_type.php' );
					$wp_filesystem->put_contents( $theme_path . 'archive-de_' . sanitize_key( $option->name ) . '.php', $template );
				}
				if ( ! $wp_filesystem->exists( $theme_path . 'single-de_' . sanitize_key( $option->name ) . '.php' ) ) {
					$template = $wp_filesystem->get_contents( $plugin_path . 'pro/template/single-custom_post_type.php' );
					$wp_filesystem->put_contents ( $theme_path . 'single-de_' . sanitize_key( $option->name ) . '.php', $template );
				}
			}

			add_settings_error( 'direct-edit', 'de-updated', __( 'Settings saved.', 'direct-edit' ), 'updated' );
		} elseif ( 'create_template' == $_REQUEST['action'] ) {
			check_admin_referer( 'de_nonce_create_template', '_de_nonce' );
			
			$url = wp_nonce_url( basename( $_SERVER['PHP_SELF'] ) . '?page=direct-edit', 'de_nonce_create_template', '_de_nonce' );
			if ( false === ( $creds = request_filesystem_credentials( $url, '', false, get_stylesheet_directory(), array( 'action' => 'create_template', 'custom_page_type' => sanitize_text_field( $_POST[ 'custom_page_type' ] ), 'template_name' => sanitize_text_field( $_POST[ 'template_name' ] ) ) ) ) ) {
				return;
			}
			if ( ! WP_Filesystem( $creds ) ) {
				request_filesystem_credentials( $url, '', true, get_stylesheet_directory(), array( 'action' => 'create_template', 'custom_page_type' => sanitize_text_field( $_POST[ 'custom_page_type' ] ), 'template_name' => sanitize_text_field( $_POST[ 'template_name' ] ) ) );
				return;
			}

			global $wp_filesystem;
			$plugin_path = str_replace( ABSPATH, $wp_filesystem->abspath(), DIRECT_PATH );
			$target = trailingslashit( get_stylesheet_directory() );

			$option = sanitize_text_field( $_REQUEST[ 'custom_page_type' ] );

			if( ! empty( $options[ $option ] ) && $wp_filesystem->is_dir( $plugin_path . 'pro/custom/' . $option ) ) {
				$option = $options[ $option ];

				$target = get_stylesheet_directory();
				if ( ! $wp_filesystem->exists( $target . 'custom' ) ) {
					$wp_filesystem->mkdir( $target . 'custom' );
				}
				if ( $wp_filesystem->exists( $target . 'custom/' . sanitize_key( $option->name ) ) ) {
					de_rmdir( $target . 'custom/' . sanitize_key( $option->name ) );
				}
				de_copy( $plugin_path . 'pro/custom/' . sanitize_key( $_REQUEST[ 'template_name' ] ), $target . 'custom/' . sanitize_key( $option->name ) );
				
				// Setup hook
				if ( file_exists( $target . 'custom/' . sanitize_key( $option->name ) . '/functions.php' ) ) {
					include $target . 'custom/' . sanitize_key( $option->name ) . '/functions.php';
					do_action( 'de_custom_' . sanitize_key( $_REQUEST[ 'template_name' ] ) . '_setup', sanitize_key( $option->name ) );
				}
			}

			add_settings_error( 'direct-edit', 'de-updated', __( 'Settings saved.', 'direct-edit' ), 'updated' );
		} elseif ( 'delete' == $_REQUEST['action'] ) {
			check_admin_referer( 'de_nonce_delete', '_de_nonce' );

			$url = wp_nonce_url( basename( $_SERVER['PHP_SELF'] ) . '?page=direct-edit', 'de_nonce_delete', '_de_nonce' );
			if ( false === ( $creds = request_filesystem_credentials( $url, '', false, get_stylesheet_directory(), array( 'action' => 'delete', 'custom_page_type' => sanitize_text_field( $_POST[ 'custom_page_type' ] ) ) ) ) ) {
				return;
			}
			if ( ! WP_Filesystem( $creds ) ) {
				request_filesystem_credentials( $url, '', true, get_stylesheet_directory(), array( 'action' => 'delete', 'custom_page_type' => sanitize_text_field( $_POST[ 'custom_page_type' ] ) ) );
				return;
			}

			global $wp_filesystem;
			$plugin_path = str_replace( ABSPATH, $wp_filesystem->abspath(), DIRECT_PATH );
			$target = trailingslashit( get_stylesheet_directory() );

			$option = sanitize_text_field( $_REQUEST[ 'custom_page_type' ] );

			if( ! empty( $options[ $option ] ) ) {
				$option = $options[ $option ];

				$id = get_option( 'de_page_for_de_' . sanitize_key( $option->name ) );

				delete_option( 'de_page_for_de_' . sanitize_key( $option->name ) );
				if ( De_Language_Wrapper::has_multilanguage() && De_Language_Wrapper::get_language_posts( $id ) ) {
					foreach( De_Language_Wrapper::get_language_posts( $id ) as $lang_post ) {
						wp_delete_post( $lang_post->ID, true );
					}
				} else {
					wp_delete_post( $id, true );
				}

				$args = array(
					'numberposts' => -1,
					'post_type' =>'de_' . sanitize_key( $option->name )
				);
				$posts = get_posts( $args );
				if ( is_array( $posts ) ) {
					foreach ( $posts as $post ) {
						wp_delete_post( $post->ID, true );
					}
				}
				
				unset( $options[ $option->name ] );
				update_option( 'de_options_custom_page_types', base64_encode( serialize( $options ) ) );
				
				$target = get_stylesheet_directory();
				if ( $wp_filesystem->exists( $target . 'custom/' . sanitize_key( $option->name ) ) ) {
					de_rmdir( $target . 'custom/' . sanitize_key( $option->name ) );
				}

				if ( De_Language_Wrapper::has_multilanguage() ) {
					De_Language_Wrapper::de_post_type_delete( 'de_' . sanitize_key( $option->name ) );
				}
			}

			add_settings_error( 'direct-edit', 'de-updated', __( 'Settings saved.', 'direct-edit' ), 'updated' );
		} elseif ( 'create_webform' == $_REQUEST['action'] ) {
			check_admin_referer( 'de_nonce_create_webform', '_de_nonce' );
			
			$url = wp_nonce_url( basename( $_SERVER['PHP_SELF'] ) . '?page=direct-edit', 'de_nonce_create_webform', '_de_nonce' );
			if ( false === ( $creds = request_filesystem_credentials( $url, '', false, get_stylesheet_directory(), array( 'action' => 'create_webform', 'custom_webform_name' => sanitize_text_field( $_POST[ 'custom_webform_name' ] ) ) ) ) ) {
				return;
			}
			if ( ! WP_Filesystem( $creds ) ) {
				request_filesystem_credentials( $url, '', true, get_stylesheet_directory(), array( 'action' => 'create_webform', 'custom_webform_name' => sanitize_text_field( $_POST[ 'custom_webform_name' ] ) ) );
				return;
			}

			global $wp_filesystem;
			$plugin_path = str_replace( ABSPATH, $wp_filesystem->abspath(), DIRECT_PATH );
			$target = trailingslashit( get_stylesheet_directory() );
			
			if ( post_type_exists( 'de_webform' ) ) {
				$title = sanitize_text_field( $_POST[ 'custom_webform_name' ] );
				
				$webformPost = array(
					'post_title' => __( ucfirst( sanitize_title( $title ) ), 'direct-edit' ),
					'post_content' => '',
					'post_status' => 'publish',
					'post_date' => date('Y-m-d H:i:s'),
					'post_author' => $user_ID,
					'post_type' => 'de_webform',
					'post_category' => array( 0 )
				);

				$webformPostId = wp_insert_post( $webformPost );
				$slug = De_Url::register_url( $webformPostId, sanitize_key( $title ) );
				
				update_post_meta( $webformPostId, 'de_webform_template', 'de_webform/' . $slug . '.php' );
				update_post_meta( $webformPostId, 'de_admin_email_from', get_option( 'admin_email' ) );
				update_post_meta( $webformPostId, 'de_user_email_from', get_option( 'admin_email' ) );
				
				if ( De_Language_Wrapper::has_multilanguage() ) {
					De_Language_Wrapper::set_post_language( $webformPostId, De_Language_Wrapper::get_default_language() );
					De_Language_Wrapper::create_language_posts( $webformPostId );
					
					update_post_meta( $webformPostId, 'de_success_page', '/' . De_Language_Wrapper::get_default_language() . '/' );
					
					foreach( De_Language_Wrapper::get_language_posts( $webformPostId ) as $lang => $lang_post ) {
						if ( $lang_post->ID == $webformPostId )
							continue;
						
						$data = array(
							'ID' => $lang_post->ID,
							'post_title' => $title,
							'post_name' => sanitize_key( $title )
						);
						wp_update_post( $data );

						De_Url::register_url( $lang_post->ID, sanitize_key( $title ) );

						update_post_meta( $lang_post->ID, 'de_webform_template', 'de_webform/' . $slug . '.php' );
						update_post_meta( $lang_post->ID, 'de_success_page', "/$lang/" );
						update_post_meta( $lang_post->ID, 'de_admin_email_from', get_option( 'admin_email' ) );
						update_post_meta( $lang_post->ID, 'de_user_email_from', get_option( 'admin_email' ) );
					}
				} else {
					update_post_meta( $webformPostId, 'de_success_page', '/' );
				}

				// Create auxiliary dir and copy form template
				if ( ! $wp_filesystem->exists( $target . 'de_webform' ) ) {
					$wp_filesystem->mkdir( $target . 'de_webform' );
				}
				if ( ! $wp_filesystem->exists( $target . 'de_webform/' . $slug . '.php' ) ) {
					$template = file_get_contents( $plugin_path . 'pro/template/single-de_webform.php' );
					$template = str_replace( array( '{title}' ), array( $title ), $template );
					$wp_filesystem->put_contents( $target . 'de_webform/' . $slug . '.php', $template );
				}
			}

			add_settings_error( 'direct-edit', 'de-updated', __( 'Settings saved.', 'direct-edit' ), 'updated' );
		} elseif ( 'create_webform_template' == $_REQUEST['action'] ) {
			check_admin_referer( 'de_nonce_create_webform_template', '_de_nonce' );
			
			$url = wp_nonce_url( basename( $_SERVER['PHP_SELF'] ) . '?page=direct-edit', 'de_nonce_create_webform_template', '_de_nonce' );
			if ( false === ( $creds = request_filesystem_credentials( $url, '', false, get_stylesheet_directory(), array( 'action' => 'create_webform_template', 'webform_id' => sanitize_text_field( $_POST[ 'webform_id' ] ), 'template_name' => sanitize_text_field( $_POST[ 'template_name' ] ) ) ) ) ) {
				return;
			}
			if ( ! WP_Filesystem( $creds ) ) {
				request_filesystem_credentials( $url, '', true, get_stylesheet_directory(), array( 'action' => 'create_webform_template', 'webform_id' => sanitize_text_field( $_POST[ 'webform_id' ] ), 'template_name' => sanitize_text_field( $_POST[ 'template_name' ] ) ) );
				return;
			}

			global $wp_filesystem;
			$plugin_path = str_replace( ABSPATH, $wp_filesystem->abspath(), DIRECT_PATH );
			$target = trailingslashit( get_stylesheet_directory() );

			$webform = get_post( intval( $_REQUEST[ 'webform_id' ] ) );

			if ( ! empty( $webform ) && $webform->post_type == 'de_webform' && $wp_filesystem->is_dir( $plugin_path . 'pro/de_webform/custom/' . sanitize_key( $_REQUEST[ 'template_name' ] ) ) ) {
				if ( ! $wp_filesystem->exists( $target . 'de_webform' ) ) {
					$wp_filesystem->mkdir( $target . 'de_webform' );
				}
				if ( ! $wp_filesystem->exists( $target . 'de_webform/custom' ) ) {
					$wp_filesystem->mkdir( $target . 'de_webform/custom' );
				}
				if ( $wp_filesystem->exists( $target . 'de_webform/custom/' . $webform->post_name ) ) {
					de_rmdir( $target . 'de_webform/custom/' . $webform->post_name );
				}
				de_copy( $plugin_path . 'pro/de_webform/custom/' . sanitize_key( $_REQUEST[ 'template_name' ] ), $target . 'de_webform/custom/' . $webform->post_name );
				
				// Setup hook
				if ( $wp_filesystem->exists( $target . 'de_webform/custom/' . $webform->post_name . '/functions.php' ) ) {
					include $target . 'de_webform/custom/' . $webform->post_name . '/functions.php';
					do_action( 'de_webform_custom_' . sanitize_key( $_REQUEST[ 'template_name' ] ) . '_setup', $webform->post_name );
				}
			}

			add_settings_error( 'direct-edit', 'de-updated', __( 'Settings saved.', 'direct-edit' ), 'updated' );
		} elseif ( 'delete_webform' == $_REQUEST['action'] ) {
			check_admin_referer( 'de_nonce_delete_webform', '_de_nonce' );
			
			$url = wp_nonce_url( basename( $_SERVER['PHP_SELF'] ) . '?page=direct-edit', 'de_nonce_delete_webform', '_de_nonce' );
			if ( false === ( $creds = request_filesystem_credentials( $url, '', false, get_stylesheet_directory(), array( 'action' => 'delete_webform', 'post' => sanitize_text_field( $_POST[ 'post' ] ) ) ) ) ) {
				return;
			}
			if ( ! WP_Filesystem( $creds ) ) {
				request_filesystem_credentials( $url, '', true, get_stylesheet_directory(), array( 'action' => 'delete_webform', 'post' => sanitize_text_field( $_POST[ 'post' ] ) ) );
				return;
			}

			global $wp_filesystem;
			$plugin_path = str_replace( ABSPATH, $wp_filesystem->abspath(), DIRECT_PATH );
			$target = trailingslashit( get_stylesheet_directory() );
			
			$webformPostId = intval( $_REQUEST[ 'post' ] );
			$webform = get_post( $webformPostId );
			
			$target = get_stylesheet_directory();
			if ( $wp_filesystem->exists( $target . 'de_webform/custom/' . $webform->post_name ) ) {
				de_rmdir( $target . 'de_webform/custom/' . $webform->post_name );
			}

			if ( $webformPostId && $webform && $webform->post_type == 'de_webform' ) {
				if ( De_Language_Wrapper::has_multilanguage() && De_Language_Wrapper::get_language_posts( $webformPostId ) ) {
					foreach( De_Language_Wrapper::get_language_posts( $webformPostId ) as $lang_post ) {
						wp_delete_post( $lang_post->ID, true );
					}
				} else {
					wp_delete_post( $webformPostId, true );
				}
			}
			
			add_settings_error( 'direct-edit', 'de-updated', __( 'Settings saved.', 'direct-edit' ), 'updated' );
		} elseif ( 'wp_hooks' == $_REQUEST['action'] ) {
			check_admin_referer( 'de_nonce_wp_hooks', '_de_nonce' );
			
			$options = array_map( 'sanitize_text_field', ( array ) $_REQUEST[ 'wp_hooks' ] );
			update_option( 'de_options_wp_hooks', base64_encode( serialize( $options ) ) );
			
			add_settings_error( 'direct-edit', 'de-updated', __( 'Settings saved.', 'direct-edit' ), 'updated' );
		} elseif ( 'de_options' == $_REQUEST[ 'action' ] ) {
			check_admin_referer( 'de_nonce_de_options', '_de_nonce' );
			
			if ( post_type_exists( 'de_webform' ) ) {
				update_option( 'de_wp_login_redirect', sanitize_text_field( $_REQUEST[ 'wp_login_redirect' ] ) );
				update_option( 'de_global_admin_email', sanitize_text_field( $_REQUEST[ 'global_admin_email' ] ) );
				update_option( 'de_global_admin_email_bcc', sanitize_text_field( $_REQUEST[ 'global_admin_email_bcc' ] ) );
			}
			update_option( 'de_tweak_backend', sanitize_text_field( $_REQUEST[ 'tweak_backend' ] ) );
			update_option( 'de_tweak_frontend', sanitize_text_field( $_REQUEST[ 'tweak_frontend' ] ) );
			update_option( 'de_disable_backend_editor', sanitize_text_field( $_REQUEST[ 'disable_backend_editor' ] ) );
			update_option( 'de_text_validation', sanitize_text_field( $_REQUEST[ 'text_validation' ] ) );
			update_option( 'de_smart_urls', sanitize_text_field( $_REQUEST[ 'smart_urls' ] ) );

			// Handle login form
			if( post_type_exists( 'de_webform' ) && sanitize_text_field( $_REQUEST['wp_login_redirect'] ) ) {
				// Create log in form
				if ( post_type_exists( 'de_webform' ) && ( ! get_option( 'de_login_form' ) || ! get_post( get_option( 'de_login_form' ) ) || get_post_type( get_option( 'de_login_form' ) ) != 'de_webform' ) ) {
					$url = wp_nonce_url( basename( $_SERVER['PHP_SELF'] ) . '?page=direct-edit', 'de_nonce_de_options', '_de_nonce' );
					if ( false === ( $creds = request_filesystem_credentials( $url, '', false, get_stylesheet_directory(), array( 'action' => 'de_options', 'wp_login_redirect' => sanitize_text_field( $_POST[ 'wp_login_redirect' ] ), 'tweak_backend' => sanitize_text_field( $_POST[ 'tweak_backend' ] ), 'tweak_frontend' => sanitize_text_field( $_POST[ 'tweak_frontend' ] ), 'disable_backend_editor' => sanitize_text_field( $_POST[ 'disable_backend_editor' ] ), 'text_validation' => sanitize_text_field( $_POST[ 'text_validation' ] ), 'smart_urls' => sanitize_text_field( $_POST[ 'smart_urls' ] ) ) ) ) ) {
						return;
					}
					if ( ! WP_Filesystem( $creds ) ) {
						request_filesystem_credentials( $url, '', true, get_stylesheet_directory(), array( 'action' => 'de_options', 'wp_login_redirect' => sanitize_text_field( $_POST[ 'wp_login_redirect' ] ), 'tweak_backend' => sanitize_text_field( $_POST[ 'tweak_backend' ] ), 'tweak_frontend' => sanitize_text_field( $_POST[ 'tweak_frontend' ] ), 'disable_backend_editor' => sanitize_text_field( $_POST[ 'disable_backend_editor' ] ), 'text_validation' => sanitize_text_field( $_POST[ 'text_validation' ] ), 'smart_urls' => sanitize_text_field( $_POST[ 'smart_urls' ] ) ) );
						return;
					}

					global $wp_filesystem;
					$plugin_path = str_replace( ABSPATH, $wp_filesystem->abspath(), DIRECT_PATH );
					$target = trailingslashit( get_stylesheet_directory() );

					$args = array(
						'meta_query' => array(
							array(
								'key' => 'de_slug',
								'value' => 'log-in'
							)
						),
						'post_type' => 'de_webform',
						'posts_per_page' => 1
					);
					$p = get_posts( $args );

					if ( count( $p ) ) {
						$loginPost = array_shift( $p );
						$loginPostId = $loginPost->ID;
					} else {
						$loginPost = array(
							'post_title' => __( 'Log in', 'direct-edit' ),
							'post_content' => '',
							'post_status' => 'publish',
							'post_date' => date('Y-m-d H:i:s'),
							'post_author' => $user_ID,
							'post_type' => 'de_webform',
							'post_category' => array( 0 )
						);

						$loginPostId = wp_insert_post( $loginPost );
						De_Url::register_url( $loginPostId, 'log-in' );
						
						update_post_meta( $loginPostId, 'de_webform_template', 'de_webform/log-in.php' );
						
						if ( De_Language_Wrapper::has_multilanguage() ) {
							De_Language_Wrapper::set_post_language( $loginPostId, De_Language_Wrapper::get_default_language() );
							De_Language_Wrapper::create_language_posts( $loginPostId );
							
							update_post_meta( $loginPostId, 'de_success_page', '/' . De_Language_Wrapper::get_default_language() . '/' );
							
							foreach( De_Language_Wrapper::get_language_posts( $loginPostId ) as $lang => $lang_post ) {
								if ( $lang_post->ID == $loginPostId )
									continue;
								
								$data = array(
									'ID' => $lang_post->ID,
									'post_title' => __( 'Log in', 'direct-edit' ),
									'post_name' => sanitize_title( __( 'log in', 'direct-edit' ) )
								);
								wp_update_post( $data );

								De_Url::register_url( $lang_post->ID, sanitize_title( __( 'log in', 'direct-edit' ) ) );

								update_post_meta( $lang_post->ID, 'de_webform_template', 'de_webform/log-in.php' );
								update_post_meta( $lang_post->ID, 'de_success_page', "/$lang/" );
							}
						} else {
							update_post_meta( $loginPostId, 'de_success_page', '/' );
						}
						
						update_post_meta( $loginPostId, 'de_user_email_from', get_option( 'admin_email' ) );
						update_post_meta( $loginPostId, 'de_user_email_to', '{email}' );
						update_post_meta( $loginPostId, 'de_user_email_subject', __( 'Password recovery', 'direct-edit' ) );
						update_post_meta( $loginPostId, 'de_user_email_body_html', 1 );
						update_post_meta( $loginPostId, 'de_user_email_body', __( '<p>Click <a href="{link}">this link</a> to login on the website and to set a new password.</p>', 'direct-edit' ) );
					}
					
					update_option( 'de_login_form', $loginPostId );

					// Create auxiliary dir and copy login form template
					if ( ! $wp_filesystem->exists( $target . 'de_webform' ) ) {
						$wp_filesystem->mkdir( $target . 'de_webform' );
					}
					if ( ! $wp_filesystem->exists( $target . 'de_webform/log-in.php' ) ) {
						$template = $wp_filesystem->get_contents( $plugin_path . 'pro/template/de_webform/log-in.php' );
						$wp_filesystem->put_contents ( $target . 'de_webform/log-in.php', $template );
					}
				}
			} else {
				$loginPostId = get_option( 'de_login_form' );
				
				if ( $loginPostId && get_post( $loginPostId ) && get_post_type( $loginPostId ) == 'de_webform' ) {
					if ( De_Language_Wrapper::has_multilanguage() && De_Language_Wrapper::get_language_posts( $loginPostId ) ) {
						foreach( De_Language_Wrapper::get_language_posts( $loginPostId ) as $lang_post ) {
							wp_delete_post( $lang_post->ID, true );
						}
					} else {
						wp_delete_post( $loginPostId, true );
					}
					
					delete_option( 'de_login_form' );
				}
			}
			
			add_settings_error( 'direct-edit', 'de-updated', __( 'Settings saved.', 'direct-edit' ), 'updated' );
		} elseif ( 'de_seo' == $_REQUEST[ 'action' ] ) {
			check_admin_referer( 'de_nonce_de_seo', '_de_nonce' );

			update_option( 'de_use_seo', sanitize_text_field( $_REQUEST[ 'use_seo' ] ) );

			add_settings_error( 'direct-edit', 'de-updated', __( 'Settings saved.', 'direct-edit' ), 'updated' );
		} elseif ( 'de_security' == $_REQUEST[ 'action' ] ) {
			check_admin_referer( 'de_nonce_de_security', '_de_nonce' );
			
			update_option( 'de_strong_passwords', sanitize_text_field( $_REQUEST[ 'strong_passwords' ] ) );
			update_option( 'de_limit_login_attempts', sanitize_text_field( $_REQUEST[ 'limit_login_attempts' ] ) );
			update_option( 'de_honeypot', sanitize_text_field( $_REQUEST[ 'honeypot' ] ) );
			
			if ( sanitize_text_field( $_REQUEST[ 'limit_login_attempts' ] ) ) {
				$table_name = $wpdb->prefix . 'de_login_attempts';
				$sql = 'CREATE TABLE `' . $table_name . '` (
					`IP` VARCHAR( 20 ) NOT NULL,
					`attempts` INT NOT NULL,
					`last_login` DATETIME NOT NULL
				)';
				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				dbDelta( $sql );
			}

			add_settings_error( 'direct-edit', 'de-updated', __( 'Settings saved.', 'direct-edit' ), 'updated' );
		/* Menu editor is hidden. Probably it will be removed at all in future versions. */
		/*
		} elseif ( 'de_menu_editor' == $_REQUEST[ 'action' ] ) {
			check_admin_referer( 'de_nonce_de_menu_editor', '_de_nonce' );
			
			$url = wp_nonce_url( basename( $_SERVER['PHP_SELF'] ) . '?page=direct-edit', 'de_nonce_de_menu_editor', '_de_nonce' );
			if ( false === ( $creds = request_filesystem_credentials( $url, '', false, get_stylesheet_directory(), array( 'action' => 'de_menu_editor', 'menu_editor_enabled' => sanitize_text_field( $_POST[ 'menu_editor_enabled' ] ) ) ) ) ) {
				return;
			}
			if ( ! WP_Filesystem( $creds ) ) {
				request_filesystem_credentials( $url, '', true, get_stylesheet_directory(), array( 'action' => 'de_menu_editor', 'menu_editor_enabled' => sanitize_text_field( $_POST[ 'menu_editor_enabled' ] ) ) );
				return;
			}

			global $wp_filesystem;
			$plugin_path = str_replace( ABSPATH, $wp_filesystem->abspath(), DIRECT_PATH );
			$target = trailingslashit( get_stylesheet_directory() );
			
			update_option( 'de_menu_editor_enabled', sanitize_text_field( $_REQUEST[ 'menu_editor_enabled' ] ) );
			if ( get_option( 'de_menu_editor_enabled' ) ) {
				foreach ( $_POST as $key => $value ) {
					if ( $key == 'action' || $key == 'menu_editor_enabled' ) {
						continue;
					}
					
					update_option( 'de_' . $key, $value );
				}
				
				// Check menu edit page template
				$target = get_stylesheet_directory();

				if ( ! $wp_filesystem->exists( $target . 'edit-menu.php' ) ) {
					$template = $wp_filesystem->get_contents( $plugin_path . 'pro/template/edit-menu.php' );
					$wp_filesystem->put_contents ( $target . 'edit-menu.php', $template );
				}
			}

			add_settings_error( 'direct-edit', 'de-updated', __( 'Settings saved.', 'direct-edit' ), 'updated' );
		*/
		} elseif ( 'languages' == $_REQUEST['action'] ) {
			check_admin_referer( 'de_nonce_languages', '_de_nonce' );
			
			if ( De_Language_Wrapper::has_multilanguage() ) {
				update_option( 'de_options_show_languages', serialize( array_map( 'sanitize_text_field', ( array ) $_POST[ 'show_languages' ] ) ) );
			}
			
			add_settings_error( 'direct-edit', 'de-updated', __( 'Settings saved.', 'direct-edit' ), 'updated' );
		}
	}

	settings_errors();

	if ( get_option( 'de_options_custom_page_types' ) )
		$options = unserialize( base64_decode( get_option( 'de_options_custom_page_types' ) ) );
	else
		$options = array();
	
	if ( post_type_exists( 'de_webform' ) ) {
		$args = array( 'post_type' => 'de_webform', 'orderby' => 'title', 'posts_per_page' => -1 );
		if ( get_option( 'de_wp_login_redirect' ) ) {
			$args[ 'exclude' ] = array( get_option( 'de_login_form' ) );
		}
		$webforms = get_posts( $args );
	}
	
	if ( get_option( 'de_options_wp_hooks' ) )
		$options_wp_hooks = unserialize( base64_decode( get_option( 'de_options_wp_hooks' ) ) );
	else
		$options_wp_hooks = array( 'title' => 1, 'content' => 1, 'excerpt' => 1 );

	if ( De_Language_Wrapper::has_multilanguage() ) {
		if ( is_array( unserialize( get_option( 'de_options_show_languages' ) ) ) )
			$show_languages = unserialize( get_option( 'de_options_show_languages' ) );
		else
			$show_languages = array();
	}
	?>
	<div class="wrap">
		<h2><?php _e( 'Direct Edit Options', 'direct-edit' ); ?></h2>
		<h3><i><?php _e( 'automatic updates', 'direct-edit' ); ?></i></h3>
		<div class="inside">
			<form method="post">
				<?php wp_nonce_field( 'de_nonce_automatic_updates', '_de_nonce' ); ?>
				<input type="hidden" name="action" value="automatic_updates" />
				<table border="0">
					<tbody>
						<tr>
							<td style="width: 30px;"><?php _e( 'key', 'direct-edit' ); ?></td>
							<td><input type="text" name="automatic_updates_key" id="automatic_updates_key" value="<?php echo get_option( 'automatic_updates_key' ); ?>" style="width: 240px;" /> <input type="submit" value="save" /></td>
						</tr>
					</tbody>
				</table>
			</form>
		</div>
		<h3><i><?php _e( 'setup wizard', 'direct-edit' ); ?></i></h3>
		<h3><?php _e( 'create theme', 'direct-edit' ); ?></h3>
		<div class="inside">
			<form method="post">
				<?php wp_nonce_field( 'de_nonce_create_theme', '_de_nonce' ); ?>
				<input type="hidden" name="action" value="create_theme" />
				<table border="0">
					<tbody>
						<tr>
							<td style="width: 30px;"><?php _e( 'name', 'direct-edit' ); ?></td>
							<td><input type="text" name="theme_name" id="theme_name" style="width: 240px;" /> <input type="submit" value="create" /></td>
						</tr>
						<tr>
							<td colspan="2"><input type="checkbox" name="theme_child" id="theme_child" /> <?php _e( 'create a child theme for current theme', 'direct-edit' ); ?></td>
						</tr>
					</tbody>
				</table>
			</form>
		</div>
		<?php if ( ! file_exists( get_stylesheet_directory() . '/direct-edit' ) ) { ?>
		<h3><?php _e( 'copy files to current theme', 'direct-edit' ); ?></h3>
		<div class="inside">
			<form method="post">
				<?php wp_nonce_field( 'de_nonce_copy_files', '_de_nonce' ); ?>
				<input type="hidden" name="action" value="copy_files" />
				<table border="0">
					<tbody>
						<tr>
							<td><input type="submit" value="copy" /></td>
						</tr>
					</tbody>
				</table>
			</form>
		</div>
		<?php } else { ?>
		<h3><?php _e( 'remove /direct-edit folder from theme', 'direct-edit' ); ?></h3>
		<div class="inside">
			<form method="post">
				<?php wp_nonce_field( 'de_nonce_remove_files', '_de_nonce' ); ?>
				<input type="hidden" name="action" value="remove_files" />
				<table border="0">
					<tbody>
						<tr>
							<td><input type="submit" value="remove" onclick="return confirm( 'Do you really want to remove /direct-edit folder from theme?' );" /></td>
						</tr>
					</tbody>
				</table>
			</form>
		</div>
		<?php } ?>
		<?php
		if ( get_option( 'show_on_front' ) != 'page' ) {
		?>
		<h3><?php _e( 'create home and blog pages', 'direct-edit' ); ?></h3>
		<div class="inside">
			<form method="post">
				<?php wp_nonce_field( 'de_nonce_create_pages', '_de_nonce' ); ?>
				<input type="hidden" name="action" value="create_pages" />
				<table border="0">
					<tbody>
						<tr>
							<td><input type="submit" value="create" /></td>
						</tr>
					</tbody>
				</table>
			</form>
		</div>
		<?php
		} else {
			if ( is_dir( DIRECT_PATH . 'pro/custom/front-page' ) ) {
				$source = DIRECT_PATH . 'pro/custom/front-page';
				$d = dir( $source );
				
				$c = 0;
				while ( FALSE !== ( $entry = $d->read() ) ) {
					if ( $entry == '.' || $entry == '..' )
						continue;

					if ( is_dir( "$source/$entry" ) ) {
						$c ++;
					}
				}
				
				if ( $c ) {
				?>
				<h3><?php _e( 'custom home page templates', 'direct-edit' ); ?></h3>
				<div class="inside">
					<table border="0">
						<tbody>
							<tr>
								<td>
									<?php $d->rewind(); ?>
									<form method="post">
										<?php wp_nonce_field( 'de_nonce_create_front_page_template', '_de_nonce' ); ?>
										<input type="hidden" name="action" value="create_front_page_template" />
										<select name="template_name">
											<?php
												while ( FALSE !== ( $entry = $d->read() ) ) {
													if ( $entry == '.' || $entry == '..' )
														continue;

													if ( is_dir( "$source/$entry" ) ) {
														?>
														<option value="<?php echo $entry; ?>"><?php echo $entry; ?></option>
														<?php
													}
												}
											?>
										</select>
										<input type="submit" value="create" />
									</form>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<?php
				}
			}
		}
		?>
		<h3><?php _e( 'custom page types', 'direct-edit' ); ?></h3>
		<div class="inside">
			<table border="0">
				<tbody>
					<tr>
						<td style="width: 30px;"><?php _e( 'name', 'direct-edit' ); ?></td>
						<td><form method="post"><?php wp_nonce_field( 'de_nonce_create', '_de_nonce' ); ?><input type="hidden" name="action" value="create" /><input type="text" name="custom_page_type" id="custom_page_type" style="width: 240px;" /> <input type="submit" value="create" /></form></td>
					</tr>
					<tr>
						<td></td>
						<td>
							<table border="0">
								<tbody>
									<?php foreach ( $options as $option ) { ?>
									<tr>
										<td><?php echo $option->name; ?></td>
										<td>
											<?php
											if ( is_dir( DIRECT_PATH . 'pro/custom' ) ) {
												$source = DIRECT_PATH . 'pro/custom';
												$d = dir( $source );
												
												$c = 0;
												while ( FALSE !== ( $entry = $d->read() ) ) {
													if ( $entry == '.' || $entry == '..' || $entry == 'front-page' )
														continue;

													if ( is_dir( "$source/$entry" ) ) {
														$c ++;
													}
												}
												
												if ( $c ) {
													$d->rewind();
													?>
													<form method="post">
														<?php wp_nonce_field( 'de_nonce_create_template', '_de_nonce' ); ?>
														<input type="hidden" name="action" value="create_template" />
														<input type="hidden" name="custom_page_type" value="<?php echo $option->name; ?>" />
														<select name="template_name">
															<?php
																while ( FALSE !== ( $entry = $d->read() ) ) {
																	if ( $entry == '.' || $entry == '..' || $entry == 'front-page' )
																		continue;

																	if ( is_dir( "$source/$entry" ) ) {
																		?>
																		<option value="<?php echo $entry; ?>"><?php echo $entry; ?></option>
																		<?php
																	}
																}
															?>
														</select>
														<input type="submit" value="create" />
													</form>
													<?php
												}
											}
											?>
										</td>
										<td>
											<input type="button" onclick="location.href='<?php echo wp_nonce_url( basename( $_SERVER['PHP_SELF'] ) . '?page=direct-edit&action=delete&custom_page_type=' . urlencode( $option->name ), 'de_nonce_delete', '_de_nonce' ); ?>'" value="<?php _e( 'remove', 'direct-edit' ); ?>" />
										</td>
									</tr>
									<?php } ?>
								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php if ( post_type_exists( 'de_webform' ) ) { ?>
		<h3><?php _e( 'custom webforms', 'direct-edit' ); ?></h3>
		<div class="inside">
			<table border="0">
				<tbody>
					<tr>
						<td style="width: 30px;"><?php _e( 'name', 'direct-edit' ); ?></td>
						<td><form method="post"><?php wp_nonce_field( 'de_nonce_create_webform', '_de_nonce' ); ?><input type="hidden" name="action" value="create_webform" /><input type="text" name="custom_webform_name" id="custom_webform_name" style="width: 240px;" /> <input type="submit" value="create" /></form></td>
					</tr>
					<tr>
						<td></td>
						<td>
							<table border="0">
								<tbody>
									<?php foreach ( $webforms as $webform ) { ?>
									<tr>
										<td><?php echo $webform->post_title; ?></td>
										<td>
											<?php
											if ( is_dir( DIRECT_PATH . 'pro/de_webform/custom' ) ) {
												$source = DIRECT_PATH . 'pro/de_webform/custom';
												$d = dir( $source );
												
												$c = 0;
												while ( FALSE !== ( $entry = $d->read() ) ) {
													if ( $entry == '.' || $entry == '..' )
														continue;

													if ( is_dir( "$source/$entry" ) ) {
														$c ++;
													}
												}
												
												if ( $c ) {
													$d->rewind();
													?>
													<form method="post">
														<?php wp_nonce_field( 'de_nonce_create_webform_template', '_de_nonce' ); ?>
														<input type="hidden" name="action" value="create_webform_template" />
														<input type="hidden" name="webform_id" value="<?php echo $webform->ID; ?>" />
														<select name="template_name">
															<?php
																while ( FALSE !== ( $entry = $d->read() ) ) {
																	if ( $entry == '.' || $entry == '..' )
																		continue;

																	if ( is_dir( "$source/$entry" ) ) {
																		?>
																		<option value="<?php echo $entry; ?>"><?php echo $entry; ?></option>
																		<?php
																	}
																}
															?>
														</select>
														<input type="submit" value="create" />
													</form>
													<?php
												}
											}
											?>
										</td>
										<td>
											<input type="button" onclick="location.href='<?php echo wp_nonce_url( basename( $_SERVER['PHP_SELF'] ) . '?page=direct-edit&action=delete_webform&post=' . $webform->ID, 'de_nonce_delete_webform', '_de_nonce' ); ?>'" value="<?php _e( 'remove', 'direct-edit' ); ?>" />
										</td>
									</tr>
									<?php } ?>
								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php } ?>
		<h3><i><?php _e( 'hooks on standard wp-functions', 'direct-edit' ); ?></i></h3>
		<div class="inside">
			<form method="post">
				<?php wp_nonce_field( 'de_nonce_wp_hooks', '_de_nonce' ); ?>
				<input type="hidden" name="action" value="wp_hooks" />
				<table border="0">
					<tbody>
						<tr>
							<td><input type="hidden" name="wp_hooks[title]" value="" /><label><input type="checkbox" name="wp_hooks[title]" value="1"<?php echo ( ! empty( $options_wp_hooks[ 'title' ] ) ? ' checked="checked"' : '' ); ?> /> <?php _e( 'Title', 'direct-edit' ); ?></label></td>
						</tr>
						<tr>
							<td><input type="hidden" name="wp_hooks[content]" value="" /><label><input type="checkbox" name="wp_hooks[content]" value="1"<?php echo ( ! empty( $options_wp_hooks[ 'content' ] ) ? ' checked="checked"' : '' ); ?> /> <?php _e( 'Content', 'direct-edit' ); ?></label></td>
						</tr>
						<tr>
							<td><input type="hidden" name="wp_hooks[excerpt]" value="" /><label><input type="checkbox" name="wp_hooks[excerpt]" value="1"<?php echo ( ! empty( $options_wp_hooks[ 'excerpt' ] ) ? ' checked="checked"' : '' ); ?> /> <?php _e( 'Excerpt', 'direct-edit' ); ?></label></td>
						</tr>
						<tr>
							<td><input type="submit" value="save" /></td>
						</tr>
					</tbody>
				</table>
			</form>
		</div>
		<h3><i><?php _e( 'options', 'direct-edit' ); ?></i></h3>
		<div class="inside">
			<form method="post">
				<?php wp_nonce_field( 'de_nonce_de_options', '_de_nonce' ); ?>
				<input type="hidden" name="action" value="de_options" />
				<table border="0">
					<tbody>
						<?php if ( post_type_exists( 'de_webform' ) ) { ?>
						<tr>
							<td colspan="2"><input type="hidden" name="wp_login_redirect" value="" /><label><input type="checkbox" name="wp_login_redirect" value="1"<?php echo ( get_option( 'de_wp_login_redirect' ) ? ' checked="checked"' : '' ); ?> /> <?php _e( 'wp-login form redirect', 'direct-edit' ); ?></label></td>
						</tr>
						<?php } ?>
						<tr>
							<td colspan="2"><input type="hidden" name="tweak_backend" value="" /><label><input type="checkbox" name="tweak_backend" value="1"<?php echo ( get_option( 'de_tweak_backend' ) ? ' checked="checked"' : '' ); ?> /> <?php _e( 'tweak backend', 'direct-edit' ); ?></label></td>
						</tr>
						<tr>
							<td colspan="2"><input type="hidden" name="tweak_frontend" value="" /><label><input type="checkbox" name="tweak_frontend" value="1"<?php echo ( get_option( 'de_tweak_frontend' ) ? ' checked="checked"' : '' ); ?> /> <?php _e( 'tweak frontend WordPress toolbar', 'direct-edit' ); ?></label></td>
						</tr>
						<tr>
							<td colspan="2"><input type="hidden" name="disable_backend_editor" value="" /><label><input type="checkbox" name="disable_backend_editor" value="1"<?php echo ( get_option( 'de_disable_backend_editor' ) ? ' checked="checked"' : '' ); ?> /> <?php _e( 'disable backend editing for editor', 'direct-edit' ); ?></label></td>
						</tr>
						<tr>
							<td colspan="2"><input type="hidden" name="text_validation" value="" /><label><input type="checkbox" name="text_validation" value="1"<?php echo ( get_option( 'de_text_validation' ) ? ' checked="checked"' : '' ); ?> /> <?php _e( 'validate text', 'direct-edit' ); ?></label></td>
						</tr>
						<tr>
							<td colspan="2">
								<input type="hidden" name="smart_urls" value="" /><label><input type="checkbox" name="smart_urls" value="1"<?php echo ( get_option( 'de_smart_urls' ) ? ' checked="checked"' : '' ); ?><?php echo ( get_option( 'permalink_structure' ) != '/%postname%/' ? ' disabled="disabled"' : '' ); ?> /> <?php _e( 'use DirectEdit smart url\'s', 'direct-edit' ); ?></label>
								<?php if ( get_option( 'permalink_structure' ) != '/%postname%/' ) { ?>
								<br />
								Set <a href="<?php echo admin_url( '/options-permalink.php' ); ?>"><i>Permalink Settings</i></a> to <i>Post name</i> to use this option
								<?php } ?>
							</td>
						</tr>
						<?php if ( post_type_exists( 'de_webform' ) ) { ?>
						<tr>
							<td><?php _e( 'global admin email for custom webforms', 'direct-edit' ); ?></td>
							<td><input type="text" name="global_admin_email" value="<?php echo esc_attr( get_option( 'de_global_admin_email' ) ? get_option( 'de_global_admin_email' ) : get_option( 'admin_email' ) ); ?>" /></td>
						</tr>
						<tr>
							<td><?php _e( 'bcc for custom webforms', 'direct-edit' ); ?></td>
							<td><input type="text" name="global_admin_email_bcc" value="<?php echo esc_attr( get_option( 'de_global_admin_email_bcc' ) ? get_option( 'de_global_admin_email_bcc' ) : '' ); ?>" /></td>
						</tr>
						<?php } ?>
						<tr>
							<td><input type="submit" value="save" /></td>
						</tr>
					</tbody>
				</table>
			</form>
		</div>
		<h3><i><?php _e( 'SEO', 'direct-edit' ); ?></i></h3>
		<div class="inside">
			<form method="post">
				<?php wp_nonce_field( 'de_nonce_de_seo', '_de_nonce' ); ?>
				<input type="hidden" name="action" value="de_seo" />
				<table border="0">
					<tbody>
						<tr>
							<td><label><input type="radio" name="use_seo" value=""<?php echo ( get_option( 'de_use_seo' ) == '' ? ' checked="checked"' : '' ); ?> /> <?php _e( 'Use DE SEO', 'direct-edit' ); ?></label></td>
						</tr>
						<?php if ( is_plugin_active( 'all-in-one-seo-pack/all_in_one_seo_pack.php' ) ) { ?>
						<tr>
							<td><label><input type="radio" name="use_seo" value="all-in-one-seo-pack"<?php echo ( get_option( 'de_use_seo' ) == 'all-in-one-seo-pack' ? ' checked="checked"' : '' ); ?> /> <?php _e( 'Use All In One SEO Pack', 'direct-edit' ); ?></label></td>
						</tr>
						<?php } ?>
						<?php if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) { ?>
						<tr>
							<td><label><input type="radio" name="use_seo" value="wordpress-seo"<?php echo ( get_option( 'de_use_seo' ) == 'wordpress-seo' ? ' checked="checked"' : '' ); ?> /> <?php _e( 'Use WordPress SEO', 'direct-edit' ); ?></label></td>
						</tr>
						<?php } ?>
						<tr>
							<td><label><input type="radio" name="use_seo" value="none"<?php echo ( get_option( 'de_use_seo' ) == 'none' ? ' checked="checked"' : '' ); ?> /> <?php _e( 'Don\'t use any SEO', 'direct-edit' ); ?></label></td>
						</tr>
						<tr>
							<td><input type="submit" value="save" /></td>
						</tr>
					</tbody>
				</table>
			</form>
		</div>
		<?php if ( function_exists( 'de_security_use_honeypot' ) ) { ?>
		<h3><i><?php _e( 'security', 'direct-edit' ); ?></i></h3>
		<div class="inside">
			<form method="post">
				<?php wp_nonce_field( 'de_nonce_de_security', '_de_nonce' ); ?>
				<input type="hidden" name="action" value="de_security" />
				<table border="0">
					<tbody>
						<?php if ( post_type_exists( 'de_webform' ) ) { ?>
						<tr>
							<td><input type="hidden" name="strong_passwords" value="" /><label><input type="checkbox" name="strong_passwords" value="1"<?php echo ( get_option( 'de_strong_passwords' ) ? ' checked="checked"' : '' ); ?> /> <?php _e( 'enforce strong passwords', 'direct-edit' ); ?></label></td>
						</tr>
						<tr>
							<td><input type="hidden" name="limit_login_attempts" value="" /><label><input type="checkbox" name="limit_login_attempts" value="1"<?php echo ( get_option( 'de_limit_login_attempts' ) ? ' checked="checked"' : '' ); ?> /> <?php _e( 'limit login attempts, then block user or IP', 'direct-edit' ); ?></label></td>
						</tr>
						<tr>
							<td><input type="hidden" name="honeypot" value="" /><label><input type="checkbox" name="honeypot" value="1"<?php echo ( get_option( 'de_honeypot' ) ? ' checked="checked"' : '' ); ?> /> <?php _e( 'use honeypot captcha for DirectEdit webforms', 'direct-edit' ); ?></label></td>
						</tr>
						<?php } ?>
						<tr>
							<td><input type="submit" value="save" /></td>
						</tr>
					</tbody>
				</table>
			</form>
		</div>
		<?php } ?>
		<?php /* Menu editor is hidden. Probably it will be removed at all in future versions. */ ?> 
		<?php /* ?>
		<h3><?php _e( 'DirectEdit menu editor', 'direct-edit' ); ?></h3>
		<div class="inside">
			<form method="post">
				<?php wp_nonce_field( 'de_nonce_de_menu_editor', '_de_nonce' ); ?>
				<input type="hidden" name="action" value="de_menu_editor" />
				<table border="0">
					<tbody>
						<tr>
							<td><input type="hidden" name="menu_editor_enabled" value="" /><label><input type="checkbox" name="menu_editor_enabled" value="1"<?php echo ( get_option( 'de_menu_editor_enabled' ) ? ' checked="checked"' : '' ); ?> onchange="if (jQuery(this).attr('checked')) { jQuery('.menu_editor_enabled').show(); } else { jQuery('.menu_editor_enabled').hide(); }" /> <?php _e( 'use DirectEdit menu editor', 'direct-edit' ); ?></label></td>
						</tr>
						<tr class="menu_editor_enabled"<?php echo ( get_option( 'de_menu_editor_enabled' ) ? '' : ' style="display: none;"' ); ?>>
							<td><h3><i><?php _e( 'which menu items can be added', 'direct-edit' ); ?></i></h3></td>
						</tr>
						<tr class="menu_editor_enabled"<?php echo ( get_option( 'de_menu_editor_enabled' ) ? '' : ' style="display: none;"' ); ?>>
							<td><input type="hidden" name="menu_editor_pages" value="" /><label><input type="checkbox" name="menu_editor_pages" value="1"<?php echo ( get_option( 'de_menu_editor_pages' ) ? ' checked="checked"' : '' ); ?> /> <?php _e( 'Pages', 'direct-edit' ); ?></label></td>
						</tr>
						<tr class="menu_editor_enabled"<?php echo ( get_option( 'de_menu_editor_enabled' ) ? '' : ' style="display: none;"' ); ?>>
							<td><input type="hidden" name="menu_editor_de_archive_pages" value="" /><label><input type="checkbox" name="menu_editor_de_archive_pages" value="1"<?php echo ( get_option( 'de_menu_editor_de_archive_pages' ) ? ' checked="checked"' : '' ); ?> /> <?php _e( 'DirectEdit archive pages', 'direct-edit' ); ?></label></td>
						</tr>
						<tr class="menu_editor_enabled"<?php echo ( get_option( 'de_menu_editor_enabled' ) ? '' : ' style="display: none;"' ); ?>>
							<td><input type="hidden" name="menu_editor_de_webforms" value="" /><label><input type="checkbox" name="menu_editor_de_webforms" value="1"<?php echo ( get_option( 'de_menu_editor_de_webforms' ) ? ' checked="checked"' : '' ); ?> /> <?php _e( 'Webforms', 'direct-edit' ); ?></label></td>
						</tr>
						<tr class="menu_editor_enabled"<?php echo ( get_option( 'de_menu_editor_enabled' ) ? '' : ' style="display: none;"' ); ?>>
							<td><input type="hidden" name="menu_editor_categories" value="" /><label><input type="checkbox" name="menu_editor_categories" value="1"<?php echo ( get_option( 'de_menu_editor_categories' ) ? ' checked="checked"' : '' ); ?> /> <?php _e( 'Categories', 'direct-edit' ); ?></label></td>
						</tr>
						<?php foreach( get_taxonomies( array( '_builtin' => false ), 'object' ) as $value ) { ?>
						<tr class="menu_editor_enabled"<?php echo ( get_option( 'de_menu_editor_enabled' ) ? '' : ' style="display: none;"' ); ?>>
							<td><input type="hidden" name="menu_editor_taxonomies_<?php echo $value->name; ?>" value="" /><label><input type="checkbox" name="menu_editor_taxonomies_<?php echo $value->name; ?>" value="1"<?php echo ( get_option( 'de_menu_editor_taxonomies_' . $value->name ) ? ' checked="checked"' : '' ); ?> /> <?php _e( $value->label, 'direct-edit' ); ?></label></td>
						</tr>
						<?php } ?>
						<tr class="menu_editor_enabled"<?php echo ( get_option( 'de_menu_editor_enabled' ) ? '' : ' style="display: none;"' ); ?>>
							<td><input type="hidden" name="menu_editor_posts" value="" /><label><input type="checkbox" name="menu_editor_posts" value="1"<?php echo ( get_option( 'de_menu_editor_posts' ) ? ' checked="checked"' : '' ); ?> /> <?php _e( 'Posts', 'direct-edit' ); ?></label></td>
						</tr>
						<?php foreach( get_post_types( array( '_builtin' => false ), 'object' ) as $value ) { ?>
						<?php if ( $value->name == 'de_list_item' || $value->name == 'de_webform' ) { continue; } ?>
						<tr class="menu_editor_enabled"<?php echo ( get_option( 'de_menu_editor_enabled' ) ? '' : ' style="display: none;"' ); ?>>
							<td><input type="hidden" name="menu_editor_posts_<?php echo $value->name; ?>" value="" /><label><input type="checkbox" name="menu_editor_posts_<?php echo $value->name; ?>" value="1"<?php echo ( get_option( 'de_menu_editor_posts_' . $value->name ) ? ' checked="checked"' : '' ); ?> /> <?php _e( $value->label, 'direct-edit' ); ?></label></td>
						</tr>
						<?php } ?>
						<tr>
							<td><input type="submit" value="save" /></td>
						</tr>
					</tbody>
				</table>
			</form>
		</div>
		<?php */ ?> 
		<?php if ( De_Language_Wrapper::has_multilanguage() ) { ?>
		<h3><i><?php _e( 'Show languages', 'direct-edit' ); ?></i></h3>
		<form method="post">
			<?php wp_nonce_field( 'de_nonce_languages', '_de_nonce' ); ?>
			<input type="hidden" name="action" value="languages" />
			<div class="inside">
				<table width="100%" border="0">
					<tbody>
					<?php foreach ( De_Language_Wrapper::get_languages() as $lang ) { ?>
						<tr>
							<td>
								<label><input type="checkbox" name="show_languages[]" value="<?php echo $lang; ?>"<?php echo ( in_array( $lang, $show_languages ) ? ' checked="checked"' : '' ); ?> /> <?php echo $lang; ?></label>
							</td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
			</div>
			<div class="inside">
				<table width="100%" border="0">
					<tbody>
						<tr>
							<td>
								<input type="submit" value="save" />
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</form>
		<?php } ?>
	</div>
	<?php
}

function de_remove_more_metaboxes() {
	global $current_user;
	global $post;

	if ( ( in_array( 'editor', $current_user->roles ) && get_option( 'de_tweak_backend' ) ) && $post && $post->ID ) {
		remove_meta_box( 'slugdiv', $post->post_type, 'normal' );
		remove_meta_box( 'postimagediv', $post->post_type, 'side' );
	}
}

function de_remove_metaboxes() {
	global $current_user;
	global $post;

	if ( ( in_array( 'editor', $current_user->roles ) && get_option( 'de_tweak_backend' ) ) && $post && $post->ID ) {
		remove_meta_box( 'authordiv', $post->post_type, 'normal' );
		remove_meta_box( 'categorydiv', $post->post_type, 'normal' );
		remove_meta_box( 'commentsdiv', $post->post_type, 'normal' );
		remove_meta_box( 'commentstatusdiv', $post->post_type, 'normal' );
		remove_meta_box( 'formatdiv', $post->post_type, 'normal' );
		remove_meta_box( 'pageparentdiv', $post->post_type, 'normal' );
		remove_meta_box( 'postcustom', $post->post_type, 'normal' );
		remove_meta_box( 'postexcerpt', $post->post_type, 'normal' );
		remove_meta_box( 'revisionsdiv', $post->post_type, 'normal' );
		remove_meta_box( 'tagsdiv-post_tag', $post->post_type, 'normal' );
		remove_meta_box( 'trackbacksdiv', $post->post_type, 'normal' );
		//remove_meta_box( 'submitdiv', $post->post_type, 'normal' );
	}
}

function de_metaboxes_save( $post_id, $post ) {
	if ( ! current_user_can( 'edit_posts' ) )
		return false;

	if ( ( basename( $_SERVER['PHP_SELF'] ) == 'post.php' || basename( $_SERVER['PHP_SELF'] ) == 'post-new.php' ) && $_SERVER['REQUEST_METHOD'] == 'POST' ) {
		if ( $_POST['de_slug'] )
			De_Url::register_url( $post->ID, sanitize_title( $_POST['de_slug'] ) );
		elseif( $post->post_title )
			De_Url::register_url( $post->ID, sanitize_title( $post->post_title ) );
		else
			De_Url::register_url( $post->ID, sanitize_title( $post->post_name ) );
		
		if ( $_POST['de_wp_hooks'] ) {
			update_post_meta( $post->ID, 'de_wp_hooks', base64_encode( serialize( $_POST['de_wp_hooks'] ) ) );
		}
	}
}

function de_no_user_notification() {
	?>
	<script>
		jQuery(function() {
			jQuery( "p:contains('<?php _e( 'A password reset link will be sent to the user via email.' ); ?>')" ).remove();
		});
	</script>
	<?php
}

function de_replace_permalink( $return, $post_id ) {
	$search = "href='" . get_permalink( $post_id ) . "'";
	$replace = "href='" . De_Url::get_url( $post_id ) . "'";
	
	return str_replace( $search, $replace, $return );
}

function de_remove_row_actions( $actions ) {
	global $post;

	if( $post && ! de_is_deleteable( $post->ID ) ) {
		unset( $actions[ 'trash' ] );
		unset( $actions[ 'delete' ] );
	}

	return $actions;
}
