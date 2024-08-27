jQuery(document).ready( function($){
	if ( $('.themes .theme .theme-actions').length > 0 ){
		$('.themes .theme .theme-actions').each( function( index, element ){
			var theme_folder  = $(this).parents('.theme').attr('data-slug');
                        var download_link = '<a href="?dtwap_download='+theme_folder+'&_wpnonce='+dtwap.dtwap_nonce+'" class="button button-primary">' +dtwap.download_title+'</a>';
			$(this).prepend(download_link);
                        var support_link = '<a href="javascript:void(0)" class="button button-primary dtwap-getFormBtn dtwap-getThemeFormBtn" id="dtwap-getFormBtn" data-themename="'+theme_folder+'"><span class="" style="padding-top:4px;"></span></a>';
			$(this).prepend(support_link);
		});
	}
	// if only single theme
	if ( $('.themes.single-theme').length > 0 ){
		if ( $('.themes.single-theme .active-theme .customize').length > 0 ){
			var theme_href = $('.themes.single-theme .active-theme .customize').attr('href');
			var href_component = decodeURIComponent(theme_href).split("&");
			var theme_folder = href_component[0].split('=')[1];
			var download_link = '<a href="?dtwap_download='+theme_folder+'&_wpnonce='+dtwap.dtwap_nonce+'" class="button button-primary dtwap_download_link">'+dtwap.download_title+'</a>';
                        var support_link = '<a href="#" class="button button-primary" id="dtwap-noticeBtn"></a>';
			$('.themes.single-theme .active-theme').each( function( index, element ){
				$(this).prepend(download_link);
                                $(this).prepend(support_link);
			});
		}
	}  
        
     // jQuery to show and hide the modal

        
       $(".dtwap-getThemeFormBtn").click(function(){
         
        //$('.theme-overlay').hide();
        var theme_name = $(this).data('themename');
        $("#theme_name").val(theme_name);
         $("#dtwap-GetHelp-modal").show();
         
            $(".dtwap-notice-modal-close").click(function(){
            $("#dtwap-GetHelp-modal").hide();
             //$('.theme-overlay').show();
        });
    
            
        });
        
        $('#dtwap-change-email-btn2').click(function(e) {
                e.preventDefault();
                $('#dtwap-adminEmail2').val('');
                $('#dtwap-adminEmail2').prop('disabled', false);
                
            });
        
        $('#dtwap-inquiryForm2').submit(function(e) {
        e.preventDefault();
        $('.dtwap-error2').html('');
        var themeName = $('#theme_name').val();
        var adminEmail = $('#dtwap-adminEmail2').val();
        var adminWebsite = $('#dtwap-adminWebsite2').val();
        var message = $('#dtwap-message2').val();
        var responseContainer = $('.dtwap-form-response-message2'); // Container for displaying messages
        var formContainer = $('#dtwap-inquiryForm2'); // Form Container
        // Email validation regex
        var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        // Website validation regex
        var urlPattern = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([/\w \.-]*)*\/?$/;

        // Validation checks
        
        if (adminEmail === '')
        {
            $('.dtwap-error-email2').html('Email is required');
            return;
        }

        if (adminWebsite === '')
        {
            $('.dtwap-error-website2').html('Website is required');
            return;
        }
        
        if (message === '')
        {
            $('.dtwap-error-message2').html('Message is required');
            return;
        }
        

        if (!emailPattern.test(adminEmail)) {
            $('.dtwap-error-email2').html('Please enter a valid email address.');
            return;
        }

        if (!urlPattern.test(adminWebsite)) {
            $('.dtwap-error-website2').html('Please enter a valid website URL.');
            return;
        }

        $.ajax({
            url: dtwap_object.ajax_url, // WordPress AJAX URL
            type: 'POST',
            data: {
                action: 'dt_send_inquiry_email',
                adminEmail: adminEmail,
                adminWebsite:adminWebsite,
                message: message,
                type:themeName
            },
            success: function(response) {
                
                console.log(responseContainer);
                   //responseContainer.html('<p class="success">' + response.data.message + '</p>');
                    //$('#dtwap-inquiryForm')[0].reset(); // Reset form fields
                    responseContainer.toggle();
                    formContainer.toggle();
                
          
                
                
                
            },
            error: function(response) {
                alert('There was an error sending your message. Please try again.');
            }
        });
    });

   
        
});