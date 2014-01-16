<?php
add_action( 'add_meta_boxes', 'de_metaboxes_add', 0 );
add_action( 'admin_init', 'de_disable_for_subscribers' );
add_action( 'admin_menu', 'de_adjust_menus' );
add_action( 'admin_menu', 'de_plugin_menu' );
add_action( 'save_post','de_metaboxes_save', 1000, 2 );

add_filter( 'get_sample_permalink_html', 'de_replace_permalink', 10, 2 );
add_filter( 'page_row_actions', 'de_remove_row_actions', 10, 1 );

function de_metaboxes_add() {
	global $post;

	if ( $post && $post->ID ) {
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
		if ( ! in_array( 'administrator', $current_user->roles ) )
			$restricted[] = __( 'Media' );
		if ( in_array( 'editor', $current_user->roles ) ) {
			$restricted = array_merge( $restricted, array( __( 'Dashboard' ), __( 'Posts' ), __( 'Pages' ), __( 'List items' ), __( 'Plugins' ), __( 'Profile' ), __( 'Tools' ), __( 'Settings' ) ) );
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

		if ( in_array( 'editor', $current_user->roles ) ) {
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
	global $wpdb;
	global $options;
	global $user_ID;

	if ( basename( $_SERVER['SCRIPT_FILENAME'] ) == 'plugins.php' && isset( $_GET['page'] ) && $_GET['page'] == 'direct-edit' ) {
		// Check permissions
		if ( ! current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'direct-edit' ) );
		}

		if ( get_option( 'de_options_custom_page_types' ) )
			$options = unserialize( base64_decode( get_option( 'de_options_custom_page_types' ) ) );
		else
			$options = array();

		if ( isset( $_REQUEST['action'] ) && 'automatic_updates' == $_REQUEST['action'] ) {
			update_option( 'automatic_updates_key', $_POST[ 'automatic_updates_key' ] );
			
			wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&saved=true' ) );
		} elseif ( isset( $_REQUEST['action'] ) && 'create_theme' == $_REQUEST['action'] ) {
			// Create theme dir
			$target = get_theme_root() . '/' . sanitize_title( $_POST[ 'theme_name' ] );
			if ( ! is_dir( $target ) ) {
				umask( 0 );
				$result = mkdir( $target, 0777 );
				if ( ! $result ) {
					wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&error=create_theme' ) );
					die();
				}

				if ( empty( $_POST[ 'theme_child' ] ) ) {
					// Copy theme files
					// style.css
					$template = file_get_contents( DIRECT_PATH . 'pro/template/style.css' );
					$template = str_replace( array( '{theme_name}' ), array( $_POST[ 'theme_name' ] ), $template );
					$result = file_put_contents ( $target . '/style.css', $template );
					if ( $result === false ) {
						wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&error=create_theme' ) );
						die();
					}
					chmod( $target . '/style.css', 0777 );
					// functions.php
					$template = file_get_contents( DIRECT_PATH . 'pro/template/functions.php' );
					$result = file_put_contents ( $target . '/functions.php', $template );
					if ( $result === false ) {
						wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&error=create_theme' ) );
						die();
					}
					chmod( $target . '/functions.php', 0777 );
					// Header, footer, front-page.php, home.php ( for blog page ), index.php
					$template = file_get_contents( DIRECT_PATH . 'pro/template/header.php' );
					$result = file_put_contents ( $target . '/header.php', $template );
					if ( $result === false ) {
						wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&error=create_theme' ) );
						die();
					}
					chmod( $target . '/header.php', 0777 );
					$template = file_get_contents( DIRECT_PATH . 'pro/template/footer.php' );
					$template = str_replace( array( '{year}' ), array( date( 'Y' ) ), $template );
					$result = file_put_contents ( $target . '/footer.php', $template );
					if ( $result === false ) {
						wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&error=create_theme' ) );
						die();
					}
					chmod( $target . '/footer.php', 0777 );
					$template = file_get_contents( DIRECT_PATH . 'pro/template/front-page.php' );
					$result = file_put_contents ( $target . '/front-page.php', $template );
					if ( $result === false ) {
						wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&error=create_theme' ) );
						die();
					}
					chmod( $target . '/front-page.php', 0777 );
					$template = file_get_contents( DIRECT_PATH . 'pro/template/home.php' );
					$result = file_put_contents ( $target . '/home.php', $template );
					if ( $result === false ) {
						wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&error=create_theme' ) );
						die();
					}
					chmod( $target . '/home.php', 0777 );
					$template = file_get_contents( DIRECT_PATH . 'pro/template/page.php' );
					$result = file_put_contents ( $target . '/page.php', $template );
					if ( $result === false ) {
						wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&error=create_theme' ) );
						die();
					}
					chmod( $target . '/page.php', 0777 );
					$template = file_get_contents( DIRECT_PATH . 'pro/template/index.php' );
					$result = file_put_contents ( $target . '/index.php', $template );
					if ( $result === false ) {
						wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&error=create_theme' ) );
						die();
					}
					chmod( $target . '/index.php', 0777 );
					// 404.php
					$template = file_get_contents( DIRECT_PATH . 'pro/template/404.php' );
					$result = file_put_contents ( $target . '/404.php', $template );
					if ( $result === false ) {
						wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&error=create_theme' ) );
						die();
					}
					chmod( $target . '/404.php', 0777 );
				} else {
					$template = file_get_contents( DIRECT_PATH . 'pro/template/style-child.css' );
					$template_current = wp_get_theme();
					$template = str_replace( array( '{theme_name}', '{template_name}', '{template_uri}' ), array( $_POST[ 'theme_name' ], $template_current->get( 'Name' ), get_template_directory_uri() ), $template );
					$result = file_put_contents ( $target . '/style.css', $template );
					if ( $result === false ) {
						wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&error=create_theme' ) );
						die();
					}
					chmod( $target . '/style.css', 0777 );
				}
				
				// Create auxiliary dirs and copy login form template
				umask( 0 );
				$result = mkdir( $target . '/de_webform', 0777 );
				if ( ! $result ) {
					wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&error=create_theme' ) );
					die();
				}
				umask( 0 );
				$result = mkdir( $target . '/snippets', 0777 );
				if ( ! $result ) {
					wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&error=create_theme' ) );
					die();
				}
				if ( get_option( 'de_wp_login_redirect' ) ) {
					$template = file_get_contents( DIRECT_PATH . 'pro/template/de_webform/log-in.php' );
					$result = file_put_contents ( $target . '/de_webform/log-in.php', $template );
					if ( $result === false ) {
						wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&error=create_theme' ) );
						die();
					}
					chmod( $target . '/de_webform/log-in.php', 0777 );
				}
				
				// Create custom page templates
				foreach ( $options as $option ) {
					$template = file_get_contents( DIRECT_PATH . 'pro/template/archive-custom_post_type.php' );
					$template = str_replace( array( '{name}' ), array( sanitize_title( $option->name ) ), $template );
					$result = file_put_contents ( $target . '/archive-de_' . sanitize_title( $option->name ) . '.php', $template );
					if ( $result === false ) {
						wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&error=create_theme' ) );
						die();
					}
					chmod( $target . '/archive-de_' . sanitize_title( $option->name ) . '.php', 0777 );
					$template = file_get_contents( DIRECT_PATH . 'pro/template/single-custom_post_type.php' );
					$result = file_put_contents ( $target . '/single-de_' . sanitize_title( $option->name ) . '.php', $template );
					if ( $result === false ) {
						wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&error=create_theme' ) );
						die();
					}
					chmod( $target . '/single-de_' . sanitize_title( $option->name ) . '.php', 0777 );
				}
				
				// Switch theme
				switch_theme( sanitize_title( $_POST[ 'theme_name' ] ) );
			}
			
			wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&saved=true' ) );
		} elseif ( isset( $_REQUEST['action'] ) && 'copy_files' == $_REQUEST['action'] ) {
			if ( ! file_exists( get_stylesheet_directory() . '/direct-edit' ) ) {
				$result = de_copy( DIRECT_PATH . 'theme', get_stylesheet_directory() . '/direct-edit' );
				if ( ! $result ) {
					@de_rmdir( get_stylesheet_directory() . '/direct-edit' );
					wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&error=copy_files' ) );
					die();
				}
			}
			
			wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&saved=true' ) );
		} elseif ( isset( $_REQUEST['action'] ) && 'remove_files' == $_REQUEST['action'] ) {
			if ( file_exists( get_stylesheet_directory() . '/direct-edit' ) ) {
				de_rmdir( get_stylesheet_directory() . '/direct-edit' );
			}
			
			wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&saved=true' ) );
		} elseif ( isset( $_REQUEST['action'] ) && 'create_pages' == $_REQUEST['action'] ) {
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
			update_option( 'page_on_front', $newPostId );

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
			update_option( 'page_for_posts', $newPostId );

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
			
			update_option( 'show_on_front', 'page' );
			
			wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&saved=true' ) );
		} elseif ( isset( $_REQUEST['action'] ) && 'create' == $_REQUEST['action'] ) {
			$option = new stdClass();
			$option->name = $wpdb->escape( $_POST[ 'custom_page_type' ] );
			if ( empty( $options[ $option->name ] ) ) {
				$options[ $option->name ] = $option;
				
				if ( ! file_exists( get_stylesheet_directory() . '/archive-de_' . sanitize_title( $option->name ) . '.php' ) ) {
					$template = file_get_contents( DIRECT_PATH . 'pro/template/archive-custom_post_type.php' );
					$template = str_replace( array( '{name}' ), array( sanitize_title( $option->name ) ), $template );
					file_put_contents ( get_stylesheet_directory() . '/archive-de_' . sanitize_title( $option->name ) . '.php', $template );
					chmod( get_stylesheet_directory() . '/archive-de_' . sanitize_title( $option->name ) . '.php', 0777 );
				}
				if ( ! file_exists( get_stylesheet_directory() . '/single-de_' . sanitize_title( $option->name ) . '.php' ) ) {
					$template = file_get_contents( DIRECT_PATH . 'pro/template/single-custom_post_type.php' );
					file_put_contents ( get_stylesheet_directory() . '/single-de_' . sanitize_title( $option->name ) . '.php', $template );
					chmod( get_stylesheet_directory() . '/single-de_' . sanitize_title( $option->name ) . '.php', 0777 );
				}

				// Create list page
				$newPost = array(
					'post_title' => __( ucfirst( $option->name ), 'direct-edit' ),
					'post_content' => '',
					'post_status' => 'publish',
					'post_date' => date('Y-m-d H:i:s'),
					'post_author' => $user_ID,
					'post_type' => 'page',
					'post_category' => array( 0 )
				);

				$newPostId = wp_insert_post( $newPost );
				De_Url::register_url( $newPostId, sanitize_title( __( strtolower( $option->name ), 'direct-edit' ) ) );
				update_option( 'de_page_for_de_' . sanitize_title( $option->name ), $newPostId );

				if ( De_Language_Wrapper::has_multilanguage() ) {
					De_Language_Wrapper::set_post_language( $newPostId, De_Language_Wrapper::get_default_language() );
					De_Language_Wrapper::create_language_posts( $newPostId );
					
					foreach( De_Language_Wrapper::get_language_posts( $newPostId ) as $lang => $lang_post ) {
						if ( $lang_post->ID == $newPostId )
							continue;
						
						$data = array(
							'ID' => $lang_post->ID,
							'post_title' => __( ucfirst( $option->name ), 'direct-edit' ),
							'post_name' => sanitize_title( __( strtolower( $option->name ), 'direct-edit' ) )
						);
						wp_update_post( $data );

						De_Url::register_url( $lang_post->ID, sanitize_title( __( strtolower( $option->name ), 'direct-edit' ) ) );
						
						De_Language_Wrapper::de_post_type_add( 'de_' . sanitize_title( $option->name ) );
					}
				}

				// Update rewrite rules
				foreach( $options as $option ) {
					register_post_type( 'de_' . sanitize_title( $option->name ),
						array(
							'labels' => array(
								'name' => __( ucfirst( $option->name ), 'direct-edit' )
							),
							'public' => true,
							'hierarchical' => true,
							'supports' => array( 'title', 'editor', 'page-attributes' ),
							'rewrite' => array( 'slug' => sanitize_title( $option->name ) ),
							'has_archive' => true
						)
					);
				}
				flush_rewrite_rules();

				// Save de options
				update_option( 'de_options_custom_page_types', base64_encode( serialize( $options ) ) );
			}

			wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&saved=true' ) );
		} elseif ( isset( $_REQUEST['action'] ) && 'create_template' == $_REQUEST['action'] ) {
			$option = $wpdb->escape( $_REQUEST[ 'custom_page_type' ] );

			if( ! empty( $options[ $option ] ) && is_dir( DIRECT_PATH . 'pro/custom/' . $_REQUEST[ 'template_name' ] ) ) {
				$option = $options[ $option ];

				$target = get_stylesheet_directory();
				if ( ! file_exists( $target . '/custom' ) ) {
					umask( 0 );
					mkdir( $target . '/custom', 0777 );
				}
				if ( file_exists( $target . '/custom/' . sanitize_title( $option->name ) ) ) {
					de_rmdir( $target . '/custom/' . sanitize_title( $option->name ) );
				}
				$result = de_copy( DIRECT_PATH . 'pro/custom/' . $_REQUEST[ 'template_name' ], $target . '/custom/' . sanitize_title( $option->name ) );
				if ( ! $result ) {
					@de_rmdir( $target . '/custom/' . sanitize_title( $option->name ) );
					wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&error=create_template' ) );
					die();
				}
				
				// Setup hook
				if ( file_exists( $target . '/custom/' . sanitize_title( $option->name ) . '/functions.php' ) ) {
					include $target . '/custom/' . sanitize_title( $option->name ) . '/functions.php';
					do_action( 'de_custom_' . $_REQUEST[ 'template_name' ] . '_setup', sanitize_title( $option->name ) );
				}
			}

			wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&saved=true' ) );
		} elseif ( isset( $_REQUEST['action'] ) && 'delete' == $_REQUEST['action'] ) {
			$option = $wpdb->escape( $_REQUEST[ 'custom_page_type' ] );

			if( ! empty( $options[ $option ] ) ) {
				$option = $options[ $option ];

				$id = get_option( 'de_page_for_de_' . sanitize_title( $option->name ) );

				delete_option( 'de_page_for_de_' . sanitize_title( $option->name ) );
				if ( De_Language_Wrapper::has_multilanguage() && De_Language_Wrapper::get_language_posts( $id ) ) {
					foreach( De_Language_Wrapper::get_language_posts( $id ) as $lang_post ) {
						wp_delete_post( $lang_post->ID, true );
					}
				} else {
					wp_delete_post( $id, true );
				}

				
				$args = array(
					'numberposts' => -1,
					'post_type' =>'de_' . sanitize_title( $option->name )
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
				if ( file_exists( $target . '/custom/' . sanitize_title( $option->name ) ) ) {
					de_rmdir( $target . '/custom/' . sanitize_title( $option->name ) );
				}

				if ( De_Language_Wrapper::has_multilanguage() ) {
					De_Language_Wrapper::de_post_type_delete( 'de_' . sanitize_title( $option->name ) );
				}
			}

			wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&saved=true' ) );
		} elseif ( isset( $_REQUEST['action'] ) && 'create_webform' == $_REQUEST['action'] ) {
			if ( post_type_exists( 'de_webform' ) ) {
				$title = $wpdb->escape( $_POST[ 'custom_webform_name' ] );
				
				$webformPost = array(
					'post_title' => $title,
					'post_content' => '',
					'post_status' => 'publish',
					'post_date' => date('Y-m-d H:i:s'),
					'post_author' => $user_ID,
					'post_type' => 'de_webform',
					'post_category' => array( 0 )
				);

				$webformPostId = wp_insert_post( $webformPost );
				$slug = De_Url::register_url( $webformPostId, sanitize_title( $title ) );
				
				update_post_meta( $webformPostId, 'de_webform_template', 'de_webform/' . $slug . '.php' );
				update_post_meta( $webformPostId, 'de_admin_email_from', get_option( 'admin_email' ) );
				update_post_meta( $webformPostId, 'de_user_email_from', get_option( 'admin_email' ) );
				
				if ( De_Language_Wrapper::has_multilanguage() ) {
					De_Language_Wrapper::set_post_language( $webformPostId, De_Language_Wrapper::get_default_language() );
					De_Language_Wrapper::create_language_posts( $webformPostId );
					
					update_post_meta( $webformPostId, 'de_success_page', '/' . De_Language_Wrapper::get_default_language() . '/' );
					
					foreach( De_Language_Wrapper::get_language_posts( $webformPostId ) as $lang => $lang_post ) {
						if ( $lang_post->ID == $loginPostId )
							continue;
						
						$data = array(
							'ID' => $lang_post->ID,
							'post_title' => $title,
							'post_name' => sanitize_title( $title )
						);
						wp_update_post( $data );

						De_Url::register_url( $lang_post->ID, sanitize_title( $title ) );

						update_post_meta( $lang_post->ID, 'de_webform_template', 'de_webform/' . $slug . '.php' );
						update_post_meta( $lang_post->ID, 'de_success_page', "/$lang/" );
						update_post_meta( $lang_post->ID, 'de_admin_email_from', get_option( 'admin_email' ) );
						update_post_meta( $lang_post->ID, 'de_user_email_from', get_option( 'admin_email' ) );
					}
				} else {
					update_post_meta( $webformPostId, 'de_success_page', '/' );
				}

				// Check login form template
				$target = get_stylesheet_directory();

				// Create auxiliary dir and copy form template
				if ( ! file_exists( $target . '/de_webform' ) ) {
					umask( 0 );
					mkdir( $target . '/de_webform', 0777 );
				}
				if ( ! file_exists( $target . '/de_webform/' . $slug . '.php' ) ) {
					$template = file_get_contents( DIRECT_PATH . 'pro/template/single-de_webform.php' );
					$template = str_replace( array( '{title}' ), array( $title ), $template );
					file_put_contents ( $target . '/de_webform/' . $slug . '.php', $template );
					chmod( $target . '/de_webform/' . $slug . '.php', 0777 );
				}
			}

			wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&saved=true' ) );
		} elseif ( isset( $_REQUEST['action'] ) && 'delete_webform' == $_REQUEST['action'] ) {
			$webformPostId = ( int ) $_REQUEST[ 'post' ];
			
			if ( $webformPostId ) {
				if ( De_Language_Wrapper::has_multilanguage() && De_Language_Wrapper::get_language_posts( $webformPostId ) ) {
					foreach( De_Language_Wrapper::get_language_posts( $webformPostId ) as $lang_post ) {
						wp_delete_post( $lang_post->ID, true );
					}
				} else {
					wp_delete_post( $webformPostId, true );
				}
			}
			
			wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&saved=true' ) );
		} elseif ( isset( $_REQUEST['action'] ) && 'wp_hooks' == $_REQUEST['action'] ) {
			$options = $wpdb->escape( $_REQUEST[ 'wp_hooks' ] );
			update_option( 'de_options_wp_hooks', base64_encode( serialize( $options ) ) );
			
			wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&saved=true' ) );
		} elseif ( isset( $_REQUEST[ 'action' ] ) && 'de_options' == $_REQUEST[ 'action' ] ) {
			update_option( 'de_wp_login_redirect', $_REQUEST[ 'wp_login_redirect' ] );
			update_option( 'de_tweak_backend', $_REQUEST[ 'tweak_backend' ] );
			update_option( 'de_tweak_frontend', $_REQUEST[ 'tweak_frontend' ] );
			update_option( 'de_disable_backend_editor', $_REQUEST[ 'disable_backend_editor' ] );
			update_option( 'de_disable_validation', $_REQUEST[ 'disable_validation' ] );
			update_option( 'de_smart_urls', $_REQUEST[ 'smart_urls' ] );
			
			// Handle login form
			if( $_REQUEST['wp_login_redirect'] ) {
				// Create log in form
				if ( post_type_exists( 'de_webform' ) && ( ! get_option( 'de_login_form' ) || ! get_post( get_option( 'de_login_form' ) ) ) ) {
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
					}
					
					update_option( 'de_login_form', $loginPostId );
					
					// Check login form template
					$target = get_stylesheet_directory();

					// Create auxiliary dir and copy login form template
					if ( ! file_exists( $target . '/de_webform' ) ) {
						umask( 0 );
						mkdir( $target . '/de_webform', 0777 );
					}
					if ( ! file_exists( $target . '/de_webform/log-in.php' ) ) {
						$template = file_get_contents( DIRECT_PATH . 'pro/template/de_webform/log-in.php' );
						file_put_contents ( $target . '/de_webform/log-in.php', $template );
						chmod( $target . '/de_webform/log-in.php', 0777 );
					}
				}
			} else {
				$loginPostId = get_option( 'de_login_form' );
				
				if ( $loginPostId ) {
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
			
			wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&saved=true' ) );
		} elseif ( isset( $_REQUEST['action'] ) && 'languages' == $_REQUEST['action'] ) {
			if ( De_Language_Wrapper::has_multilanguage() ) {
				update_option( 'de_options_show_languages', serialize( $_POST[ 'show_languages' ] ) );
			}
			
			wp_redirect( home_url( '/wp-admin/plugins.php?page=direct-edit&saved=true' ) );
		}
	}
	
	add_plugins_page( 'Direct Edit Options', 'Direct Edit', 'manage_options', 'direct-edit', 'de_plugin_page' );
}

function de_plugin_page() {
	global $wpdb;
	global $options;
	global $user_ID;

	// Check permissions
	if ( ! current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'direct-edit' ) );
	}

	if ( isset( $_REQUEST['saved'] ) ) {
		echo '<div id="message" class="updated fade"><p><strong> Settings saved.</strong></p></div>';
	} elseif ( isset( $_REQUEST[ 'error' ] ) ) {
		if ( $_REQUEST[ 'error' ] == 'copy_files' ) {
			echo '<div id="message" class="updated fade"><p><strong> Settings could not be saved. Check folder permissions.</strong></p></div>';
		}
	}
		
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
		<div id="icon-themes" class="icon32">
			<br>
		</div>
		<h2>Direct Edit <?php _e( 'Options', 'direct-edit' ); ?></h2>
		<h3><i><?php _e( 'automatic updates', 'direct-edit' );?></i></h3>
		<div class="inside">
			<form method="post">
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
		<h3><i><?php _e( 'setup wizard', 'direct-edit' );?></i></h3>
		<h3><?php _e( 'create theme', 'direct-edit' );?></h3>
		<div class="inside">
			<form method="post">
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
		<h3><?php _e( 'copy files to current theme', 'direct-edit' );?></h3>
		<div class="inside">
			<form method="post">
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
		<h3><?php _e( 'remove /direct-edit folder from theme', 'direct-edit' );?></h3>
		<div class="inside">
			<form method="post">
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
		<?php if ( get_option( 'show_on_front' ) != 'page' ) { ?>
		<h3><?php _e( 'create home and blog pages', 'direct-edit' );?></h3>
		<div class="inside">
			<form method="post">
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
		<?php } ?>
		<h3><?php _e( 'custom page types', 'direct-edit' );?></h3>
		<div class="inside">
			<table border="0">
				<tbody>
					<tr>
						<td style="width: 30px;"><?php _e( 'name', 'direct-edit' ); ?></td>
						<td><form method="post"><input type="hidden" name="action" value="create" /><input type="text" name="custom_page_type" id="custom_page_type" style="width: 240px;" /> <input type="submit" value="create" /></form></td>
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
														<input type="hidden" name="action" value="create_template" />
														<input type="hidden" name="custom_page_type" value="<?php echo $option->name; ?>" />
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
											<input type="button" onclick="location.href='?page=direct-edit&action=delete&custom_page_type=<?php echo urlencode( $option->name ); ?>'" value="<?php _e( 'remove', 'direct-edit' ); ?>" />
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
		<h3><?php _e( 'custom webforms', 'direct-edit' );?></h3>
		<div class="inside">
			<form method="post">
				<input type="hidden" name="action" value="create_webform" />
				<table border="0">
					<tbody>
						<tr>
							<td style="width: 30px;"><?php _e( 'name', 'direct-edit' ); ?></td>
							<td><input type="text" name="custom_webform_name" id="custom_webform_name" style="width: 240px;" /> <input type="submit" value="create" /></td>
						</tr>
						<?php foreach ( $webforms as $webform ) { ?>
						<tr>
							<td></td>
							<td><a href="?page=direct-edit&action=delete_webform&post=<?php echo $webform->ID; ?>"><?php _e( 'delete', 'direct-edit' ); ?></a> <?php echo $webform->post_title; ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</form>
		</div>
		<?php } ?>
		<h3><i><?php _e( 'hooks on standard wp-functions', 'direct-edit' );?></i></h3>
		<div class="inside">
			<form method="post">
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
		<h3><i><?php _e( 'options', 'direct-edit' );?></i></h3>
		<div class="inside">
			<form method="post">
				<input type="hidden" name="action" value="de_options" />
				<table border="0">
					<tbody>
						<tr>
							<td><input type="hidden" name="wp_login_redirect" value="" /><label><input type="checkbox" name="wp_login_redirect" value="1"<?php echo ( get_option( 'de_wp_login_redirect' ) ? ' checked="checked"' : '' ); ?> /> <?php _e( 'wp-login form redirect', 'direct-edit' ); ?></label></td>
						</tr>
						<tr>
							<td><input type="hidden" name="tweak_backend" value="" /><label><input type="checkbox" name="tweak_backend" value="1"<?php echo ( get_option( 'de_tweak_backend' ) ? ' checked="checked"' : '' ); ?> /> <?php _e( 'tweak backend', 'direct-edit' ); ?></label></td>
						</tr>
						<tr>
							<td><input type="hidden" name="tweak_frontend" value="" /><label><input type="checkbox" name="tweak_frontend" value="1"<?php echo ( get_option( 'de_tweak_frontend' ) ? ' checked="checked"' : '' ); ?> /> <?php _e( 'tweak frontend WordPress toolbar', 'direct-edit' ); ?></label></td>
						</tr>
						<tr>
							<td><input type="hidden" name="disable_backend_editor" value="" /><label><input type="checkbox" name="disable_backend_editor" value="1"<?php echo ( get_option( 'de_disable_backend_editor' ) ? ' checked="checked"' : '' ); ?> /> <?php _e( 'disable backend editing for editor', 'direct-edit' ); ?></label></td>
						</tr>
						<tr>
							<td><input type="hidden" name="disable_validation" value="" /><label><input type="checkbox" name="disable_validation" value="1"<?php echo ( get_option( 'de_disable_validation' ) ? ' checked="checked"' : '' ); ?> /> <?php _e( 'disable text validation', 'direct-edit' ); ?></label></td>
						</tr>
						<tr>
							<td>
								<input type="hidden" name="smart_urls" value="" /><label><input type="checkbox" name="smart_urls" value="1"<?php echo ( get_option( 'de_smart_urls' ) ? ' checked="checked"' : '' ); ?><?php echo ( get_option( 'permalink_structure' ) != '/%postname%/' ? ' disabled="disabled"' : '' ); ?> /> <?php _e( 'use DirectEdit smart url\'s', 'direct-edit' ); ?></label>
								<?php if ( get_option( 'permalink_structure' ) != '/%postname%/' ) { ?>
								<br />
								Set <a href="<?php echo home_url( '/wp-admin/options-permalink.php'); ?>"><i>Permalink Settings</i></a> to <i>Post name</i> to use this option
								<?php } ?>
							</td>
						</tr>
						<tr>
							<td><input type="submit" value="save" /></td>
						</tr>
					</tbody>
				</table>
			</form>
		</div>
		<?php if ( De_Language_Wrapper::has_multilanguage() ) { ?>
		<h3><i><?php _e( 'Show languages', 'direct-edit' ); ?></i></h3>
		<form method="post">
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

function de_metaboxes_save( $post_id, $post ) {
	if ( ! current_user_can( 'edit_posts' ) )
		return false;

	if ( basename( $_SERVER['PHP_SELF'] ) == 'post.php' || basename( $_SERVER['PHP_SELF'] ) == 'post-new.php' && $_SERVER['REQUEST_METHOD'] == 'POST' ) {
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
