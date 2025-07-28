<?php

namespace Templately\Core\Importer\Runners;

use Elementor\Plugin;
use Exception;
use Templately\Core\Importer\Utils\Utils;
use Templately\Utils\Helper;

class ElementorContent extends BaseRunner {
	public function get_name(): string {
		return 'content';
	}

	public function get_label(): string {
		return __( 'Elementor', 'templately' );
	}

	public function should_run( $data, $imported_data = [] ): bool {
		return $this->manifest['platform'] === 'elementor' && ! empty( $this->manifest['content'] );
	}

	public function should_log(): bool {
		return true;
	}

	public function get_action(): string {
		return 'updateLog';
	}

	public function log_message(): string {
		return __( 'Importing Elementor Page and Post Templates', 'templately' );
	}

	/**
	 * @throws Exception
	 */
	public function import( $data, $imported_data ): array {
		$results  = $data["imported_data"]["content"] ?? [];
		$contents = $this->manifest['content'];
		$path     = $this->dir_path . 'content' . DIRECTORY_SEPARATOR;
		$processed_templates = $this->get_progress();

		// $total     = array_reduce( $contents, function ( $carry, $item ) {
		// 	return $carry + count( $item );
		// }, 0 );
		// $processed = 0;

		/**
		 * Check if there is any active kit?
		 * If not, create one.
		 */

		$kits_manager = Plugin::$instance->kits_manager;

		$active_kit = $kits_manager->get_active_id();
		$kit        = $kits_manager->get_kit( $active_kit );
		$old_logo   = $kit->get_settings('site_logo');

		if(isset($this->manifest['has_settings']) && $this->manifest['has_settings'] && !in_array("global_colors", $processed_templates)){
			// backing up the active kit id before updating the new one
			if(!get_option("__templately_" . $kits_manager::OPTION_ACTIVE)){
				add_option("__templately_" . $kits_manager::OPTION_ACTIVE, $active_kit, '', 'no');
			}
			else{
				update_option("__templately_" . $kits_manager::OPTION_ACTIVE, $active_kit, 'no');
			}

			$file     = $this->dir_path . "settings.json";
			$settings = Utils::read_json_file( $file );

			if(isset($settings['site_name'])){
				unset($settings['site_name']);
			}
			if(isset($settings['site_description'])){
				unset($settings['site_description']);
			}
			if(!empty($data['color'])){
				if (!empty($settings['system_colors'])) {
					foreach ($settings['system_colors'] as $key => $color) {
						$settings['system_colors'][$key]['color'] = $data['color'][$color['_id']] ?? $color['color'];
					}
				}
				if (!empty($settings['custom_colors'])) {
					foreach ($settings['custom_colors'] as $key => $color) {
						$settings['custom_colors'][$key]['color'] = $data['color'][$color['_id']] ?? $color['color'];
					}
				}
			}

			if(!empty($data['typography'])){
				if (!empty($settings['system_typography'])) {
					foreach ($settings['system_typography'] as $key => &$typography) {
						if(!empty($data['typography'][$typography['_id']])){
							$typography = array_merge($typography, $data['typography'][$typography['_id']]);
						}
					}
				}
				if (!empty($settings['custom_typography'])) {
					foreach ($settings['custom_typography'] as $key => &$typography) {
						if(!empty($data['typography'][$typography['_id']])){
							$typography = array_merge($typography, $data['typography'][$typography['_id']]);
						}
					}
				}
			}

			if (!empty($data['logo']['id'])) {
				$settings['site_logo'] = $data['logo'];
				Utils::backup_option_value( 'site_logo' );
				$this->origin->update_imported_list('attachment', $data['logo']['id']);
			} elseif (!empty($data['logo'])) {
				$settings['site_logo'] = $old_logo;

				// If there's no old logo id, try to upload a new logo
				if (empty($old_logo['id'])) {
					$site_logo = Utils::upload_logo($data['logo'], $this->session_id);

					// If the upload was successful, use the new logo, otherwise use the old one
					if(!empty($site_logo['id'])){
						$settings['site_logo'] = $site_logo;
						Utils::backup_option_value( 'site_logo' );
						$this->origin->update_imported_list('attachment', $site_logo['id']);
					}
				}
			}


			$kit_id = $kits_manager->create_new_kit( $this->manifest['name'], $settings, true );

			$kit    = $kits_manager->get_kit( $kit_id );

			// $kit->update_settings( ['site_logo' => $settings['site_logo']] );

			// Create an array with the post ID and the new title
			$post_data = array(
				'ID'         => $kit_id,
				'post_title' => $this->manifest['name'] . " Kit",
			);
			// Update the post
			wp_update_post( $post_data );

			$processed_templates[] = "global_colors";
			$this->update_progress( $processed_templates);
		}

		$active_kit = $kits_manager->get_active_id();
		$kit        = $kits_manager->get_kit( $active_kit );

		if ( ! $kit->get_id() ) {
			$kit = $kits_manager->create_default();
			update_option( $kits_manager::OPTION_ACTIVE, $kit );
		}

		// $processed = 0;
		$total     = array_reduce($contents, function($carry, $item) {
			return $carry + count($item);
		}, 0);

		$results = $this->loop( $contents, function($post_type, $post, $results ) use($path, $imported_data, $total) {
			return $this->loop( $post, function($id, $content_settings, $result ) use ($post_type, $results, $path, $imported_data, $total) {
				if ( post_type_exists( $post_type ) ) {

					$import = $this->import_post_type_content( $id, $post_type, $path, $imported_data, $content_settings );

					if ( ! $import ) {
						$result[ $post_type ]['failed'][ $id ] = $import;
					} else {
						Utils::import_page_settings( $import, $content_settings );
						$result[ $post_type ]['succeed'][ $id ] = $import;
					}

					// Broadcast Log
					$processed = 0;
					$results = Helper::recursive_wp_parse_args($result, $results);
					array_walk_recursive($results, function($item) use (&$processed) {
						$processed++;
					});
					$progress   = floor( ( 100 * $processed ) / $total );
					$this->log( $progress );
				}

				return $result;
			}, $post_type); //, true
		});

		return [ 'content' => $results ];
	}

	/**
	 * @throws Exception
	 */
	private function import_post_type_content( $id, $post_type, $path, $imported_data, $content_settings ) {
		try {
			$template = $this->factory->create( $content_settings['doc_type'], [
				'post_title'  => $content_settings['title'],
				'post_status' => 'publish',
				'post_type'   => $post_type,
			] );

			$file      = $path . $post_type . DIRECTORY_SEPARATOR . "{$id}.json";
			$post_data = Utils::read_json_file( $file );

			if ( ! empty( $content_settings['data'] ) ) {
				/**
				 * TODO:
				 *
				 * We can check if there is any data for settings.
				 * if yes: ignore content from insert.
				 *
				 * Process the content while finalizing.
				 */
				// $this->json->prepare( $post_data['content'], $id, $content_settings['data'], $imported_data );

				$post_data['content'] = [];
			}

			unset($content_settings['conditions']);
			$post_data['import_settings'] = $content_settings;

			$template->import( $post_data );

			return $template->get_main_id();
		} catch ( Exception $e ) {
			return false;
		}
	}
}