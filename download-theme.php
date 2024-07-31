<?php
/*
Plugin Name: Download Theme
Plugin URI: http://metagauss.com
Description: Download any theme from your wordpress admin panel's Appearance page by just one click!
Version: 1.1.1
Author: Metagauss
Author URI: https://profiles.wordpress.org/metagauss/
Text Domain: download-theme
Tested up to: 6.5
*/

/**
 * Basic plugin definitions 
 * 
 * @package Download Theme
 * @since 1.0.0
 */
if( !defined( 'DTWAP_VERSION' ) ) {
	define( 'DTWAP_VERSION', '1.1.1' ); //Plugin version number
}
if( !defined( 'DTWAP_DIR' ) ) {
  define( 'DTWAP_DIR', dirname( __FILE__ ) );			// Plugin dir
}
if( !defined( 'DTWAP_URL' ) ) {
  define( 'DTWAP_URL', plugin_dir_url( __FILE__ ) );	// Plugin url
}
if(!defined('DTWAP_PREFIX')) {
  define('DTWAP_PREFIX', 'dtwap_'); // Plugin Prefix
}

/**
 * Load text domain
 *
 * This gets the plugin ready for translation.
 *
 * @package Download Theme
 * @since 1.0.0
 */
load_plugin_textdomain( 'download-theme', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

/**
 * Enqueue styles/scripts on admin side
 * 
 * @package Download Theme
 * @since 1.0.0
 */
function dtwap_admin_scripts( $hook ){
	wp_register_script( 'dtwap-dismiss-script', DTWAP_URL.'js/dtwap-dismiss-script.js', array( 'jquery' ), DTWAP_VERSION, true );
        wp_enqueue_script( 'dtwap-dismiss-script' );
        wp_localize_script( 'dtwap-dismiss-script', 'dtwap_object', array('ajax_url' => admin_url( 'admin-ajax.php' ), 'dtwap_nonce'=> wp_create_nonce('dtwap-themes')) );
	if( $hook == 'themes.php' ){
	
		wp_register_style( 'dtwap-admin-style', DTWAP_URL.'css/dtwap-admin.css', array(), DTWAP_VERSION );
		wp_enqueue_style( 'dtwap-admin-style' );
		
		wp_register_script( 'dtwap-admin-script', DTWAP_URL.'js/dtwap-admin.js', array( 'jquery' ), DTWAP_VERSION, true );
		wp_enqueue_script( 'dtwap-admin-script' );
		wp_localize_script( 'dtwap-admin-script', 'dtwap', array('download_title' => __( 'Download', 'download-theme' ), 'dtwap_nonce'=> wp_create_nonce('dtwap-themes')) );
	}
        
        if( $hook == 'plugins.php' ){
            wp_register_style( 'download-theme-popup', DTWAP_URL.'css/download-theme-popup.css', array(), DTWAP_VERSION );
            wp_enqueue_style( 'download-theme-popup' );
                
            wp_register_script( 'download-theme-popup', DTWAP_URL.'js/download-theme-popup.js', array( 'jquery' ), DTWAP_VERSION, true );
            wp_enqueue_script( 'download-theme-popup' );
        }
        
}
add_action( 'admin_enqueue_scripts', 'dtwap_admin_scripts' );

/**
 * Download theme zip
 * 
 * @package Download Theme
 * @since 1.0.0
 */
function dtwap_download(){
	if(!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'],'dtwap-themes')){
                return;
            }
	$themes = wp_get_themes();
	
	if( is_user_logged_in() && current_user_can( 'switch_themes' ) && isset( $_GET['dtwap_download'] ) && !empty( $_GET['dtwap_download'] ) && array_key_exists( $_GET['dtwap_download'], $themes ) ){
		
		$dtwap_download = $_GET['dtwap_download'];
		$folder_path    = get_theme_root( $dtwap_download ).'/'.$dtwap_download;
		$root_path      = realpath( $folder_path );
		
		$zip = new ZipArchive();
		$zip->open( $folder_path.'.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE );
		
		$files = new RecursiveIteratorIterator(
		    new RecursiveDirectoryIterator( $root_path ),
		    RecursiveIteratorIterator::LEAVES_ONLY
		);
		
		foreach( $files as $name=>$file ){
		    
			if ( !$file->isDir() ){
		        
				$file_path	   = $file->getRealPath();
		        $relative_path = substr( $file_path, strlen( $root_path ) + 1 );
		        
		        $zip->addFile( $file_path, $relative_path );
		    }
		}
		
		$zip->close();
		
		// Download Zip
		$zip_file = $folder_path.'.zip';
		
		if( file_exists( $zip_file ) ) {
			
		    header('Content-Description: File Transfer');
		    header('Content-Type: application/octet-stream');
		    header('Content-Disposition: attachment; filename="'.basename($zip_file).'"');
		    header('Expires: 0');
		    header('Cache-Control: must-revalidate');
		    header('Pragma: public');
		    header('Content-Length: ' . filesize($zip_file));
		    header('Set-Cookie:fileLoading=true');
		    readfile($zip_file);
		    unlink($zip_file);
		    exit;
		}
	}	
}

function download_theme_popup_html()
{
    global $pagenow;
    if ( $pagenow == 'plugins.php')
    {
        if(!get_option('download_theme_popup_status'))
        {
            require_once 'download-theme-popup.php';
            add_option('download_theme_popup_status',1);
        }
    }
}
add_action( 'admin_init', 'dtwap_download' );
add_action( 'admin_footer', 'download_theme_popup_html');
add_action( 'wp_ajax_dtwap_dismissible_notice', 'dtwap_dismissible_notice' );		
add_action( 'admin_notices', 'download_theme_admin_notice' );
add_action('wp_ajax_dt_send_inquiry_email', 'dt_send_inquiry_email');

function dt_send_inquiry_email() {
    if ( isset($_POST['adminEmail']) && isset($_POST['message']) ) {
        $admin_email = sanitize_email($_POST['adminEmail']);
        $message = sanitize_textarea_field($_POST['message']);

        $subject = 'New Inquiry from Admin Notice';
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $body = '<p>You have received a new inquiry from the admin notice form.</p>';
        $body .= '<p><strong>Email:</strong> ' . $admin_email . '</p>';
        $body .= '<p><strong>Message:</strong><br>' . nl2br($message) . '</p>';

        if ( wp_mail('support@metagauss.com', $subject, $body, $headers) ) {
            wp_send_json_success(array('message' => 'Your message has been sent successfully.'));
        } else {
            wp_send_json_error(array('message' => 'Failed to send the email. Please try again.'));
        }
    } else {
        wp_send_json_error(array('message' => 'Invalid input.'));
    }

    wp_die();
}



function download_theme_admin_notice()
{
    $notice_name = get_option( 'dtwap_dismissible_plugin', '0' );
    $admin_email = get_option('admin_email');
   if ( $notice_name == '1' ) {
           return;}
   ?>
   <div class="notice notice-info is-dismissible dtwap-dismissible" id="dtwap_dismissible_plugin">
       <p><?php esc_html_e( "Download Themes team can solve your any WordPress problems at a fixed cost.", 'download-theme' ); ?> <a href="#" id="dtwap-noticeBtn"> Get Help Now!</a></p>
   </div>

<!-- The Modal -->
<div id="dtwap-notice-modal" class="dtwap-notice-modal">

    <!-- Modal content -->
    <div class="dtwap-notice-modal-content">
        <span class="dtwap-notice-modal-close">&times;</span>
                <form id="inquiryForm">
                <h2><?php esc_html_e('Get Help Now','download-theme');?></h2>
                <label class="dtwap-form-label" for="adminEmail"><?php esc_html_e('Email','download-theme');?>:</label>
                <input type="email" id="dtwap-adminEmail" class="dtwap-form-control" name="adminEmail" value="<?php esc_attr_e($admin_email); ?>" disabled>
                <div class="dtwap-change-email"><a href="#" id="dtwap-change-email-btn"><?php esc_html_e('Change Email','download-theme');?></a></div>
                <label class="dtwap-form-label" for="message"><?php esc_html_e('Message','download-theme');?>:</label>
                <textarea id="dtwap-message" class="dtwap-form-control"  name="message" rows="4" cols="50"></textarea><br><br>
                <div class="dtwap-form-submit-button"><button type="submit" class="button button-primary"><?php esc_html_e('Submit','download-theme');?></button></div>
            </form>
    </div>

</div>
<style>
    
    /* New Modal CSS--*/
    
        .dtwap-form-label{
            width: 100%;
            display: inline-block;
        }
        
        .dtwap-form-control{
            width: 100%;
        }
        
     .dtwap-change-email {
    text-align: right;
}

.dtwap-change-email a {
    text-decoration: none;
}

        .dtwap-notice-modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }
        .dtwap-notice-modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 100%;
            max-width: 600px;
            border-radius: 3px;
            }
        .dtwap-notice-modal-close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .dtwap-notice-modal-close:hover,
        .dtwap-notice-modal-close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        
        .dtwap-form-submit-button{
            text-align: right;
        }
</style>
   <?php
}

function dtwap_dismissible_notice()
{
    $nonce = filter_input( INPUT_POST, 'nonce' );
    if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'dtwap-themes' ) ) {
            die( esc_html__( 'Failed security check', 'download-theme' ) );
    }
    if ( current_user_can( 'manage_options' ) ) 
    {
        if ( isset( $_POST['notice_name'] ) ) {
                $notice_name = sanitize_text_field(wp_unslash($_POST['notice_name']));
                update_option( $notice_name, '1' );
        }
       
    }
    die;
}
	



