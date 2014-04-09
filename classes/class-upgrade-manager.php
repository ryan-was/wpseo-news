<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class WPSEO_News_Upgrade_Manager {

	/**
	 * Check if there's a plugin update
	 */
	public function check_update() {

		// Get options
		$options = WPSEO_News::get_options();

		// Check if update is required
		if ( 1 === version_compare( WPSEO_News::VERSION, $options['dbversion'] ) ) {

			// Do update
			$this->do_update( $options['dbversion'] );

			// Update version code
			$this->update_current_version_code();

		}

	}

	/**
	 * An update is required, do it
	 *
	 * @param $current_version
	 */
	private function do_update( $current_version ) {
	}

	/**
	 * Update the current version code
	 */
	private function update_current_version_code() {
		$options              = WPSEO_News::get_options();
		$options['dbversion'] = WPSEO_News::VERSION;
		update_site_option( 'wpseo_news_options', $options );
	}

}