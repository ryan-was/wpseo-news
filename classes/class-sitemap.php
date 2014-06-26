<?php

class WPSEO_News_Sitemap {

	private $options;

	public function __construct() {
		$this->options = WPSEO_News::get_options();
	}

	/**
	 * Get attachment
	 *
	 * @param $attachment_id
	 *
	 * @return array
	 */
	private function get_attachment( $attachment_id ) {
		// Get attachment
		$attachment = get_post( $attachment_id );

		// Check if we've found an attachment
		if ( null == $attachment ) {
			return array();
		}

		// Return props
		return array(
				'alt'         => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
				'caption'     => $attachment->post_excerpt,
				'description' => $attachment->post_content,
				'href'        => get_permalink( $attachment->ID ),
				'src'         => $attachment->guid,
				'title'       => $attachment->post_title
		);
	}

	/**
	 * Register the XML News sitemap with the main sitemap class.
	 */
	public function init() {
		if ( isset( $GLOBALS['wpseo_sitemaps'] ) ) {
			$GLOBALS['wpseo_sitemaps']->register_sitemap( 'news', array( $this, 'build' ) );
		}
	}

	/**
	 * Add the XML News Sitemap to the Sitemap Index.
	 *
	 * @param string $str String with Index sitemap content.
	 *
	 * @return string
	 */
	function add_to_index( $str ) {

		$date = new DateTime( get_lastpostdate( 'gmt' ), new DateTimeZone( $this->wp_get_timezone_string() ) );

		$str .= '<sitemap>' . "\n";
		$str .= '<loc>' . home_url( 'news-sitemap.xml' ) . '</loc>' . "\n";
		$str .= '<lastmod>' . htmlspecialchars( $date->format( 'c' ) ) . '</lastmod>' . "\n";
		$str .= '</sitemap>' . "\n";

		return $str;
	}

	/**
	 * Returns the timezone string for a site, even if it's set to a UTC offset
	 *
	 * Adapted from http://www.php.net/manual/en/function.timezone-name-from-abbr.php#89155
	 *
	 * @return string valid PHP timezone string
	 */
	private function wp_get_timezone_string() {

		// if site timezone string exists, return it
		if ( $timezone = get_option( 'timezone_string' ) )
			return $timezone;

		// get UTC offset, if it isn't set then return UTC
		if ( 0 === ( $utc_offset = get_option( 'gmt_offset', 0 ) ) )
			return 'UTC';

		// adjust UTC offset from hours to seconds
		$utc_offset *= 3600;

		// attempt to guess the timezone string from the UTC offset
		$timezone = timezone_name_from_abbr( '', $utc_offset );

		// last try, guess timezone string manually
		if ( false === $timezone ) {

			$is_dst = date( 'I' );

			foreach ( timezone_abbreviations_list() as $abbr ) {
				foreach ( $abbr as $city ) {
					if ( $city['dst'] == $is_dst && $city['offset'] == $utc_offset )
						return $city['timezone_id'];
				}
			}
		}

		// fallback to UTC
		return 'UTC';
	}

	/**
	 * Build the sitemap and push it to the XML Sitemaps Class instance for display.
	 */
	public function build() {
		global $wpdb;

		// Get supported post types
		$post_types = array();
		foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $posttype ) {
			if ( isset( $this->options['newssitemap_include_' . $posttype->name] ) && ( 'on' == $this->options['newssitemap_include_' . $posttype->name] ) ) {
				$post_types[] = $posttype->name;
			}
		}

		if ( count( $post_types ) > 0 ) {
			$post_types = "'" . implode( "','", $post_types ) . "'";
		} else {
			$post_types = "'post'";
		}

		// Get posts for the last two days only, credit to Alex Moss for this code.
		$items = $wpdb->get_results( "SELECT ID, post_content, post_name, post_author, post_parent, post_date_gmt, post_date, post_date_gmt, post_title, post_type
									FROM $wpdb->posts
									WHERE post_status='publish'
									AND (DATEDIFF(CURDATE(), post_date_gmt)<=2)
									AND post_type IN ($post_types)
									ORDER BY post_date_gmt DESC
									LIMIT 0, 1000" );

		$output = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
		xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"
		xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

//		echo '<!--' . print_r( $items, 1 ) . '-->';


		if ( ! empty( $items ) ) {
			foreach ( $items as $item ) {
				$item->post_status = 'publish';

				if ( WPSEO_Meta::get_value( 'newssitemap-exclude', $item->ID ) == 'on' ) {
					continue;
				}

				if ( false != WPSEO_Meta::get_value( 'meta-robots', $item->ID ) && strpos( WPSEO_Meta::get_value( 'meta-robots', $item->ID ), 'noindex' ) !== false ) {
					continue;
				}

				if ( 'post' == $item->post_type ) {

					$cats    = get_the_terms( $item->ID, 'category' );
					$exclude = 0;

					foreach ( $cats as $cat ) {
						if ( isset( $this->options['catexclude_' . $cat->slug] ) ) {
							$exclude ++;
						}
					}

					if ( $exclude >= count( $cats ) ) {
						continue;
					}

				}

				$publication_name = ! empty( $this->options['name'] ) ? $this->options['name'] : get_bloginfo( 'name' );
				$locale = apply_filters( 'wpseo_locale', get_locale() );

				// fallback to 'en', if the length of the locale is less than 2 characters
				if ( strlen( $locale ) < 2 ) {
					$locale = 'en';
				}

				$publication_lang = substr( $locale, 0, 2 );

				$keywords = explode( ',', trim( WPSEO_Meta::get_value( 'newssitemap-keywords', $item->ID ) ) );
				$tags     = get_the_terms( $item->ID, 'post_tag' );
				if ( $tags ) {
					foreach ( $tags as $tag ) {
						$keywords[] = $tag->name;
					}
				}

				// TODO: add suggested keywords to each post based on category, next to the entire site
				if ( isset( $this->options['default_keywords'] ) && $this->options['default_keywords'] != '' ) {
					$keywords = array_merge( $keywords, explode( ',', $this->options['default_keywords'] ) );
				}
				$keywords = strtolower( trim( implode( ', ', $keywords ), ', ' ) );

				$genre = WPSEO_Meta::get_value( 'newssitemap-genre', $item->ID );
				if ( is_array( $genre ) ) {
					$genre = implode( ',', $genre );
				}

				if ( $genre == '' && isset( $this->options['default_genre'] ) && $this->options['default_genre'] != '' ) {
					$genre = $this->options['default_genre'];
				}
				$genre = trim( preg_replace( '/^none,?/', '', $genre ) );

				$stock_tickers = trim( WPSEO_Meta::get_value( 'newssitemap-stocktickers' ) );
				if ( $stock_tickers != '' ) {
					$stock_tickers = "\t\t<stock_tickers>" . htmlspecialchars( $stock_tickers ) . '</stock_tickers>' . "\n";
				}

				$output .= '<url>' . "\n";
				$output .= "\t<loc>" . get_permalink( $item ) . '</loc>' . "\n";
				$output .= "\t<news:news>\n";
				$output .= "\t\t<news:publication>" . "\n";
				$output .= "\t\t\t<news:name>" . htmlspecialchars( $publication_name ) . '</news:name>' . "\n";
				$output .= "\t\t\t<news:language>" . htmlspecialchars( $publication_lang ) . '</news:language>' . "\n";
				$output .= "\t\t</news:publication>\n";

				if ( ! empty( $genre ) ) {
					$output .= "\t\t<news:genres>" . htmlspecialchars( $genre ) . '</news:genres>' . "\n";
				}

				// Create a DateTime object date in the correct timezone
				$datetime = new DateTime( $item->post_date_gmt, new DateTimeZone( $this->wp_get_timezone_string() ) );

				$output .= "\t\t<news:publication_date>" . $datetime->format( 'c' ) . '</news:publication_date>' . "\n";
				$output .= "\t\t<news:title>" . htmlspecialchars( $item->post_title ) . '</news:title>' . "\n";

				if ( ! empty( $keywords ) ) {
					$output .= "\t\t<news:keywords>" . htmlspecialchars( $keywords ) . '</news:keywords>' . "\n";
				}

				$output .= $stock_tickers;
				$output .= "\t</news:news>\n";

				$images = array();
				if ( preg_match_all( '/<img [^>]+>/', $item->post_content, $matches ) ) {
					foreach ( $matches[0] as $img ) {
						if ( preg_match( '/src=("|\')([^"|\']+)("|\')/', $img, $match ) ) {
							$src = $match[2];
							if ( strpos( $src, 'http' ) !== 0 ) {

								if ( $src[0] != '/' ) {
									continue;
								}

								$src = get_bloginfo( 'url' ) . $src;
							}

							if ( $src != esc_url( $src ) ) {
								continue;
							}

							if ( isset( $url['images'][$src] ) ) {
								continue;
							}

							$image = array();
							if ( preg_match( '/title=("|\')([^"\']+)("|\')/', $img, $match ) ) {
								$image['title'] = str_replace( array( '-', '_' ), ' ', $match[2] );
							}

							if ( preg_match( '/alt=("|\')([^"\']+)("|\')/', $img, $match ) ) {
								$image['alt'] = str_replace( array( '-', '_' ), ' ', $match[2] );
							}

							$images[$src] = $image;
						}
					}
				}

				// Also check if the featured image value is set.
				$post_thumbnail_id = get_post_thumbnail_id( $item->ID );

				if ( '' != $post_thumbnail_id ) {

					$attachment = $this->get_attachment( $post_thumbnail_id );

					if ( count( $attachment ) > 0 ) {

						$image = array();

						if ( '' != $attachment['title'] ) {
							$image['title'] = $attachment['title'];
						}

						if ( '' != $attachment['alt'] ) {
							$image['alt'] = $attachment['alt'];
						}

						$images[$attachment['src']] = $image;

					}

				}

				if ( isset( $images ) && count( $images ) > 0 ) {
					foreach ( $images as $src => $img ) {

						/**
						 * Filter: 'wpseo_xml_sitemap_img_src' - Allow changing of sitemap image src
						 *
						 * @api string $src The image source
						 *
						 * @param object $item The post item
						 */
						$src = apply_filters( 'wpseo_xml_sitemap_img_src', $src, $item );

						$output .= "\t\t<image:image>\n";
						$output .= "\t\t\t<image:loc>" . htmlspecialchars( $src ) . "</image:loc>\n";

						if ( isset( $img['title'] ) ) {
							$output .= "\t\t\t<image:title>" . htmlspecialchars( $img['title'] ) . "</image:title>\n";
						}

						if ( isset( $img['alt'] ) ) {
							$output .= "\t\t\t<image:caption>" . htmlspecialchars( $img['alt'] ) . "</image:caption>\n";
						}

						$output .= "\t\t</image:image>\n";
					}
				}

				$output .= '</url>' . "\n";
			}
		}

		$output .= '</urlset>';
		$GLOBALS['wpseo_sitemaps']->set_sitemap( $output );
		$GLOBALS['wpseo_sitemaps']->set_stylesheet( '<?xml-stylesheet type="text/xsl" href="' . plugin_dir_url( WPSEO_News::get_file() ) . 'assets/xml-news-sitemap.xsl"?>' );
	}

}