<?php

class WPSEO_News_Standout {

	private $options;

	public function __construct() {
		$this->options = WPSEO_News::get_options();
	}

	/**
	 * Run this function to remove -old- standout tags from posts
	 */
	public function remove_old_standouts() {
		$old_posts = $this->get_standout_posts();

		if ( is_array( $old_posts ) && count( $old_posts ) >= 1 ) {
			foreach ( $old_posts as $post ) {
				//echo 'Standout - ' . WPSEO_Meta::get_value( 'sitemap-standout', $post->ID );
			}
		}

	}

	/**
	 * Get the postypes for the posts which could have a standout tag
	 *
	 * @return string
	 */
	private function get_post_types() {
		$types      = array();
		$post_types = get_post_types( array( 'public' => true ), 'objects' );

		foreach ( $post_types as $posttype ) {
			$types[] = $posttype->name;
		}

		return implode( "','", $types );
	}

	/**
	 * Get the posts with a standout tag enabled
	 *
	 * @return mixed
	 */
	private function get_standout_posts() {
		global $wpdb;
		$post_types = $this->get_post_types();

		$posts = $wpdb->get_results( "SELECT ID
									FROM $wpdb->posts
									WHERE post_status='publish'
									AND post_type IN ($post_types)
									ORDER BY post_date_gmt DESC
									LIMIT 0, 1000" );

		return $posts;
	}

}