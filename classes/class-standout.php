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

			}
		}

	}

	/**
	 * Get the postypes for the posts which could have a standout tag
	 *
	 * @return string
	 */
	private function get_post_types() {
		return implode( "','", get_post_types( array( 'public' => true ), 'objects' ) );
	}

	/**
	 * Get the posts with a standout tag enabled
	 *
	 * @return mixed
	 */
	private function get_standout_posts() {
		global $wpdb;
		$post_types = $this->get_post_types();

		$posts = $wpdb->get_results( "SELECT ID, post_content, post_name, post_author, post_parent, post_date_gmt, post_date, post_date_gmt, post_title, post_type
									FROM $wpdb->posts
									WHERE post_status='publish'
									AND post_type IN ($post_types)
									ORDER BY post_date_gmt DESC
									LIMIT 0, 1000" );

		return $posts;
	}

}