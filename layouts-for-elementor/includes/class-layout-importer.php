<?php
/**
 * Class for importing a template.
 *
 * @package LFE
 */

namespace Elementor\TemplateLibrary;

use Layouts_For_Elementor\API\Layouts_Remote;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Imports a remote Layouts for Elementor template as a page or a library
 * template, by extending Elementor's own remote template-library source.
 */
class Layouts_Importer extends Source_Remote {

	/**
	 * Layouts_Importer constructor.
	 */
	public function __construct() {
		if ( ! function_exists( 'wp_crop_image' ) ) {
			include ABSPATH . 'wp-admin/includes/image.php';
		}
		$this->hooks();
	}

	/**
	 * Register the class's WordPress hooks.
	 */
	public function hooks() {
		add_action( 'wp_ajax_handle_import', array( $this, 'handle_import' ) );
	}

	/**
	 * AJAX handler: import a remote template as a page or library template.
	 *
	 * Restricted to users who can install plugins, with a valid nonce,
	 * since importing writes new content (a post plus Elementor postmeta)
	 * sourced from the remote template API.
	 */
	public function handle_import() {
		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_die( '-1', 403 );
		}

		if ( ! isset( $_POST['nonce'], $_POST['template_id'], $_POST['with_page'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ajax-nonce' ) ) {
			wp_die( '-1', 403 );
		}

		$template_id = absint( $_POST['template_id'] );
		$with_page   = sanitize_text_field( wp_unslash( $_POST['with_page'] ) );

		$template = Layouts_Remote::lfe_get_instance()->get_template_content( $template_id );

		if ( is_wp_error( $template ) ) {
			wp_die( esc_html( $template->get_error_message() ) );
		}

		// Finally create the page.
		$page_id = $this->create_page( $template, $with_page );

		if ( is_wp_error( $page_id ) ) {
			wp_die( esc_html( $page_id->get_error_message() ) );
		}

		echo esc_html( (string) (int) $page_id );
		exit;
	}

	/**
	 * Create a new post (page draft or Elementor library template) from
	 * remote template data.
	 *
	 * The remote content is JSON from Techeshta's own hosted API (not
	 * user-controllable), but it is still run through the same element
	 * ID-replacement and control-level import processing Elementor's own
	 * Source_Remote::get_data() applies to remote templates, and the
	 * title is sanitized before being used as a post field.
	 *
	 * @param array       $template  Decoded remote template data.
	 * @param string|bool $with_page Page title if importing as a page, false for a library template.
	 * @return int|\WP_Error New post ID, or a WP_Error on failure.
	 */
	private function create_page( $template, $with_page = false ) {
		if ( ! $template || empty( $template['content'] ) ) {
			return new \WP_Error( 'invalid_template', esc_html__( 'Invalid Template ID.', 'layouts-for-elementor' ) );
		}

		$content             = json_decode( $template['content'], true );
		$content             = $this->replace_elements_ids( $content );
		$template['content'] = $this->process_export_import_content( $content, 'on_import' );

		$title = ! empty( $template['title'] ) ? sanitize_text_field( $template['title'] ) : '';

		$args = array(
			'post_type'    => $with_page ? 'page' : 'elementor_library',
			'post_status'  => $with_page ? 'draft' : 'publish',
			'post_title'   => $with_page ? sanitize_text_field( $with_page ) : 'LFE: ' . $title,
			'post_content' => '',
		);

		$new_post_id = wp_insert_post( $args, true );

		if ( $new_post_id && ! is_wp_error( $new_post_id ) ) {
			$template_type = ! empty( $template['type'] ) ? sanitize_key( $template['type'] ) : 'page';
			$page_template = ! empty( $template['page_template'] ) ? sanitize_text_field( $template['page_template'] ) : 'elementor_canvas';

			update_post_meta( $new_post_id, '_elementor_data', wp_slash( wp_json_encode( $template['content'] ) ) );
			update_post_meta( $new_post_id, '_elementor_template_type', $template_type );
			update_post_meta( $new_post_id, '_elementor_edit_mode', 'builder' );
			update_post_meta( $new_post_id, '_lfe_import_type', $with_page ? 'page' : 'library' );
			update_post_meta( $new_post_id, '_lfe_template_id', absint( $template['id'] ) );
			update_post_meta( $new_post_id, '_wp_page_template', $page_template );

			if ( ! $with_page ) {
				$library_type = ! empty( $template['elementor_library_type'] ) ? sanitize_key( $template['elementor_library_type'] ) : 'page';
				wp_set_object_terms( $new_post_id, $library_type, 'elementor_library_type' );
			}

			return $new_post_id;
		}

		return new \WP_Error( 'import_error', esc_html__( 'Unable to create page.', 'layouts-for-elementor' ) );
	}
}

new Layouts_Importer();
