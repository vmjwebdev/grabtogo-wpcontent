<?php

namespace Templately\Core\Importer\Runners;

use Exception;
use Templately\Builder\PageTemplates;
use Templately\Builder\Types\BaseTemplate;
use Templately\Core\Importer\Utils\Utils;
use Templately\Utils\Helper;

class Templates extends BaseRunner {
	protected $imported_types = [];

	public function get_name(): string {
		return 'templates';
	}

	public function get_label(): string {
		return __( 'Templates', 'templately' );
	}

	public function log_message(): string {
		return __( 'Importing Templates (i.e: Header, Footer, etc)', 'templately' );
	}

	public function should_run( $data, $imported_data = [] ): bool {
		return ! empty( $this->manifest['templates'] );
		// return isset( $data['templates'] ) && $data['templates'] && ! empty( $this->manifest['templates'] );
	}

	/**
	 * @throws Exception
	 */
	public function import( $data, $imported_data ): array {
		$results  = $data["imported_data"]["templates"] ?? [];
		$templates = $this->manifest['templates'];
		$path      = $this->dir_path . 'templates' . DIRECTORY_SEPARATOR;

		$_extra_pages = [];


		$total     = count( $templates );

		$results = $this->loop( $templates, function($id, $template_settings, $results ) use( $path, $total ) {
			$template_content = Utils::read_json_file( $path . $id . '.json' );

			$import = $this->import_template( $id, $template_settings, $template_content );
			if ( $import ) {
				$results['templates']['succeed'][ $id ]   = $import['id'];
				$results['templates']['template_types'][] = $template_settings['type'];

				if ( $template_settings['type'] === 'archive' || $template_settings['type'] === 'product_archive' || $template_settings['type'] === 'course_archive' ) {
					$page_id = $this->create_archive_page( $template_settings, $this->manifest['platform'] );
					if ( $page_id && isset($template_settings['page_settings']['archive_page_id']) ) {
						$archive_page_id = $template_settings['page_settings']['archive_page_id'];
						$results['archive_settings'][$archive_page_id] = [
							'old_id'     => $id,
							'page_id'    => $page_id,
							'archive_id' => $archive_page_id
						];
					}
				}

			} else {
				$results['templates']['failed'][ $id ] = $import;
			}

			// Broadcast Log
			$processed = 0;
			$processed_template = array_merge($results['templates']['succeed'] ?? [], $results['templates']['failed'] ?? []);
			array_walk_recursive($processed_template, function($item, $key) use (&$processed) {
				$processed++;
			});
			$progress  = floor( ( 100 * $processed ) / $total );
			$this->log( $progress );

			$results['templates']['__attachments'][ $id ] = isset($import['__attachments']) ? $import['__attachments'] : [];
			// Add the template to the processed templates and update the session data


			return $results;
		}); // , null, true

		return $results;
	}

	private function import_template( $id, $template_settings, $template_content ) {
		$type = $template_settings['type'];

		/**
		 * @var BaseTemplate $template
		 */

		$post_data = [
			'post_title'  => $template_settings['title'] ?? ucfirst( $type ) . ' - (by Templately)',
			'post_status' => 'publish',
			'post_type'   => 'templately_library',
		];

		$meta = [];
		$result = [];

		if ( $this->manifest['platform'] == 'gutenberg' ) {
			$meta['_wp_page_template'] = PageTemplates::TEMPLATE_HEADER_FOOTER;
		}

		$template = $this->factory->create( $type, $post_data, $meta );

		if ( is_wp_error( $template ) ) {
			return false;
		}
		if (!$template->is_elementor_template()) {
			$attachments = $this->json->parse_images($template_content['content']);

			if (!empty($attachments)) {
				$result['__attachments'] = $attachments;
				// $manifest_content = &$this->manifest['templates'][$id];
				// if(!isset($manifest_content['__attachments'])){
				// 	$manifest_content['__attachments'] = [];
				// }
				// $manifest_content['__attachments'] = $attachments;
			}
		}

		$template_content['id']              = $id;
		unset($template_settings['conditions']);
		$template_content['import_settings'] = $template_settings;
		$template->import( $template_content );

		if($template->has_logo($template_content)){
			$this->manifest['templates'][$id]['has_logo'] = true;
		}
		$result['id'] = $template->get_main_id();
		return $result;
	}


	/**
	 * @param $template_settings
	 * @param $platform
	 *
	 * @return false|int
	 */
	private function create_archive_page( $template_settings, $platform ) {
		try {
			$archive_page_id = $template_settings['page_settings']['archive_page_id'] ?? null;
			if($archive_page_id && !empty($this->manifest['content']['page'][$archive_page_id])){
				return false;
			}

			$type = $template_settings['type'];

			$archive_page = wp_insert_post( [
				'post_title'    => $template_settings['title'] ?? ucfirst( $type ) . ' - (by Templately)',
				'post_status'   => 'publish',
				'post_type'     => 'page',
				'post_content'  => '',
				'page_template' => $platform === 'elementor' ? 'elementor_header_footer' : PageTemplates::TEMPLATE_HEADER_FOOTER,
			] );

			if ( is_wp_error( $archive_page ) ) {
				return false;
			}

			if($type === 'archive'){
				Utils::update_option( 'page_for_posts', $archive_page );
			}

			if($type === 'product_archive'){
				Utils::update_option( 'woocommerce_shop_page_id', $archive_page );
			}
			return $archive_page;
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Only for gutenberg create a single page template.
	 * @return void
	 */
	private function create_page_template() {
		try {
			$meta = [];
			$data = [];

			$post_data = [
				'post_title'   => 'Single Page - (by Templately)',
				'post_status'  => 'publish',
				'post_type'    => 'templately_library',
			];

			if ( $this->manifest['platform'] == 'elementor' ) {
				$meta['_wp_page_template'] = 'elementor_header_footer';
				$data = json_decode('{"content":[{"id":"4a86515d","settings":[],"elements":[{"id":"30a46db1","settings":{"content_width":"full"},"elements":[],"isInner":false,"widgetType":"tl-post-content","elType":"widget"}],"isInner":false,"elType":"container"}],"settings":{"template":"elementor_header_footer"},"metadata":[]}', true);
			} elseif ( $this->manifest['platform'] == 'gutenberg' ) {
				$meta['_wp_page_template'] = PageTemplates::TEMPLATE_HEADER_FOOTER;
				$data = ['content' => '<!-- wp:post-content /-->'];
			}

			$template = $this->factory->create( 'page_single', $post_data, $meta );
			$template->import( $data );

			// return $archive_page;
		} catch ( \Exception $e ) {
			return;
		}
	}
}