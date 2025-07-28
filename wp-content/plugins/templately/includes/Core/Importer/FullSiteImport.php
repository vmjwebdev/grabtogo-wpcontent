<?php

/**
 * remove 'action'  => 'continue',
 * way to retry
 * way to skip if failed multiple times
 *
 *	@todo: on runner check for timeout and retry
 *	@todo: use ErrorException on runner to skip item when error occurs: not useful
 *
 */


namespace Templately\Core\Importer;

use Elementor\Plugin;
use Error;
use Exception;
use Templately\Core\Importer\Exception\NonRetryableErrorException;
use Templately\Core\Importer\Exception\RetryableErrorException;
use Templately\Core\Importer\Exception\UnknownErrorException;
use Templately\Core\Importer\Utils\LogHandler;
use Templately\Core\Importer\Utils\Utils;
use Templately\Utils\Base;
use Templately\Utils\Helper;
use Templately\Utils\Installer;
use Templately\Utils\Options;

class FullSiteImport extends Base {
	use LogHelper;

	const SESSION_OPTION_KEY = 'templately_import_session';
	public    $manifest;
	protected $export;

	private $version = '1.0.0';

	public    $download_key;
	protected $dev_mode       = false;
	protected $api_key        = '';
	protected $session_id        = '';
	protected $documents_data = [];
	private   $is_import_status_handled = false;

	public    $dir_path;
	protected $filePath;
	protected $tmp_dir        = null;
	public    $request_params = [];

	public function __construct() {
		$this->dev_mode = defined('TEMPLATELY_DEV') && TEMPLATELY_DEV;
		$this->api_key  = Options::get_instance()->get('api_key');

		$this->add_ajax_action('import_settings', $this);
		$this->add_ajax_action('import_status', $this);
		$this->add_ajax_action('import', $this);
		$this->add_ajax_action('import_revert', $this);
		$this->add_ajax_action('import_info', $this);
		$this->add_ajax_action('import_close_feedback_modal', $this);
		$this->add_ajax_action('feedback_form', $this);
		$this->add_ajax_action('google_font', $this);

		add_action('admin_init', [$this, 'admin_init']);
		// add_action('admin_notices', [$this, 'add_revert_button']);

		if(isset($_GET['action']) && ($_GET['action'] == 'templately_pack_import' || $_GET['action'] == 'templately_pack_import_status')) {
			add_filter('wp_redirect', '__return_false', 999);
		}

		if ($this->dev_mode) {
			add_filter('http_request_host_is_external', '__return_true');
			add_filter('http_request_args', function ($args) {
				$args['sslverify'] = false;

				return $args;
			});
		}
	}

	public function add_ajax_action($action, $object) {
		add_action("wp_ajax_templately_pack_$action", function() use ($action, $object) {
			// Check nonce
			$nonce = null;
			if(isset($_POST['nonce'])){
				$nonce = $_POST['nonce'];
			}
			if(isset($_GET['nonce'])){
				$nonce = $_GET['nonce'];
			}
			if (!$nonce || !wp_verify_nonce($nonce, 'templately_nonce')) {
				wp_send_json_error(['message' => __('Invalid nonce', 'templately')]);
				wp_die();
			}

			// Check user capability
			if (!current_user_can('install_plugins') || !current_user_can('install_themes')) {
				wp_send_json_error(['message' => __('Insufficient permissions', 'templately')]);
				wp_die();
			}

			// Call the actual handler method
			call_user_func([$object, $action]);
		});
	}

	public function admin_init() {
		if (get_option('templately_flush_rewrite_rules', false)) {
			flush_rewrite_rules();
			delete_option('templately_flush_rewrite_rules');
		}
	}

	public function import_settings() {
		$data = wp_unslash($_POST);

		$upload_dir = wp_upload_dir();
		$session_id = uniqid();
		$tmp_dir    = trailingslashit($upload_dir['basedir']) . 'templately' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;

		$this->session_id   = $session_id;
		$data['session_id'] = $session_id;
		$data['root_dir']   = $tmp_dir;
		$data['dir_path']   = $tmp_dir . $session_id . DIRECTORY_SEPARATOR;
		$data['zip_path']   = $tmp_dir . "{$session_id}.zip";


		if ( is_array( $data ) && ! empty( $data ) ) {
			foreach ( $data as $key => $value ) {
				$json         = is_string($value) ? json_decode( $value, true ) : null;
				$data[ $key ] = $json !== null ? $json : $value;
			}
		}

		Utils::update_session_data($session_id, $data);


		//clear previous revert backup
		$options = Utils::get_backup_options();
		foreach ($options as $key => $value) {
			delete_option("__templately_$key");
		}
		delete_option('templately_fsi_imported_list');
		delete_option('templately_fsi_log');

		wp_send_json_success([
			'is_lightspeed' => !Helper::should_flush(),
			'session_id'    => $session_id,
		]);
	}

	public function import_close_feedback_modal() {
		$return = null;
		if(isset($_GET['closeAction']) && $_GET['closeAction']){
			$review_email = isset($_POST['review-email']) ? sanitize_email($_POST['review-email']) : '';
			$pack_id      = get_user_meta(get_current_user_id(), 'templately_fsi_pack_id', true);

			// Prepare the body of the request
			$body = json_encode([
				'action'      => $_GET['closeAction'],
				'email'       => $review_email,
				'pack_id'     => (int) $pack_id,
			]);

			// Send the request to the API
			$response = wp_remote_post($this->get_api_url('v2', 'feedback/close'), [
				'timeout' => 30,
				'headers' => [
					'Content-Type'         => 'application/json',
					'Authorization'        => 'Bearer ' . $this->api_key,
					'x-templately-ip'      => Helper::get_ip(),
					'x-templately-url'     => home_url('/'),
					'x-templately-version' => TEMPLATELY_VERSION,
				],
				'body' => $body,
			]);
			$body = wp_remote_retrieve_body($response);
			$return = json_decode($body, true);
		}
		update_user_meta(get_current_user_id(), 'templately_fsi_complete', 'done');
		wp_send_json_success($return);
	}
	public function feedback_form() {
		// Get data from $_POST
		$review_description = isset($_POST['review-description']) ? sanitize_textarea_field($_POST['review-description']) : '';
		$review_email       = isset($_POST['review-email']) ? sanitize_email($_POST['review-email']) : '';
		$rating             = isset($_POST['rating']) ? sanitize_text_field($_POST['rating']) : '';
		$pack_id            = get_user_meta(get_current_user_id(), 'templately_fsi_pack_id', true);

		// Prepare the body of the request
		$body = json_encode([
			'description' => $review_description,
			'email'       => $review_email,
			'rating'      => (int) $rating,
			'pack_id'     => (int) $pack_id,
		]);

		// Send the request to the API
		$response = wp_remote_post($this->get_api_url('v2', 'feedback/store'), [
			'timeout' => 30,
			'headers' => [
				'Content-Type'         => 'application/json',
				'Authorization'        => 'Bearer ' . $this->api_key,
				'x-templately-ip'      => Helper::get_ip(),
				'x-templately-url'     => home_url('/'),
				'x-templately-version' => TEMPLATELY_VERSION,
			],
			'body' => $body,
		]);

		if (is_wp_error($response)) {
			wp_send_json_error($response->get_error_message());
		}

		if (wp_remote_retrieve_response_code($response) != 200 && wp_remote_retrieve_response_code($response) != 201) {
			wp_send_json_error('API request failed with response code ' . wp_remote_retrieve_response_code($response), wp_remote_retrieve_response_code($response));
		}

		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		if (!isset($data['status']) || $data['status'] !== 'success') {
			wp_send_json_error('API response indicates failure.');
		}

		if (!isset($data['message'])) {
			wp_send_json_error('API response missing data.');
		}

		$result = $data['message'];

		wp_send_json_success($result);
	}

    // Modified get_session_data to use the static version
    protected function get_session_data() {
        return Utils::get_session_data_by_id();
    }

    // Modified update_session_data to use the static version
    protected function update_session_data($data) {
        return Utils::update_session_data_by_id($data);
    }

	public function initialize_props() {
		$data = $this->get_session_data();
		if (isset($data['session_id'])) {
			$this->session_id = $data['session_id'];
		}
		if (isset($data['dir_path'])) {
			$this->dir_path = $data['dir_path'];
		}
		if (isset($data['zip_path'])) {
			$this->filePath = $data['zip_path'];
		}
		if (isset($data['download_key'])) {
			$this->download_key = $data['download_key'];
		}
		if (isset($data['is_import_status_handled'])) {
			$this->is_import_status_handled = $data['is_import_status_handled'];
		}
	}

	private function clear_session_data(): bool {
		return delete_site_option(self::SESSION_OPTION_KEY);
	}

	private function finishRequestHeaders() {
		if(Helper::should_flush()) {
			// Disable output buffering and compression
			@ini_set('output_buffering', 'Off');
			@ini_set('zlib.output_compression', 'Off');
			@ini_set('implicit_flush', 1);

			// Time to run the import!  Set no limit
			set_time_limit(0);


			// Set headers to prevent caching and buffering
			header('Content-Type: text/event-stream, charset=UTF-8');
			header('Cache-Control: no-cache, must-revalidate');
			header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
			header('Connection: Keep-Alive');
			header('Pragma: no-cache');

			if (!empty($GLOBALS['is_nginx'])) {
				header('X-Accel-Buffering: no');
				header('Content-Encoding: none');
			}

			flush();
			ob_flush();
			wp_ob_end_flush_all();
		} else {
			header("Cache-Control: no-store, no-cache");
			// header( 'Content-Type: text/event-stream, charset=UTF-8' );
			// header( "Connection: Keep-Alive" );

			// Ignore user aborts and allow the script to run forever
			// (Use with caution, consider progress updates or timeouts)
			ignore_user_abort(true);

			// Time to run the import!  Set no limit
			set_time_limit(0);


			if (!empty($GLOBALS['is_nginx'])) {
				header('X-Accel-Buffering: no');
				header('Content-Encoding: none');
			}

			// Send output as soon as possible during long-running process
			if (function_exists('fastcgi_finish_request')) {
				fastcgi_finish_request();
			} elseif (function_exists('litespeed_finish_request')) {
				litespeed_finish_request();
			} else {
				wp_ob_end_flush_all();
			}
		}
	}

	public function import() {
		if ( ! $this->dev_mode && ! wp_doing_ajax() ) {
			exit;
		}

		add_filter( 'wp_image_editors', [ $this, 'wp_image_editors' ], 10, 1 );


		define('TEMPLATELY_START_TIME', microtime(true));

		// delete_option( 'templately_fsi_log' );

		register_shutdown_function( [ $this, 'register_shutdown' ] );

		$this->finishRequestHeaders();

		try {
			// TODO: Need to check if user is connected or not
			if(!empty($_GET['session_id'])){
				$this->session_id = sanitize_text_field($_GET['session_id']);
			}
			else {
				$this->throw(__('Invalid Session ID.', 'templately'));
			}


			$this->request_params = $this->get_session_data();
			$this->initialize_props();
			$this->add_revert_hooks();
			$progress = $this->request_params['progress'] ?? [];

			if(empty($progress['create_log_dir'])){
				// Create Log Directory and if fail then chose option method
				LogHandler::create_log_dir();

				$progress['create_log_dir'] = true;
				$this->update_session_data( [
					'progress' => $progress,
				] );
				$this->sse_message( [
					'type'    => 'eventLog',
					'action'  => 'eventLog',
					'info'    => 'create_log_dir',
					'results' => __METHOD__ . '::' . __LINE__,
				] );
			}

			$_id = isset($this->request_params['id']) ? (int) $this->request_params['id'] : null;

			if ($_id === null) {
				$this->throw(__('Invalid Pack ID.', 'templately'));
			}

			$this->sse_message( [
				'type'    => 'start',
				'action'  => 'eventLog',
				'results' => __METHOD__ . '::' . __LINE__,
			] );

			if(empty($progress['download_zip'])){
				/**
				 * Check Writing Permission
				 */
				$this->check_writing_permission();

				/**
				 * Download the zip
				 */
				$this->download_zip( $_id );

				$progress['download_zip'] = true;
				$this->update_session_data( [
					'progress' => $progress,
				] );
				$this->sse_message( [
					'type'    => 'continue',
					'action'  => 'continue',
					'info'    => 'download_zip',
					'results' => __METHOD__ . '::' . __LINE__,
				] );
				exit;
			}




			/**
			 * Reading Manifest File
			 */
			$this->manifest = $this->read_manifest($this->request_params['dir_path']);

			/**
			 * Version Check
			 */
			if ( ! empty( $this->manifest['version'] ) && version_compare( $this->manifest['version'], $this->version, '>' ) ) {
				/**
				 * FIXME: The message should be re-written (by content/support team).
				 */
				$this->throw( __( 'Please update the templately plugin.', 'templately' ) );
			}

			$platform = $this->manifest['platform'] ?? '';
			if($platform === 'elementor') {
				Helper::enable_elementor_container();
			}



			update_option('templately_import_platform', $platform);


			/**
			 * Should Revert Old Data
			 */
			// $this->revert();

			/**
			 * Platform Based Templates Import
			 */
			$this->start_content_import();

		} catch ( Exception $e ) {
			$should_retry = $e instanceof RetryableErrorException;
			$this->handle_import_status('failed', $e->getMessage());

			$this->sse_message([
				'action'  => 'error',
				'status'  => 'error',
				'type'    => "error",
				'retry'   => $should_retry,
				'title'   => __("Oops!", "templately"),
				'message' => $e->getMessage(),
				'trace'   => $e->getTraceAsString(),
			]);
		}

		// if($_GET['part'] === 'import'){
			// TODO: cleanup
			// $this->clear_session_data();
		// }
	}


	public function wp_image_editors( $editors ) {
		// If GD is available, use only GD. Otherwise, fallback to all available editors.
		if ( is_callable( [ 'WP_Image_Editor_GD', 'test' ] ) && call_user_func( [ 'WP_Image_Editor_GD', 'test' ] ) ) {
			return [ 'WP_Image_Editor_GD' ];
		}
		return $editors;
	}

	// Updated import_status method
	public function import_status() {
		$request_params = $this->get_session_data();

		if (isset($request_params['log_type']) && $request_params['log_type'] == 'file') {
			$log_index  = isset($_GET['lastLogIndex']) ? (int) $_GET['lastLogIndex'] : 0;
			$log = LogHandler::read_log_file($log_index);

			wp_send_json(['count' => count($log), 'log' => $log]);
		} else {
			$log = get_option('templately_fsi_log');

			if (!empty($log) && is_array($log) && isset($_GET['lastLogIndex'])) {
				$lastLogIndex = (int) $_GET['lastLogIndex'];
				$log = array_slice($log, $lastLogIndex);
			}
			wp_send_json(['count' => $log ? count($log) : 0, 'log' => $log]);
		}
	}

	/**
	 * @throws Exception
	 */
	private function throw($message, $code = 0) {
		if ($this->dev_mode) {
			error_log(print_r($message, 1));
		}
		throw new Exception($message);
	}
	/**
	 * @throws Exception
	 */
	private function throw_non_retryable($message, $code = 0) {
		if ($this->dev_mode) {
			error_log(print_r($message, 1));
		}
		throw new NonRetryableErrorException($message);
	}
	/**
	 * @throws Exception
	 */
	private function throw_retryable($message, $code = 0) {
		if ($this->dev_mode) {
			error_log(print_r($message, 1));
		}
		throw new RetryableErrorException($message);
	}
	/**
	 * @throws Exception
	 */
	private function throw_unknown($message, $code = 0) {
		if ($this->dev_mode) {
			error_log(print_r($message, 1));
		}
		throw new UnknownErrorException($message);
	}

	/**
	 * @throws Exception
	 */
	private function check_writing_permission() {
		$upload_dir = wp_upload_dir();

		if (!is_writable($upload_dir['basedir'])) {
			$this->throw(__('Upload directory is not writable.', 'templately'));
		}

		$this->tmp_dir = trailingslashit($upload_dir['basedir']) . 'templately' . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;

		if (!is_dir($this->tmp_dir)) {
			wp_mkdir_p($this->tmp_dir);
		}

		$this->sse_log('writing_permission_check', __('Permission Passed', 'templately'), 100);
	}

	private function get_api_url($version, $end_point): string {
		return $this->dev_mode ? "https://app.templately.dev/api/$version/" . $end_point : "https://app.templately.com/api/$version/" . $end_point;
	}

	private function info_get_api_url($id): string {
		return $this->dev_mode ? 'https://app.templately.dev/api/v1/import/info/pack/' . $id : 'https://app.templately.com/api/v1/import/info/pack/' . $id;
	}

	/**
	 * @throws Exception
	 */
	private function download_zip( $id ) {
		$this->sse_log( 'download', __( 'Downloading Template Pack', 'templately' ), 1 );
		$response = wp_remote_get( $this->get_api_url( "v2", "import/pack/$id" ), [
			'timeout' => 30,
			'headers' => [
				'Content-Type'         => 'application/json',
				'Authorization'        => 'Bearer ' . $this->api_key,
				'x-templately-ip'      => Helper::get_ip(),
				'x-templately-url'     => home_url('/'),
				'x-templately-version' => TEMPLATELY_VERSION,
			]
		]);

		$response_code = wp_remote_retrieve_response_code($response);
		$content_type  = wp_remote_retrieve_header($response, 'content-type');
		$this->download_key  = wp_remote_retrieve_header($response, 'download-key');

		if (is_wp_error($response)) {
			$this->throw_retryable(__('Template pack download failed', 'templately') . $response->get_error_message());
		} else if ($response_code != 200) {
			if (strpos($content_type, 'application/json') !== false) {
				// Retrieve Data from Response Body.
				$response_body = json_decode(wp_remote_retrieve_body($response), true);

				// If the response body is JSON and it contains an error, throw an exception with the error message
				if (isset($response_body['status']) && $response_body['status'] === 'error') {
					$support_message = '';
					if(strpos($response_body['message'], 'https://wpdeveloper.com/support') === false){
						$support_message = sprintf(__(" Please try again or contact <a href='%s' target='_blank'>support</a>.", "templately"), 'https://wpdeveloper.com/support');
					}
					$this->throw_non_retryable($response_body['message'] . $support_message);
				}
			}
			$this->throw_unknown(__('Template pack download failed with response code: ', 'templately') . $response_code);
		}

		$this->sse_log('download', __('Downloading Template Pack', 'templately'), 57);

		$this->update_session_data([
			'download_key' => $this->download_key,
		]);

		if (file_put_contents($this->filePath, $response['body'])) { // phpcs:ignore
			$this->sse_log('download', __('Downloading Template Pack', 'templately'), 100);

			$this->unzip();
		} else {
			$this->throw_retryable(__('Downloading Failed. Please try again', 'templately'));
		}
	}

	/**
	 * @throws Exception
	 */
	protected function unzip() {
		if (!WP_Filesystem()) {
			$this->throw(__('WP_Filesystem cannot be initialized', 'templately'));
		}

		$unzip = unzip_file($this->filePath, $this->dir_path);
		if (is_wp_error($unzip)) {
			$unzip = $this->unzip_file($this->filePath, $this->dir_path);
		}

		if (is_wp_error($unzip)) {
			$error = $unzip->get_error_message();
			if (empty($error)) {
				// Generic error message
				Helper::log($unzip);
				$error_message = sprintf(__("It seems we're experiencing technical difficulties. Please try again or contact <a href='%s' target='_blank'>support</a>.", "templately"), 'https://wpdeveloper.com/support');
				$this->throw($error_message);
			} else {
				$this->throw($unzip->get_error_message());
			}
		}

		if ($unzip) {
			unlink($this->filePath);
		}
	}

	/**
	 * Unzip a specified ZIP file to a location on the Filesystem.
	 *
	 * @param string $file Full path and filename of ZIP archive.
	 * @param string $to Full path on the filesystem to extract archive to.
	 * @return true|WP_Error True on success, WP_Error on failure.
	 */
	function unzip_file($file, $to) {
		try {
			$zip = new \ZipArchive;

			$res = $zip->open($file);
			if ($res === TRUE) {
				$zip->extractTo($to);
				$zip->close();

				return true;
			}
		} catch (\Throwable $th) {
			return new \WP_Error('exception_caught', $th->getMessage());
		}

		if (isset($zip)) {
			return new \WP_Error('zip_error_' . $zip->status, $zip->getStatusString());
		} else {
			return new \WP_Error('unknown_error', '');
		}
	}

	/**
	 * @throws Exception
	 */
	private function read_manifest($dir_path) {
		$manifest_content = file_get_contents($dir_path . 'manifest.json');
		if (empty($manifest_content)) {
			$this->throw(__('Cannot be imported, as the manifest file is corrupted', 'templately'));
		}

		$manifest_content = json_decode($manifest_content, true);
		$this->removeLog('temp');

		return $manifest_content;
		// TODO: Read & Broadcast the LOG for waiting list
		// $this->sse_log( 'plugin', 'Installing required plugins', '--', 'updateLog', 'processing' );
		// // $this->sse_log( 'extra-content', 'Import Extra Contents (i.e: Forms)', '--', 'updateLog', 'processing' );
		// $this->sse_log( 'templates', 'Import Templates (i.e: Header, Footer etc)', '--', 'updateLog', 'processing' );
		// // $this->sse_log( 'content', 'Import Pages, Posts etc', '--', 'updateLog', 'processing' );
		// $this->sse_log( 'wp-content', 'Importing Pages, Posts, Navigation, etc', '--', 'updateLog', 'processing' );
		// $this->sse_log( 'finalize', 'Finalizing Your Imports', '--', 'updateLog', 'processing' );
	}

	private function skipped_plugin(): bool {
		return empty($this->request_params['plugins']) || !is_array($this->request_params['plugins']);
	}


	private function before_install_hook() {
		// remove_all_actions( 'wp_loaded' );
		// remove_all_actions( 'after_setup_theme' );
		// remove_all_actions( 'plugins_loaded' );
		// remove_all_actions( 'init' );

		// making sure so that no redirection happens during plugin installation and hooks triggered bellow.
		add_filter('wp_redirect', '__return_false', 999);
	}

	private function after_install_hook() {
		// do_action( 'wp_loaded' );
		// do_action( 'after_setup_theme' );
		// do_action( 'plugins_loaded' );
		// do_action( 'init' );
	}

	/**
	 * @throws Exception
	 */
	private function start_content_import() {
		add_filter('upload_mimes', array($this, 'allow_svg_upload'));
		add_filter('elementor/files/allow_unfiltered_upload', '__return_true');

		$request_params = $this->get_session_data();

		$import        = new Import(array_merge($request_params, [
			'origin'   => $this,
			'manifest' => $this->manifest,
		]));
		$imported_data = $import->run();

		$import_status = $this->handle_import_status('success');

		update_option('templately_flush_rewrite_rules', true, false);

		$this->sse_message([
			'type'    => 'complete',
			'action'  => 'complete',
			'results' => $this->normalize_imported_data($imported_data)
		]);

		update_user_meta(get_current_user_id(), 'templately_fsi_pack_id', $request_params["id"]);
		if(!empty($import_status['hasFeedback'])){
			update_user_meta(get_current_user_id(), 'templately_fsi_complete', 'done');
		}
		else{
			update_user_meta(get_current_user_id(), 'templately_fsi_complete', true);
		}
	}

	private function normalize_imported_data($data) {
		$attachments        = !empty($data['attachments']['succeed']) ? count($data['attachments']['succeed']) : 0;
		$attachments_fail   = !empty($data['attachments']['failed']) ? count($data['attachments']['failed']) : 0;
		$attachments_errors = !empty($data['attachments_errors']) ? $data['attachments_errors'] : [];
		$templates          = !empty($data['templates']['succeed']) ? count($data['templates']['succeed']) : 0;
		$template_types     = !empty($data['templates']['template_types']) ? $data['templates']['template_types'] : [];
		$dependency_data    = !empty($data['dependency_data']) ? $data['dependency_data'] : [];

		$post_types = [];
		$content_templates = [];
		if (!empty($data['content']) && is_array($data['content'])) {
			foreach ($data['content'] as $type => $type_data) {
				$content_templates[$type] = !empty($type_data['succeed']) ? count($type_data['succeed']) : 0;
				$post_types[] = $this->get_post_type_label_by_slug($type);
			}
		}

		$contents = [];
		if (!empty($data['wp-content']) && is_array($data['wp-content'])) {
			foreach ($data['wp-content'] as $type => $type_data) {
				$contents[$type] = !empty($type_data['succeed']) ? count($type_data['succeed']) : 0;
				if (!in_array($type, ['wp_navigation', 'nav_menu_item'])) {
					$post_types[] = $this->get_post_type_label_by_slug($type);
				}
			}
		}

		Helper::log($data);

		return [
			'attachments'        => $attachments,
			'attachments_fail'   => $attachments_fail,
			'attachments_errors' => $attachments_errors,
			'templates'          => $templates,
			'contents'           => $content_templates,
			'wp-content'         => $contents,
			'post_types'         => $post_types,
			'template_types'     => $template_types,
			'dependency_data'    => $dependency_data,
		];
	}

	public function get_request_params() {
		return $this->request_params;
	}

	private function revert() {
		// $request = $this->get_request_params();
		// if ( isset( $request['revert'] ) && $request['revert'] ) {
		// 	// TODO: Implement the Revert Process.
		// }
	}

	public function redirect_for_archives($link, $post_id) {
		$archive_settings = get_option('templately_post_archive');
		if (!empty($archive_settings) && intval($archive_settings['post_id']) === intval($post_id)) {
			$link = str_replace($post_id, $archive_settings['archive_id'], $link);
		}

		return $link;
	}

	public function allow_svg_upload($mimes) {
		// Allow SVG
		$mimes['svg'] = 'image/svg+xml';
		return $mimes;
	}

	public function register_shutdown() {
		$status     = connection_status();
		$last_error = error_get_last();
		if ($last_error && ($last_error['type'] === E_ERROR || $last_error['type'] === E_CORE_ERROR || $last_error['type'] === E_COMPILE_ERROR || $last_error['type'] === E_USER_ERROR)) {
			if (!empty($last_error['message'])) {
				$full_message = $last_error['message'];
				// Extract the first line from the error message
				$firstLine = strtok($full_message, "\n");

				// Remove absolute paths by replacing the WordPress directory path with a placeholder
				$error_message = str_replace(ABSPATH, 'ABSPATH/', $firstLine);
			} else {
				// Generic error message
				$error_message = sprintf(__("It seems we're experiencing technical difficulties. Please try again or contact <a href='%s' target='_blank'>support</a>.", "templately"), 'https://wpdeveloper.com/support');
			}


			$this->handle_import_status('failed', $error_message);
			// Handle the error, e.g. log it or display a message to the user
			$this->sse_message([
				'action'   => 'error',
				'status'   => 'error',
				'type'     => "error",
				'retry'    => true,
				'title'    => __("Oops!", "templately"),
				'message'  => $error_message,
				// 'position' => 'plugin',
				// 'progress' => '--',
			]);
		}

		$this->debug_log("Shutdown:.....");
		$this->debug_log("connection_status: " . $this->getConnectionStatusText());
		$this->debug_log($last_error);
	}

	public function handle_import_status($status, $description = '') {
		if ($this->is_import_status_handled === $status) {
			Helper::log("Import status already handled: $status");
			return null;
		}
		$this->is_import_status_handled = $status;

		$download_key = $this->download_key;

		$headers = [
			'Content-Type'     => 'application/json',
			'Authorization'    => 'Bearer ' . $this->api_key,
			'download_key'     => $download_key,
			'download-key'     => $download_key,
			'x-templately-ip'  => Helper::get_ip(),
			'x-templately-url' => home_url('/'),
		];

		$args = [
			'headers' => $headers,
		];


		if ($status === 'success') {
			$url          = $this->get_api_url("v1", 'import/success');
			$args['body'] = json_encode(['type' => 'pack']);
			$response     = wp_remote_post($url, $args);
		} elseif ($status === 'failed') {
			$url          = $this->get_api_url("v1", 'import/failed');
			$args['body'] = json_encode(['type' => 'pack', 'description' => $description ?: "Something Went wrong....."]);
			$response     = wp_remote_post($url, $args);
		}

		Helper::log($response);

		if (is_wp_error($response)) {
			// Handle error
			Helper::log($response->get_error_message());
		} else {

			$this->update_session_data([
				'is_import_status_handled' => $this->is_import_status_handled,
			]);
			// Handle success
			$body = wp_remote_retrieve_body($response);
			$data = json_decode($body, true);
			// Do something with $body
			return $data;
		}

		return null;
	}

	protected function getConnectionStatusText() {
		$status = connection_status();
		switch ($status) {
			case CONNECTION_NORMAL:
				return "Normal";
			case CONNECTION_ABORTED:
				return "Aborted";
			case CONNECTION_TIMEOUT:
				return "Timeout";
			default:
				return "Unknown";
		}
	}

	protected function get_post_type_label_by_slug($slug) {
		$post_type_obj = get_post_type_object($slug);
		if ($post_type_obj) {
			return $post_type_obj->label;
		}
		return null;
	}

	public function import_info() {

		$platform = isset($_GET['platform']) ? $_GET['platform'] : 'elementor';
		$id       = isset($_GET['id']) ? intval($_GET['id']) : 0;

		$response = wp_remote_get($this->info_get_api_url($id), [
			'timeout' => 30,
			'headers' => [
				'Authorization'        => 'Bearer ' . $this->api_key,
				'x-templately-ip'      => Helper::get_ip(),
				'x-templately-url'     => home_url('/'),
				'x-templately-version' => TEMPLATELY_VERSION,
			]
		]);

		if (is_wp_error($response)) {
			wp_send_json_error($response->get_error_message());
			return;
		}
		// If the response code is not 200, return the error message
		if (wp_remote_retrieve_response_code($response) != 200) {
			wp_send_json_error(json_decode(wp_remote_retrieve_body($response)), wp_remote_retrieve_response_code($response));
			return;
		}
		// If the response body is JSON and it contains an error, return the error message
		// Retrieve Data from Response Body.
		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		if (isset($data['error'])) {
			wp_send_json_error($data['error']);
			return;
		}

		if (isset($data['data']['manifest'])) {
			$data['data']['manifest'] = json_decode($data['data']['manifest'], true);
		}
		if (isset($data['data']['settings'])) {
			$data['data']['settings'] = json_decode($data['data']['settings'], true);
		}

		// Return the response body
		wp_send_json($data);
	}

	public function update_imported_list($type, $id) {
		$imported_list = get_option('templately_fsi_imported_list', []);
		$imported_list[$type][] = $id;
		update_option('templately_fsi_imported_list', $imported_list);
	}

	/**
	 *
	 *
	 * @return void
	 */
	protected function add_revert_hooks() {
		add_action('wp_insert_post', function ($post_id) {
			$this->update_imported_list('posts', $post_id);
		});
		add_action('add_attachment', function ($post_id) {
			$this->update_imported_list('attachment', $post_id);
		});
		add_action('created_term', function ($term_id, $tt_id, $taxonomy, $args) {
			$this->update_imported_list('term', [$term_id, $taxonomy]);
		}, 10, 4);
		add_action('registered_taxonomy', function ($taxonomy, $object_type, $taxonomy_object) {
			$this->update_imported_list('taxonomy', $taxonomy);
		}, 10, 3);
		add_action('fluentform/form_imported', function ($formId){
			$this->update_imported_list('fluentform', $formId);
		}, 10, 1);
	}

	public static function has_revert(){
		$options = Utils::get_backup_options();
		$imported_list = get_option('templately_fsi_imported_list', []);
		if(!empty($options) || !empty($imported_list)){
			return true;
		}
		return false;
	}

	public function import_revert() {

		// // Get the nonce value from the request (usually from $_POST or $_GET)
		// $received_nonce = isset($_REQUEST['_wpnonce']) ? $_REQUEST['_wpnonce'] : '';

		// // Verify the nonce using wp_verify_nonce()
		// $verified = wp_verify_nonce($received_nonce, 'templately_pack_import_revert_nonce');

		// if (!$verified) {
		// 	wp_send_json_error("Nonce not verified.");
		// }

		delete_option('templately_import_platform');

		$option_active         = null;
		$options_deleted       = false;
		$imported_list_deleted = false;
		$options               = Utils::get_backup_options();
		$status_args           = [ 'post_type' => 'templately_library' ];
		$all_post_url          = add_query_arg( [
			"page" => "templately_settings",
			"path" => "settings/elementor/miscellaneous",
		], admin_url('admin.php' ));
		// wp_send_json_success([$options]);

		if(class_exists('Elementor\Plugin')){
			$kits_manager  = Plugin::$instance->kits_manager;
			$option_active = $kits_manager::OPTION_ACTIVE;
			$kit           = $kits_manager->get_active_kit();

			if ( ! $kit->get_id() ) {
				$kit = $kits_manager->create_default();
				update_option( $kits_manager::OPTION_ACTIVE, $kit );
			}
		}


		if (!empty($options) && is_array($options)) {
			foreach ($options as $key => $value) {
				if ('stylesheet' === $key) {
					if (get_option('stylesheet') !== $value) {
						switch_theme($value);
					}
				} else if($option_active === $key && class_exists('Elementor\Plugin')) {
					$kits_manager->revert( (int) $kits_manager->get_active_id(), (int) $value, 0 );
					$kit      = $kits_manager->get_active_kit();
					$settings = $kit->get_data('settings');
					if ( isset( $settings['site_logo'] ) ) {
						set_theme_mod( 'custom_logo', $settings['site_logo']['id'] );
					}
				} else {
					update_option($key, $value);
				}
				delete_option("__templately_$key");
				$options_deleted = true;
			}
		}

		$imported_list = get_option('templately_fsi_imported_list', []);
		if (!empty($imported_list) && is_array($imported_list)) {
			$_GET['force_delete_kit'] = 1; // Fallback GET Ready!
			foreach ($imported_list as $type => $list) {
				if (empty($list) || !is_array($list)) {
					continue;
				}
				// Loop through each item ID and delete it
				foreach ($list as $key => $item_id) {
					switch ($type) {
						case 'posts':
							// making sure default kit don't get deleted.
							if($option_active && isset($options[$option_active]) && $options[$option_active] == $item_id){
								break;
							}
							wp_delete_post($item_id, true); // Set true for permanent deletion
							break;
						case 'attachment':
							wp_delete_attachment($item_id, true); // Set true for permanent deletion
							break;
						case 'term':
							list($term_id, $taxonomy) = $item_id;
							wp_delete_term($term_id, $taxonomy); // Use corresponding taxonomy
							break;
						case 'taxonomy':
							// Taxonomies cannot be directly deleted. Consider de-registering it.
							break;
						case 'fluentform':
							if(class_exists('\FluentForm\App\Models\Form')){
								\FluentForm\App\Models\Form::remove($item_id);
							}
							break;
					}
				}
			}

			$imported_list_deleted = true;
			delete_option('templately_fsi_imported_list');
		}


		if($options_deleted || $imported_list_deleted){
			sleep(5);
			wp_send_json_success([ 'options' => $options_deleted, 'imported_list' => $imported_list_deleted, 'site_url' => home_url(), 'redirect' => $all_post_url ]);
		}

		wp_send_json_error([ 'options' => $options_deleted, 'imported_list' => $imported_list_deleted, 'site_url' => home_url() ]);
	}

	public function google_font() {
		$result = get_transient('templately-google-fonts');

		if (false == $result) {
			$response = wp_remote_get($this->get_api_url('v2', 'google-font'), [
				'timeout' => 30,
				'headers' => [
					'Authorization'        => 'Bearer ' . $this->api_key,
					'x-templately-ip'      => Helper::get_ip(),
					'x-templately-url'     => home_url( '/' ),
					'x-templately-version' => TEMPLATELY_VERSION,
				]
			]);

			if (is_wp_error($response)) {
				wp_send_json_error($response->get_error_message());
			}

			if (wp_remote_retrieve_response_code($response) != 200) {
				wp_send_json_error('API request failed with response code ' . wp_remote_retrieve_response_code($response), wp_remote_retrieve_response_code($response));
			}

			$body = wp_remote_retrieve_body($response);
			$data = json_decode($body, true);

			if (!isset($data['status']) || $data['status'] !== 'success') {
				wp_send_json_error('API response indicates failure.');
			}

			if (!isset($data['data'])) {
				wp_send_json_error('API response missing data.');
			}

			$result = $data['data'];
			set_transient('templately-google-fonts', $result, DAY_IN_SECONDS);
		}

		wp_send_json_success($result);
	}


}
