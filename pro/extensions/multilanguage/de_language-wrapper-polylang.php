<?php
// Polylang plugin connector

class De_Language_Wrapper {
	public static function has_multilanguage() {
		if ( count( self::get_languages() ) ) {
			return true;
		} else {
			return false;
		}
	}

	public static function get_languages() {
		global $polylang;

		$result = array();

		if ( is_array( unserialize( get_option( 'de_options_show_languages' ) ) ) )
			$show_languages = unserialize( get_option( 'de_options_show_languages' ) );
		else
			$show_languages = array();

		foreach ( $polylang->model->get_languages_list() as $language ) {
			if ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_de_frontend' ) || in_array( $language->slug, $show_languages ) ) {
				$result[] = $language->slug;
			}
		}

		return $result;
	}

	public static function get_default_language() {
		$options = get_option('polylang');
		return $options[ 'default_lang' ];
	}

	public static function set_current_language( $lang ) {
		global $polylang;
		global $l10n;

		$polylang->curlang = $polylang->model->get_language( $lang );
		do_action( 'pll_language_defined', $polylang->curlang->slug, $polylang->curlang );
	}

	public static function get_current_language() {
		global $polylang;

		return pll_current_language();
	}

	public static function get_current_locale() {
		global $polylang;

		return pll_current_language( 'locale' );
	}

	public static function set_post_language( $post_id, $lang ) {
		global $polylang;

		$polylang->model->set_post_language( $post_id, $lang );
	}

	public static function get_post_language( $post_id ) {
		global $polylang;

		if ( PLL()->model->post->get_language( $post_id ) )
			return PLL()->model->post->get_language( $post_id )->slug;
		else
			return '';
	}

	public static function create_language_posts( $post_id ) {
		global $polylang;
		global $user_ID;

		$post = get_post( $post_id );

		if ( $post ) {
			$post_type = get_post_type_object( $post->post_type );

			// If the post has no language, then we set default language
			if ( ! self::get_post_language( $post_id ) ) {
				self::set_post_language( $post_id, self::get_default_language() );
			}

			$translations = array();
			foreach( self::get_languages() as $lang ) {
				if ( $lang == self::get_post_language( $post_id ) ) {
					$translations[ $lang ] = $post_id;
					continue;
				}

				$lang_post = self::get_language_post( $post_id, $lang );
				if ( $lang_post ) {
					// Language post exists
					$translations[ $lang ] = $lang_post->ID;
				} else {
					// Create new language post
					$lang_post_title = $post->post_title . ' (' . $lang . ')';
					$lang_post = array(
						'post_content' => '',
						'post_title' => $lang_post_title,
						'post_name' => sanitize_title( $lang_post_title ),
						'post_status' => $post->post_status,
						'post_date' => current_time( 'mysql' ),
						'post_author' => $user_ID,
						'post_type' => $post->post_type,
						'post_category' => array( 0 )
					);
					$lang_post_id = wp_insert_post( $lang_post );

					De_Language_Wrapper::set_post_language( $lang_post_id, $lang );
					$translations[ $lang ] = $lang_post_id;

					De_Url::register_url( $lang_post_id, sanitize_title( $lang_post_title ) );

					update_post_meta( $lang_post_id, 'de_title_not_translated', 1 );
				}
			}

			// Update translations
			pll_save_post_translations( $translations );
		}
	}

	public static function get_language_post( $post_id, $lang, $get_all = true ) {
		$lang_post_id = pll_get_post( $post_id, $lang );

		if ( $lang_post_id && ( ! de_is_hidden( $lang_post_id ) || ( ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_de_frontend' ) ) && $get_all ) ) ) {
			return get_post( $lang_post_id );
		} else {
			return null;
		}
	}

	public static function get_language_posts( $post_id, $get_all = true ) {
		if ( self::get_post_language( $post_id ) ) {
			$lang_posts = array();

			foreach ( self::get_languages() as $lang ) {
				$lang_post_id = pll_get_post( $post_id, $lang );
				if ( $lang_post_id && get_post( $lang_post_id ) && ( ! de_is_hidden( $lang_post_id ) || ( ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_de_frontend' ) ) && $get_all ) ) ) {
					$lang_posts[ $lang ] = get_post( $lang_post_id );
				}
			}

			return $lang_posts;
		} else {
			return array();
		}
	}

	public static function get_language_name( $lang ) {
		global $polylang;

		return $polylang->model->get_language( $lang )->name;
	}

	public static function register_translation( $string, $name = '' ) {
		pll_register_string( ( $name ? $name : $string ), $string, 'direct-edit' );
	}

	public static function translate_string( $string, $echo = false ) {
		if ( $echo ) {
			pll_e( $string );
		} else {
			return pll__( $string );
		}
	}

	public static function on_language_add() {
		global $wpdb;

		set_time_limit( 300 );

		$o = get_option( 'polylang' );
		$post_types = array( "'post'", "'page'" );
		foreach( $o[ 'post_types' ] as $p ) {
			if ( post_type_exists( $p ) ) {
				$post_types[] = "'$p'";
			}
		}

		$querystr = "
			SELECT wposts.*
			FROM $wpdb->posts wposts
			WHERE (wposts.post_status = 'publish' OR wposts.post_status = 'draft')
			AND wposts.post_type IN (" . implode( ', ', $post_types ) . ")
		";

		$items = $wpdb->get_results( $querystr, OBJECT );

		foreach( $items as $item ) {
			if ( self::get_post_language( $item->ID ) != self::get_default_language() )
				continue;

			self::create_language_posts( $item->ID );
		}
	}

	public static function translate_menu_items( $items ) {
		global $polylang;

		$items_filtered = array();

		if ( is_array( unserialize( get_option( 'de_options_show_languages' ) ) ) )
			$show_languages = unserialize( get_option( 'de_options_show_languages' ) );
		else
			$show_languages = array();

		foreach( $items as $i => $item ) {
			// Language switcher does not need translation
			if ( $item->type == 'custom' && is_array( $item->classes ) && in_array( 'lang-item', $item->classes ) ) {
				if ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_de_frontend' ) || in_array( $item->lang, $show_languages ) ) {
					$items_filtered[ $i ] = $item;
					$items_filtered[ $i ]->classes[] = 'language';
				}

				continue;
			}

			// Category
			if ( $item->type == 'taxonomy' ) {
				$items_filtered[ $i ] = $item;

				if ( pll_get_term( $item->object_id, De_Language_Wrapper::get_current_language() ) != $item->object_id ) {
					$term = get_term( pll_get_term( $item->object_id, De_Language_Wrapper::get_current_language() ), $item->object );

					$items_filtered[ $i ]->url = get_term_link( pll_get_term( $item->object_id, De_Language_Wrapper::get_current_language() ), $item->object );
					$items_filtered[ $i ]->title = $term->name;
					$items_filtered[ $i ]->object_id = $term->term_id;
				}

				continue;
			}

			// Post
			if ( $item->type == 'post_type' ) {
				$items_filtered[ $i ] = $item;

				if ( De_Language_Wrapper::get_post_language( $item->object_id ) && De_Language_Wrapper::get_post_language( $item->object_id ) != De_Language_Wrapper::get_current_language() && De_Language_Wrapper::get_language_post( $item->object_id, De_Language_Wrapper::get_current_language() ) ) {
					$lang_post = De_Language_Wrapper::get_language_post( $item->object_id, De_Language_Wrapper::get_current_language() );
					$items_filtered[ $i ]->post_title = direct_bloginfo( 'navigation_label', false, $lang_post->ID );
					$items_filtered[ $i ]->url = get_permalink( $lang_post->ID );
					$items_filtered[ $i ]->title = direct_bloginfo( 'navigation_label', false, $lang_post->ID );
					$items_filtered[ $i ]->object_id = $lang_post->ID;
				} else {
					$items_filtered[ $i ]->post_title = direct_bloginfo( 'navigation_label', false, $item->object_id );
					$items_filtered[ $i ]->title = direct_bloginfo( 'navigation_label', false, $item->object_id );
				}

				continue;
			}

			$items_filtered[ $i ] = $item;
		}

		return array_values( $items_filtered );
	}

	public static function de_post_type_add( $name ) {
		$options = get_option( 'polylang' );

		if ( ! is_array( $options[ 'post_types' ] ) || array_search( $name, $options[ 'post_types' ] ) === false ) {
			$options[ 'post_types' ][] = $name;
			update_option( 'polylang', $options );
		}
	}

	public static function de_post_type_delete( $name ) {
		$options = get_option( 'polylang' );

		if ( ! is_array( $options[ 'post_types' ] ) || array_search( $name, $options[ 'post_types' ] ) !== false ) {
			unset( $options[ 'post_types' ][ array_search( $name, $options[ 'post_types' ] ) ] );
			update_option( 'polylang', $options );
		}
	}
}

add_filter( 'de_get_de_posts', 'de_add_language_query_arg' );
add_filter( 'de_translate_menu_items', 'de_translate_menu_items' );
add_filter( 'pll_check_canonical_url', 'de_polylang_prevent_redirects' );
add_filter( 'wp_redirect', 'de_on_language_add' );

function de_add_language_query_arg( $request ) {
	$request[ 'lang' ] = De_Language_Wrapper::get_current_language();
	return $request;
}

function de_translate_menu_items( $items ) {
	$items = De_Language_Wrapper::translate_menu_items( $items );

	return $items;
}

function de_polylang_prevent_redirects( $redirect_url ) {
	if ( get_query_var( 'p' ) ) {
		return $redirect_url;
	} else {
		return false;
	}
}

function de_on_language_add( $string ) {
	if ( isset( $_REQUEST[ 'page' ] ) && $_REQUEST[ 'page' ] == 'mlang' && ( isset( $_REQUEST[ 'action' ] ) && $_REQUEST[ 'action' ] == 'add' || isset( $_REQUEST[ 'pll_action' ] ) && $_REQUEST[ 'pll_action' ] == 'add' ) && $_SERVER[ 'REQUEST_METHOD' ] == 'POST' ) {
		De_Language_Wrapper::on_language_add();
	}

	return $string;
}
