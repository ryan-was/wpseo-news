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
		$stdgenre                        = ( isset( $this->options['newssitemap_default_genre'] ) ) ? $this->options['newssitemap_default_genre'] : 'blog';
		$mbs['newssitemap-exclude']      = array(
				"name"  => "newssitemap-exclude",
				"type"  => "checkbox",
				"std"   => "on",
				"title" => __( "Exclude from News Sitemap" )
		);
		$mbs['newssitemap-keywords']     = array(
				"name"        => "newssitemap-keywords",
				"type"        => "text",
				"std"         => "",
				"title"       => __( "Meta News Keywords" ),
				"description" => __( "Comma separated list of the keywords this article aims at.", "wordpress-seo" ),
		);
		$mbs['newssitemap-genre']        = array(
				"name"        => "newssitemap-genre",
				"type"        => "multiselect",
				"std"         => $stdgenre,
				"title"       => __( "Google News Genre", 'yoast-wpseo' ),
				"description" => __( "Genre to show in Google News Sitemap.", 'yoast-wpseo' ),
				"options"     => array(
						"none"          => __( "None", 'yoast-wpseo' ),
						"pressrelease"  => __( "Press Release", 'yoast-wpseo' ),
						"satire"        => __( "Satire", 'yoast-wpseo' ),
						"blog"          => __( "Blog", 'yoast-wpseo' ),
						"oped"          => __( "Op-Ed", 'yoast-wpseo' ),
						"opinion"       => __( "Opinion", 'yoast-wpseo' ),
						"usergenerated" => __( "User Generated", 'yoast-wpseo' ),
				),
		);
		$mbs['newssitemap-original']     = array(
				"name"        => "newssitemap-original",
				"std"         => "",
				"type"        => "text",
				"title"       => __( "Original Source", 'yoast-wpseo' ),
				"description" => __( 'Is this article the original source of this news? If not, please enter the URL of the original source here. If there are multiple sources, please separate them by a pipe symbol: | .', 'yoast-wpseo' ),
		);
		$mbs['newssitemap-stocktickers'] = array(
				"name"        => "newssitemap-stocktickers",
				"std"         => "",
				"type"        => "text",
				"title"       => __( "Stock Tickers", 'yoast-wpseo' ),
				"description" => __( 'A comma-separated list of up to 5 stock tickers of the companies, mutual funds, or other financial entities that are the main subject of the article. Each ticker must be prefixed by the name of its stock exchange, and must match its entry in Google Finance. For example, "NASDAQ:AMAT" (but not "NASD:AMAT"), or "BOM:500325" (but not "BOM:RIL").', 'yoast-wpseo' ),
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

		if ( isset ( $this->options['newssitemap_posttypes'] ) && $this->options['newssitemap_posttypes'] != '' ) {
			foreach ( $this->options['newssitemap_posttypes'] as $post_type ) {
				if ( $post->post_type == $post_type ) {
					echo '<li class="news"><a class="wpseo_tablink" href="#wpseo_news">' . __( 'Google News', 'wordpress-seo' ) . '</a></li>';
				}
			}
		} else {
			if ( $post->post_type == 'post' ) {
				echo '<li class="news"><a class="wpseo_tablink" href="#wpseo_news">' . __( 'Google News', 'wordpress-seo' ) . '</a></li>';
			}
		}
	}

	/**
	 * The tab content
	 */
	public function content() {
		global $post;

		if ( isset( $this->options['newssitemap_posttypes'] ) && $this->options['newssitemap_posttypes'] != '' ) {
			if ( !in_array( $post->post_type, $this->options['newssitemap_posttypes'] ) ) {
				return;
			}
		} else {
			if ( $post->post_type != 'post' ) {
				return;
			}
		}

		$content = '';
		foreach ( $this->get_meta_boxes() as $meta_key => $meta_box ) {
			$content .= $this->do_meta_box( $meta_box, $meta_key );
		}
		$this->do_tab( 'news', __( 'Google News', 'wordpress-seo' ), $content );
	}


}