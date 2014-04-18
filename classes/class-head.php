<?php

class WPSEO_News_Head {

	/**
	 * WPSEO_News_Head Constructor
	 */
	public function __construct() {
		do_action( 'wpseo_news_head' );
	}

	/**
	 * Display the optional sources link elements in the <code>&lt;head&gt;</code>.
	 */
	public function add_head_tags() {

		if ( is_singular() ) {
			global $post;

			if ( apply_filters( 'wpseo_news_head_display_keywords', true ) ) {
				$meta_news_keywords = trim( WPSEO_Meta::get_value( 'newssitemap-keywords', $post->ID ) );
				if ( ! empty( $meta_news_keywords ) ) {
					echo '<meta name="news_keywords" content="' . $meta_news_keywords . '" />' . "\n";
				}
			}

			if ( apply_filters( 'wpseo_news_head_display_original', true ) ) {
				$original_source = trim( WPSEO_Meta::get_value( 'newssitemap-original', $post->ID ) );
				if ( empty( $original_source ) ) {
					echo '<link rel="original-source" href="' . get_permalink( $post->ID ) . '" />' . "\n";
				} else {
					$sources = explode( '|', $original_source );
					foreach ( $sources as $source ) {
						echo '<link rel="original-source" href="' . $source . '" />' . "\n";
					}
				}
			}

			// Standout tag
			if ( apply_filters( 'wpseo_news_head_display_standout', true ) ) {
				$meta_standout = WPSEO_Meta::get_value( 'newssitemap-standout', $post->ID );
				if ( 'on' == $meta_standout ) {
					echo '<meta name="standout" content="' . get_permalink( $post->ID ) . '"/>' . "\n";
				}
			}

		}

	}

}