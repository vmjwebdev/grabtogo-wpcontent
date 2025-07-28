<?php
namespace Frontend_Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if( ! class_exists( 'PDF_Module' ) ) :

	class PDF_Module{

        function pdf_download_column( $columns ) {
            $columns['fea-pdf'] = __( 'Download PDF', 'acf-frontend-form-element' );
            return $columns;
        }

        function user_table_row( $val, $column_name, $user_id ) {
            if( $column_name == 'fea-pdf' ){
                return '<a target="_blank" class="fea-download-pdf" href="'.home_url('fea-actions?fea-download-pdf&id='.$user_id.'&object=user').'"><span class="dashicons dashicons-download"></span></a>';
            }
            return $val;
        }

        function post_table_row( $column_name, $post_id ) {
            if( $column_name == 'fea-pdf' ){
                echo '<a target="_blank" class="fea-download-pdf" href="'.home_url('fea-actions?fea-download-pdf&id='.$post_id.'&object=post').'"><span class="dashicons dashicons-download"></span></a>';
            }
        }

        function submission_table_row( $item, $column_name ) {
            if( $column_name != 'fea-pdf' ) return null;

            return sprintf( '<a target="_blank" class="fea-download-pdf" href="%s"><span class="dashicons dashicons-download"></span></a>', esc_url( $this->get_submission_url( $item['id'] ) ) );
            
        }

        function get_submission_url( $id ){
            return wp_nonce_url( home_url('fea-actions?fea-download-pdf&id='.$id.'&object=submission'), 'fea-download-pdf' );
        }

        function show_pdf( $title, $content, $context = 'i' ){
            if( $content ){
                require_once( FEAPDF_PATH.'includes/vendor/autoload.php' );  

                $filename = sanitize_title( $title ) . '.pdf';

                if( $context == 'f' ){
                    $upload = wp_upload_dir();
                    $upload_dir = $upload['basedir'];
                    $upload_dir = $upload_dir . '/pdfs/';
                    if (! is_dir($upload_dir)) {
                        mkdir( $upload_dir );
                    }
                    // Upload dir.
			        $upload_path = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir ) . DIRECTORY_SEPARATOR;
                    $filename = $upload_path . $filename; 

                }

                // reference the Dompdf namespace

                // instantiate and use the dompdf class
                $mpdf = new \Mpdf\Mpdf([
                    'default_font_size' => 9,
                    'default_font' => 'dejavusans'
                ]);
                $mpdf->SetDisplayMode('fullpage');
                
                //$mpdf->showImageErrors = true;

                $mpdf->autoLangToFont = true;
                $mpdf->WriteHTML($content);
                if( $context == 'f' ){
                    $mpdf->Output( $filename, \Mpdf\Output\Destination::FILE );
                    return $filename;
                }else{
                    $mpdf->Output( $filename, \Mpdf\Output\Destination::INLINE );
                }
            }
            return false;
        }

        function display_pdf(){
            if ( isset( $_GET['action'] ) && 'fea-download-pdf' === $_GET['action'] ) {
				if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'fea-download-pdf' ) ) {
					wp_nonce_ays( '' );
				}
            }
            
            if( empty( $_REQUEST['id'] ) || empty( $_REQUEST['object'] ) ) {
                return;
			}

            $object = $_REQUEST['object'];
            $id = $_REQUEST['id'];

            switch( $object ){
                case 'user':
                    $args = [
                        'user_id' => $id,
                        'rest'      => true,
                    ];
                    $object_id = 'user_' . $id;
                    $user = get_userdata( $id );
                    $title = $user->display_name;
                    if( empty( $title ) ){
                        $title = $user->first_name;
                    }
                    if( empty( $title ) ) $title = $user->user_login;
                break;
                case 'post':
                    $args = [
                        'post_id' => $id,
                        'rest'      => true,
                    ];
                    $object_id = $id;
                    $post = get_post( $id );
                    $title = $post->post_title;

                break;
                case 'submission':
                    global $fea_instance;
                    $submission = $fea_instance->submissions_handler->get_form( $id );

                    if( ! $submission ) {
                        echo __( 'Subsmission Not Found', 'frontend-admin' );
                        exit;
                    }
                    $title = $submission['submission_title'];

                    ob_start(); ?>
                    <head><?php echo $this->get_head( $submission, $title ); ?></head>
                    <body><?php echo $this->get_body( $submission ); ?></body>
                                
                    <?php $content = ob_get_clean();

                    if( ! empty( $submission['submission_pdf_filename'] ) ){
                        $title = $fea_instance->dynamic_values->get_dynamic_values( $submission['submission_pdf_filename'], $submission );
                    }
                    $this->show_pdf( $title, $content, 'i' );
                    exit;
                break;
                default: exit;
            }
            

            //$field_groups = acf_get_field_groups( $args );

            if( $field_groups ){
               
                $content .= '  
                <h3 align="center">'.$title.'</h3><br /><br />  
                <table border="1" cellspacing="0" cellpadding="5">';  

                foreach( $field_groups as $field_group ) {
                    $group_fields = acf_get_fields( $field_group['key'] );

                    if( $group_fields ){
                        foreach( $group_fields as $field ){
                            $field['value'] = acf_get_value( $object_id, $field );
                            $field['value'] = acf_format_value( $field['value'], $object_id, $field );

                            $content .= '<tr>  
                                <th width="20%">'.$field['label'].'</th>  
                                <th width="80%">'.fea_instance()->dynamic_values->display_field( $field ) .'</th>
                            </tr>';
                        }
                    }
                }

                $content .= '</table>';                  

            }
            exit;

        }

        function get_head( $submission, $title ){
            ob_start();
            ?>
            <head><?php wp_head(); ?></head>
            <?php
            $content = ob_get_clean();

            return apply_filters( 'frontend_admin/pdf/head', $content, $submission );
        }

        function get_body( $submission ){
            ob_start();
            ?>
            <body>
            <?php echo $this->render_pdf_content( $submission ) ?>
            </body>
            
            <?php
            $body = ob_get_clean();

            return apply_filters( 'frontend_admin/pdf/body', $body, $submission );
        }

        function render_pdf_content( $submission ){
            $content = fea_instance()->dynamic_values->get_all_fields_values( $submission );

            return apply_filters( 'frontend_admin/pdf/content', $content, $submission );
        }

        public function module_settings(){   
            $settings = array(
                'feapdf_key' => array(
                    'label' => __( 'Test', 'acf-frontend-form-element' ),
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '50.1',
                        'class' => '',
                        'id' => '',
                    ),
                ),
            ); 
            return $settings;
      
        }     

        function item_actions( $item, $type ){
            echo sprintf( '<a target="_blank" href="%s" class="dashicons dashicons-download small dark"  data-name="edit_item"></a>', esc_url( wp_nonce_url( home_url('fea-actions?fea-download-pdf&id='.$item['id'].'&object='.$type) ) ) );
        }
        function email_attachments( $attachments, $email, $form ){ 
            if( ! empty( $email['attach_submit_pdf'] ) ){  
                global $fea_instance;
            
                $title = $form['submission_title'];       
                if( ! $title ) $title = $form['title'];

                ob_start(); ?>
                <head><?php echo $this->get_head( $form, $title ); ?></head>

                <body><?php echo $this->get_body( $form ); ?></body>
                            
                <?php $content = ob_get_clean();

                if( ! empty( $form['submission_pdf_filename'] ) ){
                    $title = $fea_instance->dynamic_values->get_dynamic_values( $form['submission_pdf_filename'], $form );
                }

                $attachments[] = $this->show_pdf( $title, $content, 'f' );
            }

            return $attachments;
        }

        function unlink_attachments( $email, $form, $attachments ){   
            if( ! empty( $email['attach_submit_pdf'] ) ){  
                foreach( $attachments as $attachment ){
                    unlink( $attachment );
                }
            }
            
        }

        function email_action_setting( $fields ){
            if( $fields ){
                $fields[] = array(
                    'label'        => __( 'Attach Submission PDF', 'acf-frontend-form-element' ),
                    'instructions' => '',
                    'type'         => 'true_false',
                    'key'          => 'attach_submit_pdf',
                    'ui'           => 0,
                );
            }
            return $fields;
        }
        function pdf_file_name( $fields ){
            if( $fields ){
                $fields[] = array(
                    'key' => 'submission_pdf_filename',
                    'label' => __( 'PDF filename', 'acf-frontend-form-element' ),
                    'type' => 'text',
                    'instructions' => __( 'By default, the submission filename will be the submission title sanitized. You can dynamically set this to something that fits your needs.', 'acf-frontend-form-element' ),
                    'required' => 0,
                    'placeholder' => '[post:title]',
                    'dynamic_value_choices' => 1,
                );
            }
            return $fields;
        }

        public function pdf_redirect_option( $options ){
            $options['pdf'] = __( 'PDF', 'acf-frontend-form-element' );
            return $options;
        }

        public function pdf_redirect_url( $url, $form ){
            if( 'pdf' == $form['redirect'] ){
                $url = home_url('?fea-actions&fea-download-pdf&id='.$form['submission'].'&object=submission');
            }
            return $url;
        }
        

		public function __construct() {         
            if ( ! defined( 'FEAPDF_PATH' ) ) return;
            /* add_filter( 'manage_users_columns', [$this,'pdf_download_column'] );        
            add_filter( 'manage_users_custom_column', [$this,'user_table_row'], 10, 3 );

            add_filter( 'manage_posts_columns', [$this,'pdf_download_column'] );        
            add_filter( 'manage_posts_custom_column', [$this,'post_table_row'], 10, 2 ); */
            add_filter( 'frontend_admin/submissions/admin_columns', [$this,'pdf_download_column'] );        
            add_filter( 'frontend_admin/submissions/admin_column_render', [$this,'submission_table_row'], 10, 2 );
            add_action( 'frontend_admin/items_list/item_actions', [$this, 'item_actions'], 10, 2 );
			add_filter( 'frontend_admin/wp_mail_attachments', [$this, 'email_attachments'], 10, 3 );
			add_action( 'frontend_admin/mail_sent', [$this, 'unlink_attachments'], 10, 3 );
            add_filter( 'frontend_admin/action_settings/type=email', [$this, 'email_action_setting' ] );
            add_filter( 'frontend_admin/forms/settings_tabs/tab=submissions', [$this, 'pdf_file_name' ], 15 );

            add_filter( 'frontend_admin/forms/redirect_options', [$this, 'pdf_redirect_option' ], 15 );
            add_filter( 'frontend_admin/forms/redirect_url', [$this, 'pdf_redirect_url' ], 15, 2 );

            add_action( 'wp_loaded', [$this,'display_pdf'], 11 );

        }

       
    }

    new PDF_Module();

endif;	

