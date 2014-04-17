<?php

class WPSEO_News_Sitemap_Editors_Pick {

	private $items;

	public function __construct() {
		$this->prepare_items();
	}

	/**
	 * Prepare RSS feed data
	 */
	private function prepare_items() {
		$this->items = array();

		// EP Query
		$ep_query = new WP_Query(
				array(
						'post_type'   => 'any',
						'post_status' => 'publish',
						'meta_query'  => array(
								array(
										'key'   => '_yoast_wpseo_newssitemap-editors-pick',
										'value' => 'on'
								)
						),
						'order'       => 'DESC',
						'orderby'     => 'modified'
				)
		);

		// The Loop
		if ( $ep_query->have_posts() ) {
			while ( $ep_query->have_posts() ) {
				$ep_query->the_post();
				$this->items[] = array(
						'title'       => get_the_title(),
						'link'        => get_permalink(),
						'description' => get_the_excerpt(),
						'creator'     => get_the_author_meta( 'display_name' )
				);
			}
		}

		/* Restore original Post Data */
		wp_reset_postdata();

	}

	/**
	 * Generate the Editors' Pick URL
	 */
	public function generate_rss() {

		$options = WPSEO_News::get_options();

		echo '<?xml version="1.0" encoding="UTF-8" ?>' . PHP_EOL;
		echo '<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">' . PHP_EOL;
		echo '<channel>' . PHP_EOL;

		// Display the main channel tags
		echo '<link>' . get_site_url() . '</link>' . PHP_EOL;
		echo '<description>' . get_bloginfo( 'description' ) . '</description>' . PHP_EOL;
		echo '<title>' . get_bloginfo( 'name' ) . '</title>' . PHP_EOL;

		// Display the image tag if an image is set
		if ( isset( $options['newssitemap_ep_image'] ) && $options['newssitemap_ep_image'] != '' ) {
			echo '<image>' . PHP_EOL;
			echo '<url>' . $options['newssitemap_ep_image'] . '</url>' . PHP_EOL;

			// Display the image title tag if an image is set
			if ( isset( $options['newssitemap_ep_image_title'] ) && $options['newssitemap_ep_image_title'] != '' ) {
				echo '<title>' . $options['newssitemap_ep_image_title'] . '</title>' . PHP_EOL;
			}
			echo '<link>' . get_site_url() . '</link>' . PHP_EOL;
			echo '</image>' . PHP_EOL;
		}

		// Display the items
		if ( count( $this->items ) > 0 ) {
			foreach ( $this->items as $item ) {
				echo '<item>' . PHP_EOL;
				echo '<title>' . $item['title'] . '</title>' . PHP_EOL;
				echo '<link>' . $item['link'] . '</link>' . PHP_EOL;
				echo '<description>' . $item['description'] . '</description>' . PHP_EOL;
				echo '<dc:creator>' . $item['creator'] . '</dc:creator>' . PHP_EOL;
				echo '</item>' . PHP_EOL;
			}
		}


		echo '</channel>' . PHP_EOL;
		echo '</rss>' . PHP_EOL;

	}

}