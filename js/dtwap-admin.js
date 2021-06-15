jQuery(document).ready( function($){
	
	$('.themes .theme .theme-actions').each( function( index, element ){
		
		var theme_folder  = $(this).parents('.theme').attr('aria-describedby').split(' ')[0].slice( 0, -7 );
		var download_link = '<a href="?dtwap_download='+theme_folder+'" class="button button-primary dtwap_download_link">'+dtwap.download_title+'</a>';
		
		$(this).prepend(download_link)
	});
});


// Plugin Info Modal


jQuery(document).ready(function(){
  jQuery('.dtwap-modal-view').addClass('is-active');
    jQuery('.dtwap-modal-wrap').removeClass('dtwap-popup-out');
    jQuery('.dtwap-modal-wrap').addClass('dtwap-popup-in');
    jQuery('.dtwap-modal-overlay').removeClass('dtwap-popup-overlay-fade-out');
    jQuery('.dtwap-modal-overlay').addClass('dtwap-popup-overlay-fade-in');
  
});


jQuery(document).ready(function () {
    jQuery('.dtwap-modal-close, .dtwap-modal-overlay').click(function () {
        setTimeout(function () {
            //jQuery(this).parents('.rm-modal-view').hide();
            jQuery('.dtwap-modal-view').hide();
        }, 400);
    });
    jQuery('.dtwap-modal-close, .dtwap-modal-overlay').on('click', function () {
        jQuery('.dtwap-modal-wrap').removeClass('dtwap-popup-in');
        jQuery('.dtwap-modal-wrap').addClass('dtwap-popup-out');

        jQuery('.dtwap-modal-overlay').removeClass('dtwap-popup-overlay-fade-in');
        jQuery('.dtwap-modal-overlay').addClass('dtwap-popup-overlay-fade-out');
    });






});