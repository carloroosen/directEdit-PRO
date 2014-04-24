<?php
// Automatic Updates
require_once DIRECT_PATH . 'pro/plugin-updates/plugin-update-checker.php';
$de_update_checker = new PluginUpdateChecker(
	'http://directedit.co/downloads/info.json',
	DIRECT_PATH . 'direct-edit.php'
);

function de_pro_add_updates_key( $query ){
	$query[ 'key' ] = get_option( 'automatic_updates_key' );
	$query[ 'url' ] = urlencode( get_option( 'siteurl' ) );

	return $query;
}
$de_update_checker->addQueryArgFilter( 'de_pro_add_updates_key' );

// General setup
add_action( 'admin_bar_menu', 'de_pro_tweak_menu', 90 );
add_action( 'after_setup_theme', 'de_pro_remove_admin_bar' );
add_action( 'after_switch_theme', 'de_pro_copy_de_files' );
add_action( 'before_delete_post', 'de_pro_disable_pages_removal' );
add_action( 'init', 'de_pro_create_post_types', 0 );
add_action( 'init', 'de_pro_capabilities' );
add_action( 'login_init', 'de_pro_login_redirect' );
add_action( 'plugins_loaded', 'de_pro_extensions_include' );
add_action( 'pre_get_posts', 'de_pro_filter_posts' );
add_action( 'template_include', 'de_pro_custom_template' );
add_action( 'template_redirect', 'de_pro_404_override' );
add_action( 'template_redirect', 'de_pro_nonactive_languages_redirect' );
add_action( 'template_redirect', 'de_pro_perform_actions', 5 );
add_action( 'wp', 'de_pro_add_filter_permalink' );
add_action( 'wp', 'de_pro_handle_url', 0 );
add_action( 'wp_print_footer_scripts', 'de_pro_footer_scripts', 20 );

add_filter( 'edit_post_link', 'de_pro_remove_edit_post_link' );
add_filter( 'logout_url', 'de_pro_logout_home', 10, 2 );
add_filter( 'wp_nav_menu_objects', 'de_pro_nav_menu_filter', 10, 2 );

function de_pro_tweak_menu( $wp_admin_bar ) {
	global $current_user;
	global $direct_queried_object;

	remove_action( 'admin_bar_menu', 'de_adjust_menu', 100 );

	if ( ( current_user_can( 'edit_theme_options' ) || current_user_can( 'edit_de_frontend' ) ) && get_option( 'de_menu_editor_enabled' ) && get_option( 'de_edit_menu_page' ) == $direct_queried_object->ID ) {
		$wp_admin_bar->remove_menu( 'site-name' );
		$wp_admin_bar->remove_menu( 'view-site' );
		$wp_admin_bar->remove_menu( 'dashboard' );
		$wp_admin_bar->remove_menu( 'menus' );
		$wp_admin_bar->remove_menu( 'user-info' );
		$wp_admin_bar->remove_menu( 'edit-profile' );
		$wp_admin_bar->remove_menu( 'customize' );
		$wp_admin_bar->remove_menu( 'updates' );
		$wp_admin_bar->remove_menu( 'wp-logo' );
		$wp_admin_bar->remove_menu( 'themes' );
		$wp_admin_bar->remove_menu( 'comments' );
		$wp_admin_bar->remove_menu( 'new-content' );
		$wp_admin_bar->remove_menu( 'edit' );
		
		$wp_admin_bar->add_node( array(
				'id' => 'menu-back',
				'title' => __( 'Back to website', 'direct-edit' ),
				'parent' => '',
				'href' => home_url(),
				'group' => '',
				'meta' => array( 'title' => __( 'Back to website', 'direct-edit' ) )
			)
		);
		$wp_admin_bar->add_node( array(
				'id' => 'menu-save',
				'title' => __( 'Save', 'direct-edit' ),
				'parent' => '',
				'href' => '#',
				'group' => '',
				'meta' => array( 'title' => __( 'Save', 'direct-edit' ) )
			)
		);
	} elseif ( get_option( 'de_tweak_backend' ) && is_admin() || get_option( 'de_tweak_frontend' ) && ! is_admin() ) {
		// Menu changes are needed to edit only
		if ( current_user_can('edit_posts') || current_user_can( 'edit_users' ) || current_user_can( 'edit_theme_options' ) || current_user_can( 'edit_de_frontend' ) ) {
			// Remove some unwanted menuitems
			if ( in_array( 'administrator', $current_user->roles ) ) {
				$wp_admin_bar->remove_menu( 'site-name' );
				if ( is_admin() ) {
					$wp_admin_bar->remove_menu( 'view-site' );
					$wp_admin_bar->add_menu( array(
							'id' => 'site-name',
							'title' => __( 'Website', 'direct-edit' ),
							'parent' => '',
							'href' => get_site_url(),
							'group' => '',
							'meta' => array()
						)
					);
				} else {
					$wp_admin_bar->add_menu( array(
							'id' => 'site-name',
							'title' => __( 'Backend', 'direct-edit' ),
							'parent' => '',
							'href' => get_site_url() . '/wp-admin/',
							'group' => '',
							'meta' => array()
						)
					);
				}
			} elseif ( in_array( 'editor', $current_user->roles ) ) {
				$wp_admin_bar->remove_menu( 'site-name' );
				if ( is_admin() ) {
					$wp_admin_bar->remove_menu( 'view-site' );
					$wp_admin_bar->add_menu( array(
							'id' => 'site-name',
							'title' => __( 'Website', 'direct-edit' ),
							'parent' => '',
							'href' => get_site_url(),
							'group' => '',
							'meta' => array()
						)
					);
				}
				$wp_admin_bar->remove_menu( 'dashboard' );
				$wp_admin_bar->remove_menu( 'menus' );
				$wp_admin_bar->remove_menu( 'user-info' );
				$wp_admin_bar->remove_menu( 'edit-profile' );
				$wp_admin_bar->add_menu( array(
						'id' => 'menus',
						'title' => __( 'Menus', 'direct-edit' ),
						'parent' => '',
						'href' => get_site_url() . '/wp-admin/nav-menus.php',
						'group' => '',
						'meta' => array()
					)
				);
			} else {
				$wp_admin_bar->remove_menu( 'site-name' );
				if ( is_admin() ) {
					$wp_admin_bar->remove_menu( 'view-site' );
				}
				$wp_admin_bar->remove_menu( 'dashboard' );
				$wp_admin_bar->remove_menu( 'menus' );
				$wp_admin_bar->remove_menu( 'my-account' );
				$wp_admin_bar->remove_menu( 'search' );
				$wp_admin_bar->remove_menu( 'wp-logo' );
			}
			
			$wp_admin_bar->remove_menu( 'customize' );
			$wp_admin_bar->remove_menu( 'updates' );
			$wp_admin_bar->remove_menu( 'wp-logo' );
			$wp_admin_bar->remove_menu( 'themes' );
			$wp_admin_bar->remove_menu( 'comments' );
		} else {
			$wp_admin_bar->remove_menu( 'site-name' );
			if ( is_admin() ) {
				$wp_admin_bar->remove_menu( 'view-site' );
			}
			$wp_admin_bar->remove_menu( 'dashboard' );
			$wp_admin_bar->remove_menu( 'menus' );
			$wp_admin_bar->remove_menu( 'my-account' );
			$wp_admin_bar->remove_menu( 'search' );
			$wp_admin_bar->remove_menu( 'wp-logo' );
		}
		
		// Menu changes are needed to edit only
		if ( current_user_can('edit_posts') || current_user_can( 'edit_users' ) || current_user_can( 'edit_theme_options' ) || current_user_can( 'edit_de_frontend' ) ) {
			$wp_admin_bar->remove_menu( 'edit' );
			
			$all_toolbar_nodes = $wp_admin_bar->get_nodes();
			foreach ( $all_toolbar_nodes as $node ) {
				if ( $node->parent == 'new-content' ) {
					$wp_admin_bar->remove_menu( $node->id );
				}
			}
			$wp_admin_bar->remove_menu( 'new-content' );

			if ( current_user_can('edit_posts') || current_user_can( 'edit_de_frontend' ) ) {
				$wp_admin_bar->add_node( array(
						'id' => 'new-content',
						'title' => '<span class="ab-icon"></span><span class="ab-label">New</span>',
						'parent' => '',
						'group' => '',
						'meta' => array( 'title' => 'Add New' )
					)
				);
				
				foreach( get_post_types( array( 'show_ui' => true ), 'objects' ) as $postType ) {
					if ( ! in_array( $postType->name, array( 'post', 'page' ) ) && ( in_array( $postType->name, array( 'de_list_item', 'de_webform' ) ) || strpos( $postType->name, 'de_' ) !== 0 ) )
						continue;

					$wp_admin_bar->add_node( array(
							'id' => 'new-' . $postType->name,
							'title' => __( $postType->labels->singular_name, 'direct-edit' ),
							'parent' => 'new-content',
							'href' => add_query_arg( array( 'de_add' => $postType->name ), get_site_url() ),
							'group' => '',
							'meta' => array()
						)
					);
				}

				if ( ! is_admin() ) {
					if ( is_object( $direct_queried_object ) && isset( $direct_queried_object->ID ) ) {
						$wp_admin_bar->add_node( array(
								'id' => 'page-options',
								'title' => __( 'Page options', 'direct-edit' ),
								'parent' => '',
								'href' => '#',
								'group' => '',
								'meta' => array( 'title' => __( 'Page options', 'direct-edit' ) )
							)
						);
						
						if ( de_is_hideable( $direct_queried_object->ID ) ) {
							if ( de_is_hidden( $direct_queried_object->ID ) ) {
								$wp_admin_bar->add_node( array(
										'id' => 'post-show',
										'title' => __('Show'),
										'parent' => '',
										'href' => get_permalink( $direct_queried_object->ID ) . '?de_show=1',
										'group' => '',
										'meta' => array( 'title' => __('Show') )
									)
								);
							} else {
								$wp_admin_bar->add_node( array(
										'id' => 'post-hide',
										'title' => __('Hide'),
										'parent' => '',
										'href' => get_permalink( $direct_queried_object->ID ) . '?de_hide=1',
										'group' => '',
										'meta' => array( 'title' => __('Hide') )
									)
								);
							}
						}
						
						// We can't delete webforms in frontend
						if ( de_is_deleteable( $direct_queried_object->ID ) && ! in_array( $direct_queried_object->post_type, array( 'de_webform' ) ) ) {
							$wp_admin_bar->add_node( array(
									'id' => 'post-delete',
									'title' => __('Delete'),
									'parent' => '',
									'href' => get_permalink( $direct_queried_object->ID ) . '?de_delete=1',
									'group' => '',
									'meta' => array(
										'title' => __('Delete'),
										'onclick' => 'return confirm( "' . __( 'Delete this page in all languages' ) . '" );'
									)
								)
							);
						}
					}
					
					$show = $_SESSION[ 'de_show_all' ];
					$wp_admin_bar->add_menu( array(
						'parent' => '',
						'id' => 'show-all',
						'title' => sprintf( '<input name="de_show_all" type="checkbox" value="1" %s /> %s', $show ? 'checked="checked"' : '', __( 'Show all', 'direct-edit' ) ),
						'href' => add_query_arg( 'de_show_all', $show ? '0' : '1', $_SERVER['REQUEST_URI'] )
					) );
				}
			}
			
			if ( ! is_admin() ) {
				$wp_admin_bar->add_node( array(
						'id' => 'save-page',
						'title' => __( 'Save page', 'direct-edit' ),
						'parent' => '',
						'href' => '#',
						'group' => '',
						'meta' => array( 'title' => __( 'Save page', 'direct-edit' ) )
					)
				);
			}
		}
	} elseif ( ! is_admin() || get_option( 'de_tweak_backend' ) && is_admin() ) {
		// Menu changes are needed to edit only
		if ( current_user_can('edit_posts') || current_user_can( 'edit_users' ) || current_user_can( 'edit_theme_options' ) || current_user_can( 'edit_de_frontend' ) ) {
			$wp_admin_bar->remove_menu( 'edit' );
			
			$all_toolbar_nodes = $wp_admin_bar->get_nodes();
			foreach ( $all_toolbar_nodes as $node ) {
				if ( $node->parent == 'new-content' ) {
					$wp_admin_bar->remove_menu( $node->id );
				}
			}

			if ( current_user_can('edit_posts') || current_user_can( 'edit_de_frontend' ) ) {
				foreach( get_post_types( array( 'show_ui' => true ), 'objects' ) as $postType ) {
					if ( ! in_array( $postType->name, array( 'post', 'page' ) ) && ( in_array( $postType->name, array( 'de_list_item', 'de_webform' ) ) || strpos( $postType->name, 'de_' ) !== 0 ) )
						continue;

					$wp_admin_bar->add_node( array(
							'id' => 'new-' . $postType->name,
							'title' => __( $postType->labels->singular_name, 'direct-edit' ),
							'parent' => 'new-content',
							'href' => add_query_arg( array( 'de_add' => $postType->name ), get_site_url() ),
							'group' => '',
							'meta' => array()
						)
					);
				}

				if ( ! is_admin() ) {
					if ( is_object( $direct_queried_object ) && isset( $direct_queried_object->ID ) ) {
						$wp_admin_bar->add_node( array(
								'id' => 'page-options',
								'title' => __( 'Page options', 'direct-edit' ),
								'parent' => '',
								'href' => '#',
								'group' => '',
								'meta' => array( 'title' => __( 'Page options', 'direct-edit' ) )
							)
						);
						
						if ( de_is_hideable( $direct_queried_object->ID ) ) {
							if ( de_is_hidden( $direct_queried_object->ID ) ) {
								$wp_admin_bar->add_node( array(
										'id' => 'post-show',
										'title' => __('Show'),
										'parent' => '',
										'href' => get_permalink( $direct_queried_object->ID ) . '?de_show=1',
										'group' => '',
										'meta' => array( 'title' => __('Show') )
									)
								);
							} else {
								$wp_admin_bar->add_node( array(
										'id' => 'post-hide',
										'title' => __('Hide'),
										'parent' => '',
										'href' => get_permalink( $direct_queried_object->ID ) . '?de_hide=1',
										'group' => '',
										'meta' => array( 'title' => __('Hide') )
									)
								);
							}
						}
						
						// We can't delete webforms in frontend
						if ( de_is_deleteable( $direct_queried_object->ID ) && ! in_array( $direct_queried_object->post_type, array( 'de_webform' ) ) ) {
							$wp_admin_bar->add_node( array(
									'id' => 'post-delete',
									'title' => __('Delete'),
									'parent' => '',
									'href' => get_permalink( $direct_queried_object->ID ) . '?de_delete=1',
									'group' => '',
									'meta' => array(
										'title' => __('Delete'),
										'onclick' => 'return confirm( "' . __( 'Delete this page in all languages' ) . '" );'
									)
								)
							);
						}
					}
					
					$show = $_SESSION[ 'de_show_all' ];
					$wp_admin_bar->add_menu( array(
						'parent' => '',
						'id' => 'show-all',
						'title' => sprintf( '<input name="de_show_all" type="checkbox" value="1" %s /> %s', $show ? 'checked="checked"' : '', __( 'Show all', 'direct-edit' ) ),
						'href' => add_query_arg( 'de_show_all', $show ? '0' : '1', $_SERVER['REQUEST_URI'] )
					) );
				}
			}
			
			if ( ! is_admin() ) {
				$wp_admin_bar->add_node( array(
						'id' => 'save-page',
						'title' => __( 'Save page', 'direct-edit' ),
						'parent' => '',
						'href' => '#',
						'group' => '',
						'meta' => array( 'title' => __( 'Save page', 'direct-edit' ) )
					)
				);
			}
		}
	}
}

function de_pro_remove_admin_bar() {
	if ( get_option( 'de_tweak_frontend' ) ) {
		if ( ! ( current_user_can('edit_posts') || current_user_can( 'edit_users' ) || current_user_can( 'edit_theme_options' ) || current_user_can( 'edit_de_frontend' ) ) ) {
			show_admin_bar( false );
		}
	}
}

function de_pro_copy_de_files() {
	if ( get_option( 'de_options_custom_page_types' ) )
		$options = unserialize( base64_decode( get_option( 'de_options_custom_page_types' ) ) );
	else
		$options = array();

	$target = get_stylesheet_directory();
	
	// Create auxiliary dirs and copy login form template
	if ( ! file_exists( $target . '/de_webform' ) ) {
		umask( 0 );
		mkdir( $target . '/de_webform', 0777 );
		if ( ! file_exists( $target . '/de_webform/log-in.php' ) && get_option( 'de_wp_login_redirect' ) ) {
			$template = file_get_contents( DIRECT_PATH . 'pro/template/de_webform/log-in.php' );
			file_put_contents ( $target . '/de_webform/log-in.php', $template );
			chmod( $target . '/de_webform/log-in.php', 0777 );
		}
	}
	
	// Create custom page templates
	foreach ( $options as $option ) {
		if ( ! file_exists( $target . '/archive-de_' . sanitize_title( $option->name ) . '.php' ) ) {
			$template = file_get_contents( DIRECT_PATH . 'pro/template/archive-custom_post_type.php' );
			$template = str_replace( array( '{name}' ), array( sanitize_title( $option->name ) ), $template );
			file_put_contents ( $target . '/archive-de_' . sanitize_title( $option->name ) . '.php', $template );
			chmod( $target . '/archive-de_' . sanitize_title( $option->name ) . '.php', 0777 );
		}
		if ( ! file_exists( $target . '/single-de_' . sanitize_title( $option->name ) . '.php' ) ) {
			$template = file_get_contents( DIRECT_PATH . 'pro/template/single-custom_post_type.php' );
			file_put_contents ( $target . '/single-de_' . sanitize_title( $option->name ) . '.php', $template );
			chmod( $target . '/single-de_' . sanitize_title( $option->name ) . '.php', 0777 );
		}
	}
}

function de_pro_disable_pages_removal( $post_id ) {
	if ( ! de_is_deleteable( $post_id ) ) {
		if ( is_admin() ) {
			wp_redirect( home_url( '/wp-admin/edit.php?post_type=page' ) );
		} else {
			wp_redirect( home_url() );
		}
		die();
	}
}

function de_pro_create_post_types() {
	register_post_type( 'de_list_item',
		array(
			'labels' => array(
				'name' => __( 'List items', 'direct-edit' ),
				'singular_name' => __( 'List item', 'direct-edit' )
			),
		'public' => true,
		'rewrite' => array( 'slug' => 'list_items' )
		)
	);

	if ( get_option( 'de_options_custom_page_types' ) )
		$options = unserialize( base64_decode( get_option( 'de_options_custom_page_types' ) ) );
	else
		$options = array();

	foreach( $options as $option ) {
		register_post_type( 'de_' . sanitize_title( $option->name ),
			array(
				'labels' => array(
					'name' => __( ucfirst( $option->name ), 'direct-edit' )
				),
				'public' => true,
				'hierarchical' => true,
				'supports' => array( 'title', 'editor', 'author', 'page-attributes' ),
				'rewrite' => array( 'slug' => sanitize_title( $option->name ) ),
				'has_archive' => true
			)
		);
	}
}

function de_pro_capabilities() {
	$admin = get_role( 'administrator' );
	if ( is_object( $admin ) && ! user_can( $admin->ID, 'edit_de_frontend' ) ) {
		$admin->add_cap( 'edit_de_frontend', true );
	}
	
	$editor = get_role( 'editor' );
	if ( is_object( $editor ) && ! user_can( $editor->ID, 'edit_de_frontend' ) ) {
		$editor->add_cap( 'edit_de_frontend', true );
	}
	if ( get_option( 'de_tweak_backend' ) || get_option( 'de_tweak_frontend' ) ) {
		$editor->add_cap( 'edit_themes' );
		$editor->add_cap( 'edit_theme_options' );
	}
}

function de_pro_login_redirect() {
	if ( get_option( 'de_wp_login_redirect' ) ) {
		$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'login';
		$redirect_to = ! empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : 'wp-login.php?loggedout=true';

		if ( $action != 'logout' ) {
			if ( get_option( 'de_smart_urls' ) && get_option( 'permalink_structure' ) == '/%postname%/' ) {
				add_filter( 'post_type_link', 'de_filter_permalink', 10, 2 );
			}
			if ( strpos( de_get_login_form_permalink(), 'wp-login.php' ) === false ) {
				wp_redirect( de_get_login_form_permalink() );
				exit;
			}
		}
	}
}

function de_pro_extensions_include() {
	remove_action( 'plugins_loaded', 'de_extensions_default', 100 );
	
	// Include multilanguage extensions
	if ( is_plugin_active( 'polylang/polylang.php' ) ) {
		de_pro_include( DIRECT_PATH . 'pro/extensions/multilanguage/de_language-wrapper-polylang.php', DIRECT_PATH . 'extensions/multilanguage/de_language-wrapper-default.php' );
		add_filter( 'locale', 'de_pro_set_locale' );
	} else {
		require_once DIRECT_PATH . 'extensions/multilanguage/de_language-wrapper-default.php';
	}
}

function de_pro_filter_posts( $query ) {
	if ( ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_de_frontend' ) ) && ! empty( $_SESSION[ 'de_show_all' ] ) && ! is_admin() ) {
		$query->query_vars[ 'post_status' ] = 'any';
	}
}

function de_pro_custom_template( $template ) {
	global $direct_queried_object;

	if ( get_option( 'de_options_custom_page_types' ) )
		$options = unserialize( base64_decode( get_option( 'de_options_custom_page_types' ) ) );
	else
		$options = array();

	if ( $direct_queried_object ) {
		if ( de_is_front_page( $direct_queried_object->ID ) ) {
			if ( is_dir( get_stylesheet_directory() . '/custom/front-page' ) && file_exists( get_stylesheet_directory() . '/custom/front-page/front-page.php' ) ) {
				$template = get_stylesheet_directory() . '/custom/front-page/front-page.php';
				
				if ( file_exists( dirname( $template ) . '/functions.php' ) ) {
					include dirname( $template ) . '/functions.php';
				}
			}
		} elseif ( $direct_queried_object->post_type == 'de_webform' ) {
			if ( is_dir( get_stylesheet_directory() . '/de_webform/custom/' . $direct_queried_object->post_name ) && file_exists( get_stylesheet_directory() . '/de_webform/custom/' . $direct_queried_object->post_name . '/single-de_webform.php' ) ) {
				$template = get_stylesheet_directory() . '/de_webform/custom/' . $direct_queried_object->post_name . '/single-de_webform.php';
				
				if ( file_exists( dirname( $template ) . '/functions.php' ) ) {
					include dirname( $template ) . '/functions.php';
				}
			}
		} else {
			foreach( $options as $option ) {
				if ( $direct_queried_object->post_type == 'de_' . sanitize_title( $option->name ) ) {
					// dE post type
					if ( is_dir( get_stylesheet_directory() . '/custom/' . sanitize_title( $option->name ) ) && file_exists( get_stylesheet_directory() . '/custom/' . sanitize_title( $option->name ) . '/single.php' ) ) {
						$template = get_stylesheet_directory() . '/custom/' . sanitize_title( $option->name ) . '/single.php';
						break;
					}
				} elseif ( get_option( 'de_page_for_de_' . sanitize_title( $option->name ) ) == $direct_queried_object->ID || de_is_language_post( get_option( 'de_page_for_de_' . sanitize_title( $option->name ) ), $direct_queried_object->ID ) ) {
					// dE page for dE post type
					if ( is_dir( get_stylesheet_directory() . '/custom/' . sanitize_title( $option->name ) ) && file_exists( get_stylesheet_directory() . '/custom/' . sanitize_title( $option->name ) . '/archive.php' ) ) {
						$template = get_stylesheet_directory() . '/custom/' . sanitize_title( $option->name ) . '/archive.php';
						break;
					}
				}
			}
		}
	}
	
	// Include custom functions.php
	foreach( $options as $option ) {
		if ( get_stylesheet_directory() . '/custom/' . sanitize_title( $option->name ) == dirname( $template ) && file_exists( dirname( $template ) . '/functions.php' ) ) {
			include dirname( $template ) . '/functions.php';
			break;
		}
	}
	
	return $template;
}

function de_pro_404_override() {
	global $wp_query;
	global $post_type;
	global $post;
	global $wp;

	if ( is_404() ) {
		if( get_option( 'de_smart_urls' ) && get_option( 'permalink_structure' ) == '/%postname%/' ) {
			$uri =  explode( '/', $wp->request );

			if ( ! empty( $uri[ 0 ] ) ) {
				if ( $uri[ 0 ] == 'edit' ) {
					// /edit functionality
					if ( is_user_logged_in() ) {
						wp_redirect( home_url() );
						die();
					} else {
						wp_redirect( de_get_login_form_permalink() );
						die();
					}
				}
			}
		}
	}
}

function de_pro_nonactive_languages_redirect(){
	global $direct_queried_object;

	if ( De_Language_Wrapper::has_multilanguage() ) {
		if ( is_array( unserialize( get_option( 'de_options_show_languages' ) ) ) )
			$show_languages = unserialize( get_option( 'de_options_show_languages' ) );
		else
			$show_languages = array();

		// If there are no allowed languages we even don't try to perform a redirect
		if( count( $show_languages ) && ! in_array( De_Language_Wrapper::get_current_language(), $show_languages ) && ! ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_de_frontend' ) ) ) {
			// Try to redirect to the default language
			$lang = De_Language_Wrapper::get_default_language();
			if( in_array( $lang, $show_languages ) ) {
				if ( $direct_queried_object && De_Language_Wrapper::get_language_post( $direct_queried_object->ID, $lang ) ) {
					$redirect = get_permalink( De_Language_Wrapper::get_language_post( $direct_queried_object->ID, $lang )->ID );
				} else {
					$redirect = home_url( $lang );
				}
				wp_redirect( $redirect );
				die();
			}

			// Try all other languages
			foreach ( De_Language_Wrapper::get_languages() as $lang ) {
				if( in_array( $lang, $show_languages ) ) {
					if ( $direct_queried_object && De_Language_Wrapper::get_language_post( $direct_queried_object->ID, $lang ) ) {
						$redirect = get_permalink( De_Language_Wrapper::get_language_post( $direct_queried_object->ID, $lang )->ID );
					} else {
						$redirect = home_url( $lang );
					}
					wp_redirect( $redirect );
					die();
				}
			}
		}
	}
}

function de_pro_perform_actions() {
	global $wp_query;
	global $post_type;
	global $post;
	global $wp;
	global $direct_queried_object;
	
	// Check Edit menu page permissions
	if ( get_option( 'de_menu_editor_enabled' ) && get_option( 'de_edit_menu_page' ) == $direct_queried_object->ID && ! ( current_user_can( 'edit_theme_options' ) || current_user_can( 'edit_de_frontend' ) ) ) {
		wp_redirect( home_url() );
		die();
	}
	
	if ( ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_de_frontend' ) ) ) {
		if( isset( $_GET[ 'de_show_all' ] ) ) {
			$_SESSION[ 'de_show_all' ] = ( int ) $_GET[ 'de_show_all' ];
			wp_redirect( home_url( $wp->request ) );
			die();
		}

		if( is_object( $direct_queried_object ) && isset( $direct_queried_object->ID ) ) {
			if( ! empty( $_GET[ 'de_hide' ] ) ) {
				$p = array();
				$p[ 'ID' ] = $direct_queried_object->ID;
				$p[ 'post_status' ] = 'draft';
				wp_update_post( $p );
				wp_redirect( home_url( $wp->request ) );
				die();
			}
			if( ! empty( $_GET[ 'de_show' ] ) ) {
				$p = array();
				$p[ 'ID' ] = $direct_queried_object->ID;
				$p[ 'post_status' ] = 'publish';
				wp_update_post( $p );
				wp_redirect( home_url( $wp->request ) );
				die();
			}
			if( ! empty( $_GET[ 'de_delete' ] ) ) {
				// Hi Carlo, I dedicate this piece of crazy code to you
				$redirect = '';

				if ( $direct_queried_object->post_type == 'page' ) {
					// Generic pages
					if ( ( $locations = get_nav_menu_locations() ) && isset( $locations[ 'direct_main' ] ) ) {
						$menu_items = wp_get_nav_menu_items( $locations[ 'direct_main' ] );
						foreach( $menu_items as $menu_item ) {
							if(  $menu_item->object == $direct_queried_object->post_type && ( $menu_item->object_id == $direct_queried_object->ID || de_is_language_post( $menu_item->object_id, $direct_queried_object->ID ) ) ) {
								$parent_menu_id = $menu_item->menu_item_parent;
								break;
							}
						}
						foreach( $menu_items as $menu_item ) {
							if( $menu_item->ID == $parent_menu_id && $menu_item->object == 'page' ) {
								$redirect = get_permalink( $menu_item->object_id );
								break;
							}
						}
					}
				} elseif ( $direct_queried_object->post_type == 'post' && get_option( 'show_on_front' ) == 'page' && get_option( 'page_for_posts' ) ) {
					if ( De_Language_Wrapper::has_multilanguage() && De_Language_Wrapper::get_post_language( $direct_queried_object->ID ) && De_Language_Wrapper::get_language_post( get_option( 'page_for_posts' ), De_Language_Wrapper::get_post_language( $direct_queried_object->ID ) )->ID ) {
						$redirect = get_permalink( De_Language_Wrapper::get_language_post( get_option( 'page_for_posts' ), De_Language_Wrapper::get_post_language( $direct_queried_object->ID ) )->ID );
					} else {
						$redirect = get_permalink( get_option( 'page_for_posts' ) );
					}
				} elseif ( get_post_meta( $direct_queried_object->ID, 'de_post_parent', true ) && get_post( get_post_meta( $direct_queried_object->ID, 'de_post_parent', true ) ) ) {
					$redirect = get_permalink( get_post_meta( $direct_queried_object->ID, 'de_post_parent', true ) );
				}
				
				if ( empty( $redirect ) ) {
					if ( De_Language_Wrapper::has_multilanguage() ) {
						$redirect = home_url( De_Language_Wrapper::get_current_language() );
					} else {
						$redirect = home_url();
					}
				}
				
				// Delete posts in all languages if needed
				if ( De_Language_Wrapper::has_multilanguage() && De_Language_Wrapper::get_language_posts( $direct_queried_object->ID ) ) {
					foreach( De_Language_Wrapper::get_language_posts( $direct_queried_object->ID ) as $lang_post ) {
						wp_delete_post( $lang_post->ID, true );
					}
				} else {
					wp_delete_post( $direct_queried_object->ID, true );
				}
				
				wp_redirect( $redirect );
				die();
			}
		}
	}
}

function de_pro_add_filter_permalink() {
	if ( get_option( 'de_smart_urls' ) && get_option( 'permalink_structure' ) == '/%postname%/' ) {
		add_filter( 'page_link', 'de_filter_permalink', 10, 2 );
		add_filter( 'post_link', 'de_filter_permalink', 10, 2 );
		add_filter( 'post_type_link', 'de_filter_permalink', 10, 2 );
	}
}

function de_pro_handle_url() {
	global $wp;
	global $post_type;
	global $post;
	global $direct_queried_object;
	global $wp_query;

	if ( ! is_admin() ) {
		$p = De_Url::get_post( $wp->request );

		if ( get_option( 'de_smart_urls' ) && get_option( 'permalink_structure' ) == '/%postname%/' || de_is_de_archive( $p->ID ) ) {
			if ( $p && $p->ID && ( empty( $wp->query_vars['page_id'] ) || $wp->query_vars['page_id'] == $p->ID ) ) {
				status_header( 200 );
				$wp_query->is_404 = false;
				
				$direct_queried_object = $p;

				// If it is a dE archive page or dE archive language page, then set post_type selection
				foreach( get_post_types( array( 'show_ui' => true ), 'objects' ) as $pt ) {
					if ( get_option( 'de_page_for_' . $pt->name ) == $direct_queried_object->ID || de_is_language_post( get_option( 'de_page_for_' . $pt->name ), $direct_queried_object->ID ) ) {
						$request[ 'post_type' ] = $pt->name;
						$request[ 'posts_per_page' ] = -1;
						$request[ 'orderby' ] = 'menu_order';
						$request[ 'order' ] = 'ASC';
						query_posts( $request );
						
						$post_type = $pt->name;
						$posts = get_posts( $request );
						if ( is_array( $posts ) && count( $posts ) ) {
							$post = $posts[ 0 ];
							setup_postdata( $post );
						}
						
						return;
					}
				}
				
				if( $direct_queried_object->post_type == 'page' ) {
					$request[ 'pagename' ] = $direct_queried_object->post_name;
				} else {
					$request[ $direct_queried_object->post_type ] = $direct_queried_object->post_name;
					$request[ 'post_type' ] = $direct_queried_object->post_type;
					$request[ 'name' ] = $direct_queried_object->post_name;
				}
				query_posts( $request );
				
				$post_type = $direct_queried_object->post_type;
				$post = $direct_queried_object;
				setup_postdata( $post );
				
				// Restore custom query vars
				$public_query_vars = array();
				$public_query_vars = apply_filters( 'query_vars', $public_query_vars );

				foreach ( $public_query_vars as $wpvar ) {
					if ( isset( $_POST[ $wpvar ] ) )
						$wp_query->query_vars[ $wpvar ] = $_POST[ $wpvar ];
					elseif ( isset( $_GET[ $wpvar ] ) )
						$wp_query->query_vars[ $wpvar ] = $_GET[ $wpvar ];
				}
			}
		} else {
			if ( ! empty( $post ) ) {
				// Even if we don't use our permalinks, we need post_type selection
				foreach( get_post_types( array( 'show_ui' => true ), 'objects' ) as $pt ) {
					if ( get_option( 'de_page_for_' . $pt->name ) == $post->ID || de_is_language_post( get_option( 'de_page_for_' . $pt->name ), $post->ID ) ) {
						$direct_queried_object = $post;

						$request[ 'post_type' ] = $pt->name;
						$request[ 'posts_per_page' ] = -1;
						$request[ 'orderby' ] = 'menu_order';
						$request[ 'order' ] = 'ASC';
						query_posts( $request );
						
						$post_type = $pt->name;
						$posts = get_posts( $request );
						if ( is_array( $posts ) && count( $posts ) ) {
							$post = $posts[ 0 ];
							setup_postdata( $post );
						}
						
						return;
					}
				}
			}
		}
	}
}

function de_pro_remove_edit_post_link( $link ) {
	global $current_user;
	
	if ( in_array( 'editor', $current_user->roles ) && get_option( 'de_disable_backend_editor' ) ) {
		return '';
	} else {
		return $link;
	}
}

function de_pro_set_locale( $locale ) {
	if ( ! is_admin() && ! ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_de_frontend' ) ) && De_Language_Wrapper::has_multilanguage() ) {
		return De_Language_Wrapper::get_current_locale();
	} else {
		return $locale;
	}
}

function de_pro_logout_home( $logouturl, $redir ) {
	$redir = home_url();
	return add_query_arg( 'redirect_to', urlencode( $redir ), $logouturl );
}

function de_pro_nav_menu_filter( $items, $args ) {
	global $direct_queried_object;
	global $post_type;

	// Remove drafts if needed
	if ( ! ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_de_frontend' ) ) || empty( $_SESSION[ 'de_show_all' ] ) ) {
		foreach ( $items as $key => $item ) {
			if ( $item->type == 'post_type' ) {
				$p = get_post( $item->object_id );
				if ( $p->post_status == 'draft' ) {
					unset( $items[ $key ] );
				}
			}
		}
		$items = array_values( $items );
	}

	// Translate menu to other language if needed
	$items = apply_filters( 'de_translate_menu_items', $items );

	$id = get_option( 'de_page_for_' . $post_type );
	if ( $id )
		$listPage = get_page( $id );
	else
		$listPage = null;
	
	$current = null;
	$itemsById = array();

	// Look for current menu item
	// Fill in $itemsById array
	foreach ( $items as $key => $item ) {
		if ( $item->current && $item->type != 'custom' ) {
			if ( empty( $_GET[ 'de_add' ] ) ) {
				$current = $items[ $key ];
			} else {
				if ( array_search( 'current-menu-item', $items[ $key ]->classes ) !== false ) {
					unset( $items[ $key ]->classes[ array_search( 'current-menu-item', $items[ $key ]->classes ) ] );
				}
			}
		}
		
		$itemsById[ $item->ID ] = $key;
	}

	// Set current menuitem
	if ( empty( $current ) ) {
		foreach ( $items as $key => $item ) { 
			// Current page
			if( $item->type == 'post_type' && $direct_queried_object && ( $item->object_id == $direct_queried_object->ID || de_is_language_post( $item->object_id, $direct_queried_object->ID ) ) && ! $item->current ) {
				$items[ $key ]->current = 1;
				$items[ $key ]->classes[] = 'current-menu-item';
				
				$current = $items[ $key ];
				
				break;
			}
			
			// Blog archive page
			if ( $item->type == 'post_type' && $post_type == 'post' && de_is_home( $item->object_id ) ) {
				$items[ $key ]->current = 1;
				$items[ $key ]->classes[] = 'current-menu-item';
				
				$current = $items[ $key ];
				
				break;
			}
			
			// dE post type archive page
			if( $item->type == 'post_type' && $listPage && ( $item->object_id == $listPage->ID || de_is_language_post( $item->object_id, $listPage->ID ) ) ) {
				$items[ $key ]->current = 1;
				$items[ $key ]->classes[] = 'current-menu-item';
				
				$current = $items[ $key ];
				
				break;
			}
			
			// Taxonomy page
			if ( $item->type == 'taxonomy' && $direct_queried_object && has_term( $item->object_id, $item->object, $direct_queried_object->ID ) ) {
				$items[ $key ]->current = 1;
				$items[ $key ]->classes[] = 'current-menu-item';
				
				$current = $items[ $key ];
				
				break;
			}
		}
		
		if ( is_object( $current ) && ! empty( $current->menu_item_parent ) ) {
			$parent = $items[ $itemsById[ $current->menu_item_parent ] ];
			$parent->current_item_ancestor = 1;
			$parent->current_item_parent = 1;
			$parent->classes[] = 'current-menu-ancestor';
			$parent->classes[] = 'current-menu-parent';
			while( $parent->menu_item_parent ) {
				$parent = $items[ $itemsById[ $parent->menu_item_parent ] ];
				$parent->current_item_ancestor = 1;
				$parent->classes[] = 'current-menu-ancestor';
			}
		}
	}

	if ( empty( $args->start_level ) )
		return $items;
	$startLevel = $args->start_level;
	
	// Look for the proper ancestor
	$i = 1;
	$parentId = 0;
	foreach ( $items as $key => $item ) {
		if ( $item->menu_item_parent == $parentId && ( $item->current_item_ancestor || $item->current ) ) {
			if ( $startLevel == $i ) {
				$cursor = $item->ID;
				break;
			}
			
			if ( $item->current_item )
				return array();
			
			$parentId = $item->ID;
			$i ++;
		}
	}

	if ( empty( $cursor ) )
		return array();

	$parents = array( $cursor );
	$out = array();
	while ( !empty( $parents ) ) {
		$newparents = array();

		foreach ( $items as $item ) {
			if ( in_array($item->menu_item_parent, $parents ) ) {
				if ($item->menu_item_parent == $cursor)
					$item->menu_item_parent = 0;
				$out[] = $item;
				$newparents[] = $item->ID;
			}
		}

		$parents = $newparents;
	}

	return $out;
}

function de_pro_footer_scripts() {
	global $de_global_options;
	global $direct_queried_object;

	if ( ! is_admin() ) {
		if ( is_object( $direct_queried_object ) && isset( $direct_queried_object->ID ) ) {
			// Direct Menu Editor
			if ( ( current_user_can( 'edit_theme_options' ) || current_user_can( 'edit_de_frontend' ) ) && get_option( 'de_menu_editor_enabled' ) && get_option( 'de_edit_menu_page' ) == $direct_queried_object->ID ) {
				?>
<script>
	jQuery(document).ready(function() {
		directEditMenu(<?php echo json_encode( De_Store::read_menus() ); ?>);
		jQuery('li#wp-admin-bar-menu-save a').directMenuSaveButton();
	});
</script>
				<?php
			}
			
			if ( ( current_user_can('edit_posts') || current_user_can( 'edit_de_frontend' ) ) ) {
					?>
<div class="direct-editable" id="direct-page-options" data-global-options="page-options" style="display: none;">
	<form>
		<?php if ( ! empty( $direct_queried_object->ID ) ) { ?>
		<input type="hidden" name="postId" id="postId" value="<?php echo $direct_queried_object->ID; ?>" />
		<?php } else { ?>
		<input type="hidden" name="postType" id="postType" value="<?php echo $direct_queried_object->post_type; ?>" />
		<?php } ?>
		<input type="hidden" name="templateName" id="templateName" value="<?php echo de_get_current_template(); ?>" />
		<div style="float:left; width:46%; padding:5px 2%;">
			<h5><?php _e( 'Title', 'direct-edit' ); ?></h5>
			<input type="text" name="de_title" id="de_title" value="<?php direct_bloginfo( 'title' ); ?>" />
			<h5><?php _e( 'Description', 'direct-edit' ); ?></h5>
			<textarea name="de_description" id="de_description"><?php direct_bloginfo( 'description' ); ?></textarea>
		</div>
		<?php if ( De_Language_Wrapper::has_multilanguage() || ! is_front_page() ) { ?>
		<div style="float:left; width:46%; padding:5px 2%;">
		<?php if( De_Language_Wrapper::has_multilanguage() ) { // It is needed for menu translation only ?>
			<h5><?php _e( 'Navigation label', 'direct-edit' ); ?></h5>
			<input type="text" name="de_navigation_label" id="de_navigation_label" value="<?php direct_bloginfo( 'navigation_label' ); ?>" />
		<?php
		}
		if ( get_option( 'de_smart_urls' ) && get_option( 'permalink_structure' ) == '/%postname%/' ) {
			if ( ! is_front_page() ) {
			?>
				<h5><?php _e( 'Slug', 'direct-edit' ); ?></h5>
				<input type="text" name="de_slug" id="de_slug" value="<?php direct_bloginfo( 'slug' ); ?>" />
			<?php
			}
		}
		?>
		</div>
		<div style="clear: both;"></div>
		<?php } ?>
		<?php do_action( 'de_add_page_options' ); ?>
		<div style="float:right;">
			<input class="btn" type="submit" value="<?php _e( 'Save', 'direct-edit' ); ?>" />
		</div>
		<div style="clear: both;"></div>
	</form>
</div>
<script>
	jQuery(document).ready(function() {
		jQuery('li#wp-admin-bar-page-options a').directOptionButton();
		<?php
		if ( get_post_meta( $direct_queried_object->ID, 'de_new_page', true ) ) {
			?>
			if ( confirm( '<?php _e( 'Do you want to show this page?' ); ?>' ) ) {
				location.href = '<?php echo add_query_arg( array( 'de_show' => 1 ), De_Url::get_url( $direct_queried_object->ID ) ); ?>';
			}
			<?php
			delete_post_meta( $direct_queried_object->ID, 'de_new_page' );
		}
		?>
	});
</script>
				<?php
			}
		}
	}
}
