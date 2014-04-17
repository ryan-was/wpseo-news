<?php

/*
Plugin Name: WordPress SEO News
Version: 2.0.0-beta1
Plugin URI: http://yoast.com/wordpress/seo/news/#utm_source=wpadmin&utm_medium=plugin&utm_campaign=wpseonewsplugin
Description: Google News plugin for the WordPress SEO plugin
Author: Joost de Valk
Author URI: http://yoast.com/
License: GPL v3

WordPress SEO Plugin
Copyright (C) 2008-2014, Joost de Valk - joost@yoast.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


class WPSEO_News {

	const VERSION = '2.0.0-beta1';

	/**
	 * Get WPSEO News options
	 *
	 * @return array
	 */
	public static function get_options() {
		return apply_filters( 'wpseo_news_options', wp_parse_args( get_option( 'wpseo_news', array() ), array( 'newssitemapname' => '', 'newssitemap_default_genre' => array(), 'newssitemap_default_keywords' => '', 'dbversion' => '0' ) ) );
	}

	/**
	 * Get plugin file
	 *
	 * @return string
	 */
	public static function get_file() {
		return __FILE__;
	}

	public function __construct() {

		// Setup autoloader
		require_once( dirname( __FILE__ ) . '/classes/class-autoloader.php' );
		$autoloader = new WPSEO_News_Autoloader();
		spl_autoload_register( array( $autoloader, 'load' ) );

		// Add plugin links
		add_filter( 'plugin_action_links', array( $this, 'plugin_links' ), 10, 2 );

		// Add subitem to menu
		add_filter( 'wpseo_submenu_pages', array( $this, 'add_submenu_pages' ) );

		// Add Redirect page as admin page
		add_filter( 'wpseo_admin_pages', array( $this, 'add_admin_pages' ) );

		// Register settings
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// Meta box
		$meta_box = new WPSEO_News_Meta_Box();
		add_filter( 'wpseo_save_metaboxes', array( $meta_box, 'save' ), 10, 1 );
		add_action( 'wpseo_tab_header', array( $meta_box, 'header' ) );
		add_action( 'wpseo_tab_content', array( $meta_box, 'content' ) );

		// Sitemap
		$sitemap = new WPSEO_News_Sitemap();
		add_action( 'init', array( $sitemap, 'init' ), 10 );
		add_filter( 'wpseo_sitemap_index', array( $sitemap, 'add_to_index' ) );

		// Rewrite Rules
		$rewrite_rules = new WPSEO_News_Editors_Pick_Request();
		$rewrite_rules->setup();

		// Head
		$head = new WPSEO_News_Head();
		add_action( 'wpseo_head', array( $head, 'add_head_tags' ) );

		if ( is_admin() ) {

			// Edit Post JS
			global $pagenow;
			if ( 'post.php' == $pagenow || 'post-new.php' == $pagenow ) {
				add_action( 'admin_head', array( $this, 'edit_post_css' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_edit_post' ) );
			}
			//

			// Upgrade Manager
			$upgrade_manager = new WPSEO_News_Upgrade_Manager();
			$upgrade_manager->check_update();

			// License Manager
			$license_manager = new Yoast_Plugin_License_Manager( new WPSEO_News_Product() );
			$license_manager->setup_hooks();
			add_action( 'wpseo_licenses_forms', array( $license_manager, 'show_license_form' ) );
		}

	}

	/**
	 * Add plugin links
	 *
	 * @param $links
	 * @param $file
	 *
	 * @return mixed
	 */
	public function plugin_links( $links, $file ) {
		static $this_plugin;
		if ( empty( $this_plugin ) ) {
			$this_plugin = plugin_basename( __FILE__ );
		}
		if ( $file == $this_plugin ) {
			$settings_link = '<a href="' . admin_url( 'admin.php?page=wpseo_news' ) . '">' . __( 'Settings', 'wordpress-seo-news' ) . '</a>';
			array_unshift( $links, $settings_link );
		}

		return $links;
	}

	/**
	 * Register the premium settings
	 */
	public function register_settings() {
		register_setting( 'yoast_wpseo_news_options', 'wpseo_news' );
	}

	/**
	 * Add submenu item
	 *
	 * @param $submenu_pages
	 *
	 * @return array
	 */
	public function add_submenu_pages( $submenu_pages ) {

		$admin_page = new WPSEO_News_Admin_Page();

		$submenu_pages[] = array(
				'wpseo_dashboard',
				__( 'Yoast WordPress SEO:', 'wordpress-seo-news' ) . ' ' . __( 'News SEO', 'wordpress-seo-news' ),
				__( 'News SEO', 'wordpress-seo-news' ),
				'manage_options',
				'wpseo_news',
				array( $admin_page, 'display' ),
				array( array( $this, 'enqueue_admin_page' ) )
		);

		return $submenu_pages;
	}

	/**
	 * Add admin page to admin_pages so the correct assets are loaded by WPSEO
	 *
	 * @param $admin_pages
	 *
	 * @return array
	 */
	public function add_admin_pages( $admin_pages ) {
		$admin_pages[] = 'wpseo_news';

		return $admin_pages;
	}

	/**
	 * Enqueue admin page JS
	 */
	public function enqueue_admin_page() {
		wp_enqueue_media(); // enqueue files needed for upload functionality
		wp_enqueue_script( 'wpseo-news-admin-page', plugins_url( 'assets/admin-page.js', self::get_file() ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-autocomplete' ), self::VERSION, true );
		wp_localize_script( 'wpseo-news-admin-page', 'wpseonews', WPSEO_News_Javascript_Strings::strings() );
	}

	/**
	 * Enqueue edit post JS
	 */
	public function enqueue_edit_post() {
		wp_enqueue_script( 'wpseo-news-edit-post', plugins_url( 'assets/post-edit.js', self::get_file() ), array( 'jquery' ), self::VERSION, true );
	}

	/**
	 * Print the edit post CSS
	 */
	public function edit_post_css() {
		echo "<style type='text/css'>.wpseo-news-input-error{border:1px solid #ff0000 !important;}</style>" . PHP_EOL;
	}

}

/**
 * WPSEO News __main method
 */
function __wpseo_news_main() {
	new WPSEO_News();
}

// Load WPSEO News
add_action( 'plugins_loaded', '__wpseo_news_main', 14 );