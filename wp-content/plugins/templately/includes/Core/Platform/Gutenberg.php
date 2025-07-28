<?php
namespace Templately\Core\Platform;

use Templately\API\Import;
use Templately\Core\Importer\Utils\Utils;
use Templately\Core\Platform;
use Templately\Core\Module;
use Templately\Utils\Helper;

use WP_Error;
use function get_permalink;
use function get_edit_post_link;
use function wp_insert_post;
use function wp_slash;
use function wp_unslash;
use function json_decode;

class Gutenberg extends Platform {
    /**
     * Platform ID
     * @var string
     */
    private $id = 'gutenberg';

    /**
     * Is gutenberg is active or not
     * @var boolean
     */
    public $is_gutenberg_active = false;

    /**
     * Initializing the platform and add it to module.
     */
    public function __construct(){
        Module::get_instance()->add( (object) [
            'id' => $this->id,
            'object' => $this
        ]);

        $this->hooks();
    }

    /**
     * Initializing Hooks
     * @return void
     */
    public function hooks(){
        add_action( 'enqueue_block_editor_assets', [ $this, 'scripts' ] );
        add_action( 'admin_footer', [ $this, 'print_admin_js_template' ] );
        add_action( 'wp_ajax_update_gutenberg_hide_buttons', [ $this, 'update_gutenberg_hide_buttons' ] ); // Register AJAX action
    }

    /**
     * Assets Enqueueing
     * @return void
     */
    public function scripts(){
        $this->is_gutenberg_active = true;
        templately()->assets->enqueue( 'templately-gutenberg', 'css/gutenberg.css' );
        templately()->assets->enqueue( 'templately-gutenberg', 'js/gutenberg.js' );
        templately()->admin->scripts( 'gutenberg' );
    }

    /**
     * 	Templately Button and Wrapper for Gutenberg
     *
     * @since 2.0.0
     *
     * @return void
     */
    public function print_admin_js_template() {
        if ( ! $this->is_gutenberg_active ) {
            return;
        }
        $post_type = apply_filters( 'templately_cloud_push_post_type', get_post_type());

        ?>
        <div id="templately-gutenberg"></div>
        <script id="templately-gutenberg-button-switch-mode" type="text/html">
            <div id="templately-gutenberg-buttons">
                <button id="templately-gutenberg-button" type="button" class="button button-primary button-large gutenberg-add-templately-button">
                    <i class="templately-icon" aria-hidden="true"></i>
                    <?php echo esc_html__( 'Templately', 'templately' ); ?>
                </button>
                <button id="templately-cloud-push" type="button" class="button button-primary button-large">
                    <i class="templately-cloud-icon" aria-hidden="true"></i>
                    <?php echo sprintf( __( 'Save %s in Templately', 'templately' ), $post_type ); ?>
                </button>
            </div>
        </script>
        <?php
    }

    /**
     * Determine Active UI Theme
     * @return string
     */
    public function ui_theme(){
        return 'light';
    }

    /**
     * Creating a gutenberg page
     *
     * @param integer $id
     * @param string $title
     * @param Import $importer
     *
     * @since 2.0.0
     *
     * @return array|WP_Error array on success, WP_Error on failure.
     */
	public function create_page( $id, $title, $importer = null ){
		$post_data = $inserted_ID = $importer->get_content( $id, 'gutenberg' );

		if( is_wp_error( $inserted_ID ) ) {
			return $inserted_ID;
		}

		if ( ! empty( $inserted_ID['content'] ) ) {
			$inserted_ID = wp_insert_post( array (
				'post_status'  => 'draft',
				'post_type'    => 'page',
				'post_title'   => $title,
				'post_content' => wp_slash($inserted_ID['content']),
			) );
		}

		if ( is_wp_error( $inserted_ID ) ) {
			return Helper::error(
				'import_failed',
				$inserted_ID->get_error_message(),
				'import/page',
				$inserted_ID->get_error_code()
			);
		}

		if($inserted_ID){
			$post_data['content'] = Utils::import_and_replace_attachments($post_data['content'], $inserted_ID);

			// Update the post content with the processed images
			$updated_post = array(
				'ID'           => $inserted_ID,
				'post_content' => wp_slash($post_data['content']),
			);
			wp_update_post($updated_post);
		}

		return [
			'post_id'             => $inserted_ID,
			'edit_link'           => get_edit_post_link( $inserted_ID, 'internal' ),
			'visit'               => get_permalink( $inserted_ID )
		];
	}

    /**
     * Inserts a template into the Gutenberg editor.
     *
     * @param mixed $data
     * @param int $postId
     * @return array
     */
    public function insert($data, $postId = 0) {
        $data['content'] = Utils::import_and_replace_attachments($data['content'], $postId);
        return $data;
    }

    /**
     * AJAX handler to update the option `templately-gutenberg-hide-buttons`
     */
    public function update_gutenberg_hide_buttons() {
        // Check nonce for security
        check_ajax_referer( 'templately_nonce', 'nonce' );

        // Get the new value from the AJAX request
        $hide_buttons = isset($_GET['hide_buttons']) ? sanitize_text_field($_GET['hide_buttons']) : '';

        // Update the option
        update_option('templately-gutenberg-hide-buttons', $hide_buttons);

        $hide_buttons = get_option('templately-gutenberg-hide-buttons', 'no');
        $hide_buttons = $hide_buttons === 'yes' ? 'yes' : 'no';
        // Send a response back to the JavaScript
        wp_send_json_success([
            'hide_buttons' => $hide_buttons,
        ]);
    }
}