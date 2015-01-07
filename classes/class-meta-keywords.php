<?php

class WPSEO_News_Meta_Keywords {

	/**
	 * Getting the keywords for given $item_id
	 *
	 * @param integer $item_id
	 * @param bool    $as_string
	 *
	 * @return string
	 */
	public static function list_keywords( $item_id, $as_string = true ) {

		// Getting keywords for given item_id
		$keywords = explode( ',', trim( WPSEO_Meta::get_value( 'newssitemap-keywords', $item_id ) ) );

		// Listing the tags for given item_id
		$keywords = self::get_the_terms( $item_id, $keywords );

		// Getting the default keywords from options
		$keywords = self::get_default_keywords( $keywords );

		// Trim each keyword
		$keywords = array_map( array(self, 'parse_keyword'), $keywords );

		// Make the list of keywords unique
		$keywords = array_unique( $keywords );

		// If keywords should be returned as string, implode a comma between each keyword
		if ( $as_string ) {
			$keywords = trim( implode( ', ', $keywords ), ', ' );
		}

		return $keywords;
	}

	/**
	 * Getting the terms for given item_id
	 *
	 * Each term will be added to keywords
	 *
	 * @param integer $item_id
	 * @param array $keywords
	 *
	 * @return array
	 */
	private static function get_the_terms( $item_id, $keywords) {
		$tags = get_the_terms( $item_id, 'post_tag' );
		if ( $tags ) {
			foreach ( $tags as $tag ) {
				$keywords[] = $tag->name;
			}
		}

		return $keywords;
	}

	/**
	 * If there are default keywords, use these also in keyword string
	 *
	 * @param $keywords
	 *
	 * @return mixed
	 */
	private static function get_default_keywords( $keywords ) {

		$options = WPSEO_News::get_options();

		// TODO: add suggested keywords to each post based on category, next to the entire
		if ( isset( $options['default_keywords'] ) && $options['default_keywords'] != '' ) {
			$default_keywords = explode( ',', $options['default_keywords'] );
			$keywords         = array_merge( $keywords, $default_keywords );
		}

		return $keywords;
	}

	/**
	 * This method will lowercase the whole keyword and trim spaces before and after it.
	 *
	 * @param string $keyword
	 *
	 * @return string
	 */
	private static function parse_keyword($keyword) {
		$keyword = strtolower( trim($keyword) );
		return $keyword;
	}

}