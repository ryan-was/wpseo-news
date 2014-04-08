<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WPSEO_News_Upgrade_Manager {

	/**
	 * Check if there's a plugin update
	 */
	public function check_update() {

		/**
		 * @todo Get option via WPSEO option framework
		 */

		// Get current version
		$current_version = get_site_option( WPSEO_News::OPTION_CURRENT_VERSION, '0' );

		// Check if update is required
		if( 1 === version_compare( WPSEO_News::VERSION, $current_version ) ) {

			// Do update
			$this->do_update( $current_version );

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

		/**
		 * @todo Do the option upgrade
		 */

	}

	/**
	 * Update the current version code
	 */
	private function update_current_version_code() {
		/**
		 * @todo Update option via WPSEO option framework
		 */
		//update_site_option( WPSEO_Premium::OPTION_CURRENT_VERSION, WPSEO_Premium::PLUGIN_VERSION_CODE );
	}

}