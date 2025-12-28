<?php
/**
 * @package Polylang
 */

/**
 * A class to easily manage licenses for Polylang Pro and addons
 *
 * @since 1.9
 */
class PLL_License {
	/**
	 * URL to Polylang's account page.
	 *
	 * @var string
	 */
	public const ACCOUNT_URL = 'https://polylang.pro/my-account/';

	/**
	 * Sanitized plugin name.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Plugin name.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * License key.
	 *
	 * @var string
	 */
	public $license_key;

	/**
	 * License data, obtained from the API request.
	 *
	 * @var stdClass|null
	 */
	public $license_data;

	/**
	 * Main plugin file.
	 *
	 * @var string
	 */
	private $file;

	/**
	 * Current plugin version.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Plugin author.
	 *
	 * @var string
	 */
	private $author;

	/**
	 * API url.
	 *
	 * @var string.
	 */
	private $api_url = 'https://polylang.pro';

	/**
	 * Constructor
	 *
	 * @since 1.9
	 *
	 * @param string $file      The plugin file.
	 * @param string $item_name The plugin name.
	 * @param string $version   The plugin version.
	 * @param string $author    Author name.
	 * @param string $api_url   Optional url of the site managing the license.
	 */
	public function __construct( $file, $item_name, $version, $author, $api_url = null ) {
		$this->id      = sanitize_title( $item_name );
		$this->file    = $file;
		$this->name    = $item_name;
		$this->version = $version;
		$this->author  = $author;
		$this->api_url = empty( $api_url ) ? $this->api_url : $api_url;

		$licenses          = (array) get_option( 'polylang_licenses', array() );
		$license           = isset( $licenses[ $this->id ] ) && is_array( $licenses[ $this->id ] ) ? $licenses[ $this->id ] : array();
		$this->license_key = ! empty( $license['key'] ) ? (string) $license['key'] : '';

		if ( ! empty( $license['data'] ) ) {
			$this->license_data = (object) $license['data'];
		}

		// Updater
		$this->auto_updater();

		// Register settings
		add_filter( 'pll_settings_licenses', array( $this, 'settings' ) );

		// Weekly schedule
		if ( ! wp_next_scheduled( 'polylang_check_licenses' ) ) {
			wp_schedule_event( time(), 'weekly', 'polylang_check_licenses' );
		}

		add_action( 'polylang_check_licenses', array( $this, 'check_license' ) );
	}

	/**
	 * Auto updater
	 *
	 * @since 1.9
	 *
	 * @return void
	 */
	public function auto_updater() {
		$args = array(
			'version'   => $this->version,
			'license'   => $this->license_key,
			'author'    => $this->author,
			'item_name' => $this->name,
		);

		// Setup the updater
		new PLL_Plugin_Updater( $this->api_url, $this->file, $args );
	}

	/**
	 * Registers the licence in the Settings.
	 *
	 * @since 1.9
	 *
	 * @param PLL_License[] $items Array of objects allowing to manage a license.
	 * @return PLL_License[]
	 */
	public function settings( $items ) {
		$items[ $this->id ] = $this;
		return $items;
	}

	/**
	 * Activates the license key.
	 *
	 * @since 1.9
	 *
	 * @param string $license_key Activation key.
	 * @return PLL_License Updated PLL_License object.
	 */
	public function activate_license( $license_key ) {
		$this->license_key = $license_key;
		$this->api_request( 'activate_license' );

		// Tell WordPress to look for updates.
		delete_site_transient( 'update_plugins' );
		return $this;
	}


	/**
	 * Deactivates the license key.
	 *
	 * @since 1.9
	 *
	 * @return PLL_License Updated PLL_License object.
	 */
	public function deactivate_license() {
		$this->api_request( 'deactivate_license' );
		return $this;
	}

	/**
	 * Checks if the license key is valid.
	 *
	 * @since 1.9
	 *
	 * @return void
	 */
	public function check_license() {
		// Lisans kontrolü devre dışı
		return;
	}

	/**
	 * Sends an api request to check, activate or deactivate the license
	 * Updates the licenses option according to the status
	 *
	 * @since 1.9
	 *
	 * @param string $request check_license | activate_license | deactivate_license
	 * @return void
	 */
	private function api_request( $request ) {
		// API isteği gönderimi engellendi
		return;
	}

	/**
	 * Get the html form field in a table row (one per license key) for display
	 *
	 * @since 2.7
	 *
	 * @return string
	 */
	public function get_form_field() {
		// Lisans giriş kutusu gizlendi
		return '';
	}
}
