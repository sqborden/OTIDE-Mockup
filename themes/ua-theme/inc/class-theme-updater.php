<?php
/**
 * Class that controls theme update API
 */
class UA_Theme_Updater {
	/**
	 * The update API
	 *
	 *
	 * @var string
	 * @access protected
	 */
	protected $api_url = 'https://web.ua.edu/services/wp/theme/v3/wp-update-check/';

	/**
	 * The plugin slug
	 *
	 * @var mixed
	 * @access protected
	 */
	protected $slug;


	/**
	 * The plugin version
	 *
	 * @var string
	 * @access protected
	 */
	protected $version;

	/**
	 * Various API data such as name, slug, version, etc
	 *
	 * (default value: array())
	 *
	 * @var array
	 * @access protected
	 */
	protected $api_data = array();

	public function __construct() {
		$this->slug    = UA_THEME_NAME;
		$this->version = UA_THEME_VERSION;

		$this->api_data['slug']    = $this->slug;
		$this->api_data['version'] = $this->version;

		// Hook into the plugin update check
		add_filter( 'pre_set_site_transient_update_themes', array( $this, 'api_check' ) );

		// Display plugin details screen for updating
		add_filter( 'themes_api', array( $this, 'api_info' ), 10, 3 );

		// For testing only
		//add_action( 'init', array( $this, 'delete_transient' ) );
	}

	/**
	 * Delete transients on page load
	 *
	 * FOR TESTING PURPOSES ONLY
	 *
	 * @access public
	 * @return void
	 */
	public function delete_transient() {
		delete_site_transient( 'update_themes' );
	}

	/**
	 * Check the plugin version to see if there's a new one
	 *
	 * @access public
	 * @param mixed $transient
	 * @return void
	 */
	public function api_check( $transient ) {
		// If no checked transiest, just return its value without hacking it
		if ( empty( $transient ) )
			return $transient;

		// Send request checking for an update
		$response = $this->api_request(
			'update-check',
			array(
				'slug' => $this->slug,
			)
		);

		// If response is false, don't alter the transient
		if( false !== $response && is_array( $response ) && isset( $response['new_version'] ) ) {
			// If this version is less than the new version
			if( version_compare( $this->version, $response['new_version'], '<' ) )
				$transient->response[ $this->slug ] = $response;
		}
		
		return $transient;
	}

	/**
	 * Return the plugin details for the plugin update screen
	 *
	 * @access public
	 * @param mixed $data
	 * @param string $action (default: '')
	 * @param mixed $args (default: null)
	 * @return void
	 */
	public function api_info( $data, $action = '', $args = null ) {
		if ( ( $action != 'theme_information' ) || !isset( $args->slug ) || ( $args->slug != $this->slug ) )
			return $data;

		// Send request checking for an update
		$response = $this->api_request(
			'theme-info',
			array(
				'slug' => $this->slug,
			)
		);

		if ( false !== $response )
			$data = $response;

		return $data;
	}

	/**
	 * Send a request to the custom API
	 *
	 * @access public
	 * @param mixed $action
	 * @param mixed $data
	 * @return void
	 */
	public function api_request( $action, $data ) {
		global $wp_version;

		$data = array_merge( $this->api_data, $data );

		if ( $data['slug'] != $this->slug )
			return;

		$api_params = array(
			'ua-action'=> $action,
			'slug' 	   => $this->slug,
		);

		$request = wp_remote_post(
			$this->api_url,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params
			)
		);

		if ( ! is_wp_error( $request ) ) {
			$request = json_decode( wp_remote_retrieve_body( $request ), true );

			return $request;
		}
		else
			return false;
	}
}