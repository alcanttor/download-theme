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

function download_theme_admin_notice()
{
    $notice_name = get_option( 'dtwap_dismissible_plugin', '0' );
   if ( $notice_name == '1' ) {
           return;}
   ?>
   <div class="notice notice-info is-dismissible dtwap-dismissible" id="dtwap_dismissible_plugin">
   <p><?php esc_html_e( "If you are testing multiple user profile plugins for WordPress, there's a chance that one or more of them can override ProfileGrid's functionality. If something is not working as expected, please try turning them off. A very common example is profile image upload feature not working.", 'profilegrid-user-profiles-groups-and-communities' ); ?></p>
   </div>

   <?php
}

function dtwap_dismissible_notice()
{
    $nonce = filter_input( INPUT_POST, 'nonce' );
    if ( ! isset( $nonce ) || ! wp_verify_nonce( $nonce, 'dtwap-themes' ) ) {
            die( esc_html__( 'Failed security check', 'profilegrid-user-profiles-groups-and-communities' ) );
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
	

