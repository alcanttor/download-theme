<?php
/*
Plugin Name: Download Theme
Plugin URI: http://metagauss.com
Description: Download any theme from your wordpress admin panel's Appearance page by just one click!
Version: 1.0.7
Author: Metagauss
Author URI: https://profiles.wordpress.org/metagauss/
Text Domain: download-theme
*/

/**
 * Basic plugin definitions 
 * 
 * @package Download Theme
 * @since 1.0.0
 */
if( !defined( 'DTWAP_VERSION' ) ) {
	define( 'DTWAP_VERSION', '1.0.7' ); //Plugin version number
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
	
	if( $hook == 'themes.php' ){
	
		wp_register_style( 'dtwap-admin-style', DTWAP_URL.'css/dtwap-admin.css', array(), DTWAP_VERSION );
		wp_enqueue_style( 'dtwap-admin-style' );
		
		wp_register_script( 'dtwap-admin-script', DTWAP_URL.'js/dtwap-admin.js', array( 'jquery' ), DTWAP_VERSION, true );
		wp_enqueue_script( 'dtwap-admin-script' );
		
		wp_localize_script( 'dtwap-admin-script', 'dtwap', array(	
																'download_title' => __( 'Download', 'download-theme' )
															) );
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
add_action( 'admin_init', 'dtwap_download' );
?>

<div class="dtwap-modal-view">
    
<div class="dtwap-modal-overlay dtwap-popup-overlay-fade-in"></div>

<div class="dtwap-modal-wrap dtwap-popup-out">
       <button class="dtwap-modal-close dtwap-close-btn " type="button" >&#10005;</button>
    <div class="dtwap_section_first-slide dpwap_section_content current">
        <div class="dtwap_row dtwap__head_row">
             <div class="dpwap-head-img">
                        <img src="<?php echo plugin_dir_url( __FILE__ ) . 'images/slide-1.png'; ?>">
                    </div>
            
        </div> 
        
        <div class="dtwap__head_body">

            <div class="dtwap_row">
                <div class="dtwap_slide_head dtwap-section-col">
                    Thank you for installing Download theme!
                </div>
            </div>

            <div class="dtwap_row">
                <div class="dtwap-section-col">
          It is now awfully easy to download any theme from your website dashboard with a single click!
                </div>
            </div>
            
            <div class="dtwap_modal_footer">

                <button class="btn btn-default next-tab dtwap-modal-close" id="second-slide-button" type="button">Close</button>
            </div>


        </div>
        
    </div>
    
</div>

</div>



