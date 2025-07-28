<?php

namespace Templately\Core\Importer;

use Templately\Core\Importer\Utils\LogHandler;
use Templately\Core\Importer\Utils\Utils;
use Templately\Utils\Helper;

trait LogHelper {
	private $log_types = [
		''
	];

	public function sse_log( $type, $message, $progress = 1, $action = 'updateLog', $status = null ) {
		$data = [
			'action'   => $action,
			'type'     => $type,
			'progress' => $progress,
			'message'  => $message
		];

		if ( $progress == 100 && $status == null ) {
			$data['status'] = 'complete';
		} elseif ( $status != null ) {
			$data['status'] = $status;
		}

		$this->sse_message( $data );
	}

	public function removeLog( $type ) {
		$this->sse_message( [
			'action'   => 'removeLog',
			'type'     => $type,
			'progress' => 100
		] );
	}

	public function sse_message( $data ) {
		// Log the data into debug log file
		$this->sse_log_file( $data );

		if(Helper::should_flush()){
			echo "event: message\n";
			echo 'data: ' . wp_json_encode( $data ) . "\n\n";

			// Extra padding.
			echo esc_html( ':' . str_repeat( ' ', 2048 ) . "\n\n" );

			flush();
			if (ob_get_level() > 0) {
				ob_flush();
			}
		}
		// else {
		// 	$log = get_option( 'templately_fsi_log' ) ?: [];
		// 	$log[] = $data;
		// 	update_option( 'templately_fsi_log', $log );
		// }
		// else if($data['action'] === 'complete' || $data['action'] === 'downloadComplete' || $data['action'] === 'error'){
		// 	wp_send_json( $data );
		// }
	}

	/**
	 * Printing Error Logs in debug.log file or in option.
	 *
	 * @param mixed $log
	 * @return void
	 */
	public function sse_log_file( $log ){
        $request_params = Utils::get_session_data_by_id();

        if (is_array($log)) {
            $log['timestamp'] = date('Y-m-d H:i:s');
        }

        if (isset($request_params['log_type']) && $request_params['log_type'] == 'file') {
			LogHandler::sse_log_file($log);
        } else {
            $_log = get_option('templately_fsi_log') ?: [];
            $_log[] = $log;
            update_option('templately_fsi_log', $_log, false);
        }
	}
	/**
	 * Printing Error Logs in debug.log file.
	 *
	 * @param mixed $log
	 * @return void
	 */
	public function debug_log( $log ){
		if ( defined('TEMPLATELY_EVENT_LOG') && TEMPLATELY_EVENT_LOG === true ) {

			if ( is_array( $log ) || is_object( $log ) ) {
				error_log( print_r( $log, true ) );
			} else if($log) {
				error_log( $log );
			}

		}
	}
}