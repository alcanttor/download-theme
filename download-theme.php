<?php
/*
Plugin Name: Download Theme
Plugin URI: http://metagauss.com
Description: Download any theme from your wordpress admin panel's Appearance page by just one click!
Version: 1.1.2
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
	define( 'DTWAP_VERSION', '1.1.2' ); //Plugin version number
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
        wp_register_style( 'dt-form', DTWAP_URL.'css/dt-form.css', array(), DTWAP_VERSION );
	wp_enqueue_style( 'dt-form' );
		
        wp_localize_script( 'dtwap-dismiss-script', 'dtwap_object', array('ajax_url' => admin_url( 'admin-ajax.php' ), 'nonce'=> wp_create_nonce('dtwap-themes')) );
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
        $siteurl = sanitize_url($_POST['adminWebsite']);
        $message = sanitize_textarea_field($_POST['message']);
        $subject = 'WordPress Support By Download Theme';
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $body = '<p></p>';
        $body .= '<p><strong>Email:</strong> ' . $admin_email . '</p>';
        $body .= '<p><strong>Website:</strong> ' . $siteurl . '</p>';
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
    $siteurl = get_site_url();
    if ( $notice_name == '1' ) {
            return;
    }
    
    $expiration = get_option('dtwap_dismissible_plugin_expiration', 0);
    if (time() < $expiration) 
    {
        return;
    }
   ?>
   <div class="notice notice-info is-dismissible dtwap-dismissible" id="dtwap_dismissible_plugin">
       <p><?php esc_html_e( "Hello! The Download Theme Plugin team now offers comprehensive WordPress support, ready to fix any WordPress issue you have at a single, fixed cost.", 'download-theme' ); ?> <!-- <a href="#" id="dtwap-noticeBtnhide7"> 7 days</a><a href="#" id="dtwap-noticeBtnhide15"> 15 days</a>--> </p>
       <p>
           <a href="#" id="dtwap-noticeBtn"> Get Help Now!</a><br/>
           <a href="#" class="dtwap-noticeBookmark">Bookmark us</a><br/>
           <a href="#" id="dtwap-noticeBtnhidenever">Close</a>
           
       </p>
   </div>



   <?php
}

add_action('admin_footer', 'dtwap_get_help_modal');

function dtwap_get_help_modal()
{
    $admin_email = get_option('admin_email');
    $siteurl = get_site_url();
    ?>
<!-- The Modal -->
<div id="dtwap-notice-modal" class="dtwap-notice-modal">

    <!-- Modal content -->
    <div class="dtwap-notice-modal-content">
        <span class="dtwap-notice-modal-close">&times;</span>
            <form id="dtwap-inquiryForm" class="dtwap-form-wrap">
                <div class="dtwap-form-head-wrap">
                <div class="dtwap-form-heading"><?php esc_html_e('Fix Your WordPress Problem in Minutes!','download-theme');?></div>
                <div class="dtwap-form-subheading">WordPress Support by Download Theme plugin</div>
                </div>
                <div class="dtwap-form-group">
                <label class="dtwap-form-label" for="adminEmail"><?php esc_html_e('Email','download-theme');?>:</label>
                <input type="email" id="dtwap-adminEmail" placeholder="<?php esc_html_e('Enter Email address','download-theme');?>" class="dtwap-form-control" name="adminEmail" value="<?php esc_attr_e($admin_email); ?>" disabled>
                <div class="dtwap-change-email"><a href="#" id="dtwap-change-email-btn"><?php esc_html_e('Change Email','download-theme');?></a></div>
                <div class="dtwap-error dtwap-error-email"></div>
                </div>
                  <div class="dtwap-form-group">
                 <label class="dtwap-form-label" for="website"><?php esc_html_e('Website','download-theme');?>:</label>
                 <input type="text" id="dtwap-adminWebsite" class="dtwap-form-control" name="website" value="<?php echo esc_url($siteurl);?>" >
                 <div class="dtwap-error dtwap-error-website"></div>
                  </div>
                <div class="dtwap-form-group">
                <label class="dtwap-form-label" for="message"><?php esc_html_e('Message','download-theme');?>:</label>
                <textarea id="dtwap-message" class="dtwap-form-control"  name="message" rows="4" cols="50"></textarea>
                <div class="dtwap-error dtwap-error-message"></div>
                </div>
                
                <br><br>
                <div class="dtwap-form-submit-button"><button type="submit" class="button button-primary"><?php esc_html_e('Submit','download-theme');?></button></div>
            </form>
        
        <div class="dtwap-form-response-message" style="display: none">
            <p class="dtwap-form-response"> Thank you for your enquiry! We'll be sending you a response via email shortly. Be sure to check your junk folder to ensure you don't miss our reply.</p>
            <p class="dtwap-form-response-btn"> <button class="button button-primary dtwap-noticeBookmark" id="dtwap-noticeBookmark">Bookmark us</button>  <button class="button button-secondry dtwap-notice-modal-close" >Close</button></p>
        </div>
        
    </div>

</div>
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
	
function dtwap_dismissible_notice_temp() {
    $nonce = filter_input(INPUT_POST, 'nonce');
    if (!isset($nonce) || !wp_verify_nonce($nonce, 'dtwap-themes')) {
        die(esc_html__('Failed security check', 'download-theme'));
    }
    if (current_user_can('manage_options')) {
        if (isset($_POST['days'])) {
            if($_POST['days']=='bookmark')
            {
                 update_option('dtwap_enable_bookmark',true);
                 update_option('dtwap_dismissible_plugin', 1);
                 wp_send_json_success();
            }
            else
            {
                $days = intval($_POST['days']);
                $expiration = time() + ($days * 86400); // Calculate expiration time
                update_option('dtwap_dismissible_plugin_expiration', $expiration);
                wp_send_json_success();
            }
        }
    }
    wp_send_json_error();
    die();
}
add_action('wp_ajax_dtwap_dismissible_notice_hide', 'dtwap_dismissible_notice_temp');

/**
 * Add custom help tab to the admin screen
 */

function dtwap_custom_help_sidebar() {
    $screen = get_current_screen();
        // Add your custom link to the help sidebar
    $bookmark = get_option('dtwap_enable_bookmark', false);
    if($bookmark){
        $screen->set_help_sidebar(
            $screen->get_help_sidebar() . '<a href="#" id="dtwap-noticeBtn"> Get Help Now!</a>'
        );
    }
}
add_action('admin_head', 'dtwap_custom_help_sidebar');

function dtwap_register_settings() {
    // Register the setting
    register_setting('general', 'dtwap_enable_bookmark', array(
        'type' => 'boolean',
        'description' => 'Enable Download Theme bookmark',
        'sanitize_callback' => 'rest_sanitize_boolean',
        'default' => false,
    ));

    // Add a new section to the General Settings page
    add_settings_section(
        'dtwap_custom_section',
        'Download Theme Settings',
        'dtwap_custom_section_callback',
        'general'
    );

    // Add a new field to the new section
    add_settings_field(
        'dtwap_enable_bookmark',
        'Enable Download Theme bookmark',
        'dtwap_enable_bookmark_callback',
        'general',
        'dtwap_custom_section'
    );
}
add_action('admin_init', 'dtwap_register_settings');

function dtwap_custom_section_callback() {
    echo '<p>Download Theme settings for your site.</p>';
}

function dtwap_enable_bookmark_callback() {
    $option = get_option('dtwap_enable_bookmark',false);
    echo '<input type="checkbox" id="dtwap_enable_bookmark" name="dtwap_enable_bookmark" value="1" ' . checked(1, $option, false) . '/>';
    echo '<label for="dtwap_enable_bookmark">Enable the Download Theme bookmark</label>';
}










