jQuery(document).ready( function($){
	if ( $('.themes .theme .theme-actions').length > 0 ){
		$('.themes .theme .theme-actions').each( function( index, element ){
			var theme_folder  = $(this).parents('.theme').attr('data-slug');
			var download_link = '<a href="?dtwap_download='+theme_folder+'&_wpnonce='+dtwap.dtwap_nonce+'" class="dp-download-button-custom button button-primary dtwap_download_link" style="font-weight:700; width:auto;"><span class="dashicons dashicons-download"></span>' +dtwap.download_title+'</a>';
			$(this).prepend(download_link);
		});
	}
	// if only single theme
	if ( $('.themes.single-theme').length > 0 ){
		if ( $('.themes.single-theme .active-theme .customize').length > 0 ){
			var theme_href = $('.themes.single-theme .active-theme .customize').attr('href');
			var href_component = decodeURIComponent(theme_href).split("&");
			var theme_folder = href_component[0].split('=')[1];
			var download_link = '<a href="?dtwap_download='+theme_folder+'&_wpnonce='+dtwap.dtwap_nonce+'" class="button button-primary dtwap_download_link">'+dtwap.download_title+'</a>';
			$('.themes.single-theme .active-theme').each( function( index, element ){
				$(this).prepend(download_link);
			});
		}
	}  
        
     // jQuery to show and hide the modal

        // Open the modal
        $("#dtwap-noticeBtn").click(function(){
            $("#dtwap-notice-modal").show();
        });

        // Close the modal when the user clicks on <span> (x)
        $(".dtwap-notice-modal-close").click(function(){
            $("#dtwap-notice-modal").hide();
        });

        // Close the modal when the user clicks anywhere outside of the modal
        $(window).click(function(event){
            if ($(event.target).is("#dtwap-notice-modal")) {
                $("#dtwap-notice-modal").hide();
            }
        });
        
          $("#dtwap-change-email-btn").click(function(){
         $("#dtwap-adminEmail").prop("disabled", function(i, v) { return !v; });
    });
    

   
        
});