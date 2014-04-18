<?php

class WPSEO_News_Meta_Box extends WPSEO_Metabox {

	private $options;

	public function __construct() {
		$this->options = WPSEO_News::get_options();
	}

	/**
	 * The metaboxes to display and save for the tab
	 *
	 * @return array $mbs
	 */
	public function get_meta_boxes( $post_type = 'post' ) {
		$mbs                             = array();
		$stdgenre                        = ( isset( $this->options['default_genre'] ) ) ? $this->options['default_genre'] : 'blog';
		$mbs['newssitemap-exclude']      = array(
				"name"  => "newssitemap-exclude",
				"type"  => "checkbox",
				"std"   => "on",
				"title" => __( "Exclude from News Sitemap", 'wordpress-seo-news' )
		);
		$mbs['newssitemap-keywords']     = array(
				"name"        => "newssitemap-keywords",
				"type"        => "text",
				"std"         => "",
				"title"       => __( "Meta News Keywords", 'wordpress-seo-news' ),
				"description" => __( "Comma separated list of the keywords this article aims at, use a maximum of 10 keywords.", "wordpress-seo-news" ),
		);
		$mbs['newssitemap-genre']        = array(
				"name"        => "newssitemap-genre",
				"type"        => "multiselect",
				"std"         => $stdgenre,
				"title"       => __( "Google News Genre", 'wordpress-seo-news' ),
				"description" => __( "Genre to show in Google News Sitemap.", 'wordpress-seo-news' ),
				"options"     => array(
						"none"          => __( "None", 'wordpress-seo-news' ),
						"pressrelease"  => __( "Press Release", 'wordpress-seo-news' ),
						"satire"        => __( "Satire", 'wordpress-seo-news' ),
						"blog"          => __( "Blog", 'wordpress-seo-news' ),
						"oped"          => __( "Op-Ed", 'wordpress-seo-news' ),
						"opinion"       => __( "Opinion", 'wordpress-seo-news' ),
						"usergenerated" => __( "User Generated", 'wordpress-seo-news' ),
				),
		);
		$mbs['newssitemap-original']     = array(
				"name"        => "newssitemap-original",
				"std"         => "",
				"type"        => "text",
				"title"       => __( "Original Source", 'wordpress-seo-news' ),
				"description" => __( 'Is this article the original source of this news? If not, please enter the URL of the original source here. If there are multiple sources, please separate them by a pipe symbol: | .', 'wordpress-seo-news' ),
		);
		$mbs['newssitemap-stocktickers'] = array(
				"name"        => "newssitemap-stocktickers",
				"std"         => "",
				"type"        => "text",
				"title"       => __( "Stock Tickers", 'wordpress-seo-news' ),
				"description" => __( 'A comma-separated list of up to 5 stock tickers of the companies, mutual funds, or other financial entities that are the main subject of the article. Each ticker must be prefixed by the name of its stock exchange, and must match its entry in Google Finance. For example, "NASDAQ:AMAT" (but not "NASD:AMAT"), or "BOM:500325" (but not "BOM:RIL").', 'wordpress-seo-news' ),
		);

		// Default standout description
		$standout_desc = 'If your news organization breaks a big story, or publishes an extraordinary work of journalism, you can indicate this by using the standout tag.<br/>';

		$max_standouts = 7;

		// Count standout tags
		$standout_query = new WP_Query(
				array(
						'post_type'   => 'any',
						'post_status' => 'publish',
						'meta_query'  => array(
								array(
										'key'   => '_yoast_wpseo_newssitemap-standout',
										'value' => 'on'
								)
						)
				)
		);

		$standout_desc .= '<span style="font-weight:bold;';
		if ( $standout_query->found_posts > $max_standouts ) {
			$standout_desc .= 'color:#ff0000';
		}
		$standout_desc .= '">';

		$standout_desc .= "You've used {$standout_query->found_posts}/{$max_standouts} standout tags.";

		$standout_desc .= '</span>';

		$mbs['newssitemap-standout'] = array(
				"name"        => "newssitemap-standout",
				"std"         => "",
				"type"        => "checkbox",
				"title"       => __( "Standout", 'wordpress-seo-news' ),
				"description" => __( $standout_desc, 'wordpress-seo-news' ),
		);

		$mbs['newssitemap-editors-pick'] = array(
				"name"        => "newssitemap-editors-pick",
				"std"         => "",
				"type"        => "checkbox",
				"title"       => __( "Editors' Pick", 'wordpress-seo-news' ),
				"description" => __( "Editors' Picks enables you to provide up to five links to original news content you believe represents your organization’s best journalistic work at any given moment, and potentially have it displayed on the Google News homepage or select section pages.", 'wordpress-seo-news' ),
		);

		return $mbs;
	}

	/**
	 * Add the meta boxes to meta box array so they get saved
	 *
	 * @param $meta_boxes
	 *
	 * @return array
	 */
	public function save( $meta_boxes ) {
		$meta_boxes = array_merge( $meta_boxes, $this->get_meta_boxes() );

		return $meta_boxes;
	}

	/**
	 * The tab header
	 */
	public function header() {
		global $post;

		// Get supported post types
		$post_types = array();
		foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $posttype ) {
			if ( isset( $this->options['newssitemap_include_' . $posttype->name] ) && ( 'on' == $this->options['newssitemap_include_' . $posttype->name] ) ) {
				$post_types[] = $posttype->name;
			}
		}

		// Display tab if post type is supported
		if ( count( $post_types ) > 0 ) {
			foreach ( $post_types as $post_type ) {
				if ( $post->post_type == $post_type ) {
					echo '<li class="news"><a class="wpseo_tablink" href="#wpseo_news">' . __( 'Google News', 'wordpress-seo-news' ) . '</a></li>';
				}
			}
		} else {
			// Support post if no post types are supported
			if ( $post->post_type == 'post' ) {
				echo '<li class="news"><a class="wpseo_tablink" href="#wpseo_news">' . __( 'Google News', 'wordpress-seo-news' ) . '</a></li>';
			}
		}

	}

	/**
	 * The tab content
	 */
	public function content() {
		global $post;

		// Get supported post types
		$post_types = array();
		foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $posttype ) {
			if ( isset( $this->options['newssitemap_include_' . $posttype->name] ) && ( 'on' == $this->options['newssitemap_include_' . $posttype->name] ) ) {
				$post_types[] = $posttype->name;
			}
		}

		// Display content if post type is supported
		if ( count( $post_types ) > 0 ) {
			if ( ! in_array( $post->post_type, $post_types ) ) {
				return;
			}
		} else {
			// Support post if no post types are supported
			if ( $post->post_type != 'post' ) {
				return;
			}
		}

		// Build tab content
		$content = '';
		foreach ( $this->get_meta_boxes() as $meta_key => $meta_box ) {
			$content .= $this->do_meta_box( $meta_box, $meta_key );
		}
		$this->do_tab( 'news', __( 'Google News', 'wordpress-seo-news' ), $content );
	}


}