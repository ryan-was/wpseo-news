<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class WPSEO_News_Admin_Page {

	/**
	 * Display admin page
	 */
	public function display() {
		global $wpseo_admin_pages;

		// Load options
		$options = WPSEO_News::get_options();

		// Admin header
		$wpseo_admin_pages->admin_header( true, 'yoast_wpseo_news_options', 'wpseo_news' );

		// Introducten
		echo '<p>' . __( 'You will generally only need XML News sitemap when your website is included in Google News. If it is, check the box below to enable the XML News Sitemap functionality.', 'wordpress-seo' ) . '</p>';

		// Google News Publication Name
		echo $wpseo_admin_pages->textinput( 'newssitemapname', __( 'Google News Publication Name', 'wordpress-seo' ) );

		// Default Genre
		echo $wpseo_admin_pages->select( 'newssitemap_default_genre', __( 'Default Genre', 'wordpress-seo' ),
				array(
						"none"          => __( "None", 'yoast-wpseo' ),
						"pressrelease"  => __( "Press Release", 'yoast-wpseo' ),
						"satire"        => __( "Satire", 'yoast-wpseo' ),
						"blog"          => __( "Blog", 'yoast-wpseo' ),
						"oped"          => __( "Op-Ed", 'yoast-wpseo' ),
						"opinion"       => __( "Opinion", 'yoast-wpseo' ),
						"usergenerated" => __( "User Generated", 'yoast-wpseo' ),
				) );

		// Default keywords
		echo $wpseo_admin_pages->textinput( 'newssitemap_default_keywords', __( 'Default Keywords', 'wordpress-seo' ) );
		echo '<p>' . __( 'It might be wise to add some of Google\'s suggested keywords to all of your posts, add them as a comma separated list. Find the list here:', 'wordpress-seo' ) . ' ' . make_clickable( 'http://www.google.com/support/news_pub/bin/answer.py?answer=116037' ) . '</p>';

		// Post Types to include in News Sitemap
		echo '<h3>' . __( 'Post Types to include in News Sitemap', 'wordpress-seo' ) . '</h3>';
		foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $posttype ) {
			echo $wpseo_admin_pages->checkbox( 'newssitemap_include_' . $posttype->name, $posttype->labels->name, false );
		}

		// Post categories to exclude
		if ( isset( $options['newssitemap_include_post'] ) ) {
			echo '<h3>' . __( 'Post categories to exclude', 'wordpress-seo' ) . '</h3>';
			foreach ( get_categories() as $cat ) {
				echo $wpseo_admin_pages->checkbox( 'catexclude_' . $cat->slug, $cat->name . ' (' . $cat->count . ' posts)', false );
			}
		}

		// Admin footer
		$wpseo_admin_pages->admin_footer( true, false );

	}

}