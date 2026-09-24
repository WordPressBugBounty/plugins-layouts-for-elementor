<?php
/**
 * APIs.
 *
 * @package LFE
 */

namespace Layouts_For_Elementor\API;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle Remote API requests.
 *
 * @package Layouts_For_Elementor\API
 */
class Layouts_Remote {

	/**
	 * Singleton instance.
	 *
	 * @var Layouts_Remote|null
	 */
	protected static $lfe_instance = null;

	/**
	 * Transient key used to cache the remote templates list.
	 */
	const TRANSIENT_TEMPLATE = 'layouts_template_info';

	/**
	 * Transient key used to cache the remote categories list.
	 */
	const TRANSIENT_CATEGORY = 'layouts_category_info';

	/**
	 * API endpoint for the templates list.
	 */
	const TEMPLATES = 'https://demo.layoutsforelementor.com/wp-json/layoutsforelementor/v1/templates';

	/**
	 * API endpoint for the categories list.
	 */
	const CATEGORIES = 'https://demo.layoutsforelementor.com/wp-json/layoutsforelementor/v1/categories';

	/**
	 * API endpoint template for a single template's data.
	 *
	 * @var string
	 */
	private static $template_url = 'https://demo.layoutsforelementor.com/wp-json/layoutsforelementor/v1/templates/%d';

	/**
	 * Layouts_Remote constructor.
	 */
	public function __construct() {
		$this->hooks();
	}

	/**
	 * Access the singleton plugin instance.
	 *
	 * @return Layouts_Remote
	 */
	public static function lfe_get_instance() {
		if ( null === self::$lfe_instance ) {
			self::$lfe_instance = new self();
		}

		return self::$lfe_instance;
	}

	/**
	 * Register the class's WordPress hooks.
	 */
	public function hooks() {
		add_action( 'wp_ajax_handle_sync', array( $this, 'template_sync' ) );
	}

	/**
	 * AJAX handler: force a refresh of the cached templates/categories lists.
	 *
	 * Restricted to administrators with a valid nonce, since it triggers
	 * outbound HTTP requests on demand.
	 */
	public function template_sync() {

		// Capability check — only administrators may force a remote sync.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( '-1', 403 );
		}

		// Nonce verification — the nonce is created in lfe_admin_scripts() as 'ajax-nonce'.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ajax-nonce' ) ) {
			wp_die( '-1', 403 );
		}

		$this->templates_list( true );
		$response = $this->categories_list( true );

		if ( $response ) {
			echo 'success';
		} else {
			echo 'error';
		}

		wp_die();
	}

	/**
	 * Get the templates list, from cache unless a forced update is requested.
	 *
	 * @param bool $force_update Bypass the cached transient and re-fetch.
	 * @return mixed|\WP_Error Decoded API response, or an error array.
	 */
	public function templates_list( $force_update = false ) {

		$response = get_transient( self::TRANSIENT_TEMPLATE );

		if ( ! $response || $force_update ) {

			$request = wp_remote_get(
				esc_url_raw( self::TEMPLATES ),
				array(
					'timeout'     => 15,
					'redirection' => 5,
					'blocking'    => true,
					'sslverify'   => true,
				)
			);

			if ( is_wp_error( $request ) ) {
				return array(
					'error'   => true,
					'message' => $request->get_error_message(),
				);
			}

			$body = wp_remote_retrieve_body( $request );

			if ( empty( $body ) ) {
				return array(
					'error'   => true,
					'message' => 'Empty API response',
				);
			}

			$response = json_decode( $body, true );

			if ( json_last_error() !== JSON_ERROR_NONE ) {
				return array(
					'error'   => true,
					'message' => 'Invalid JSON response',
				);
			}

			set_transient( self::TRANSIENT_TEMPLATE, $response, 12 * HOUR_IN_SECONDS );
		}

		return $response;
	}

	/**
	 * Get the templates categories list, from cache unless a forced update is requested.
	 *
	 * @param bool $force_update Bypass the cached transient and re-fetch.
	 * @return mixed|\WP_Error Decoded API response, or an error array.
	 */
	public function categories_list( $force_update = false ) {
		$response = get_transient( self::TRANSIENT_CATEGORY );

		if ( ! $response || $force_update ) {

			$request = wp_remote_get(
				esc_url_raw( self::CATEGORIES ),
				array(
					'timeout'     => 15,
					'redirection' => 5,
					'blocking'    => true,
					'sslverify'   => true,
				)
			);

			if ( is_wp_error( $request ) ) {
				return array(
					'error'   => true,
					'message' => $request->get_error_message(),
				);
			}

			$body = wp_remote_retrieve_body( $request );

			if ( empty( $body ) ) {
				return array(
					'error'   => true,
					'message' => 'Empty API response',
				);
			}

			$response = json_decode( $body, true );

			if ( json_last_error() !== JSON_ERROR_NONE ) {
				return array(
					'error'   => true,
					'message' => 'Invalid JSON response',
				);
			}

			set_transient( self::TRANSIENT_CATEGORY, $response, HOUR_IN_SECONDS );
		}
		return $response;
	}

	/**
	 * Get a single template's full content.
	 *
	 * @param int $template_id Template ID.
	 * @return mixed|\WP_Error
	 */
	public function get_template_content( $template_id ) {
		$url = sprintf( self::$template_url, $template_id );

		$response = wp_remote_get(
			esc_url_raw( $url ),
			array(
				'timeout'     => 15,
				'redirection' => 5,
				'blocking'    => true,
				'sslverify'   => true,
			)
		);
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_code = (int) wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			return new \WP_Error( 'response_code_error', sprintf( 'The request returned with a status code of %s.', $response_code ) );
		}

		$template_content = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( isset( $template_content['error'] ) ) {
			return new \WP_Error( 'response_error', $template_content['error'] );
		}

		if ( empty( $template_content['data'] ) && empty( $template_content['content'] ) ) {
			return new \WP_Error( 'template_data_error', 'An invalid data was returned.' );
		}

		return $template_content;
	}
}

new Layouts_Remote();
