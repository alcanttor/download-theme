/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

jQuery(document).ready( function($){
//    jQuery( ".dtwap-dismissible" ).click(function(){
//        var notice_name = jQuery( this ).attr( 'id' );
//        var data = {'action': 'dtwap_dismissible_notice','notice_name': notice_name,'nonce':dtwap_object.nonce};
//        jQuery.post(
//            dtwap_object.ajax_url,
//            data,
//            function(response) {
//
//            }
//        );
//
//    }); 
    
     // Open the modal
        $("#dtwap-noticeBtn, #dtwap-getHelpBtn").click(function(){
            $("#dtwap-notice-modal").show();
            $("#dtwap-inquiryForm").show();
            $(".dtwap-form-response-message").hide();
            
        });

        // Close the modal when the user clicks on <span> (x)
        $(".dtwap-notice-modal-close").click(function(){
            $("#dtwap-notice-modal").hide();
        });
        
        //Close the modal when user click on bookmark
        
        $("#dtwap-noticeBookmark").click(function(){
             $("#dtwap-notice-modal").hide();
        });

        // Close the modal when the user clicks anywhere outside of the modal
        $(window).click(function(event){
            if ($(event.target).is("#dtwap-notice-modal")) {
                $("#dtwap-notice-modal").hide();
            }
        });
        
//          $("#dtwap-change-email-btn").click(function(){
//                $("#dtwap-adminEmail").prop("disabled", function(i, v) { return !v; });
//           });
//           
            $('#dtwap-change-email-btn').click(function(e) {
                e.preventDefault();
                $('#dtwap-adminEmail').val('');
                $('#dtwap-adminEmail').prop('disabled', false);
                
            });
           
           // Handle form submission
    $('#dtwap-inquiryForm').submit(function(e) {
        e.preventDefault();
        $('.dtwap-error').html('');
        var adminEmail = $('#dtwap-adminEmail').val();
        var adminWebsite = $('#dtwap-adminWebsite').val();
        var message = $('#dtwap-message').val();
        var responseContainer = $('.dtwap-form-response-message'); // Container for displaying messages
        var formContainer = $('#dtwap-inquiryForm'); // Form Container
        // Email validation regex
        var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        // Website validation regex
        var urlPattern = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([/\w \.-]*)*\/?$/;

        // Validation checks
        
        if (adminEmail === '')
        {
            $('.dtwap-error-email').html('Email is required');
            return;
        }

        if (adminWebsite === '')
        {
            $('.dtwap-error-website').html('Website is required');
            return;
        }
        
        if (message === '')
        {
            $('.dtwap-error-message').html('Message is required');
            return;
        }
        

        if (!emailPattern.test(adminEmail)) {
            $('.dtwap-error-email').html('Please enter a valid email address.');
            return;
        }

        if (!urlPattern.test(adminWebsite)) {
            $('.dtwap-error-website').html('Please enter a valid website URL.');
            return;
        }

        $.ajax({
            url: dtwap_object.ajax_url, // WordPress AJAX URL
            type: 'POST',
            data: {
                action: 'dt_send_inquiry_email',
                adminEmail: adminEmail,
                adminWebsite:adminWebsite,
                message: message
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
    
     // Handle hiding notice for 7 days
    $('#dtwap-noticeBtnhide7').click(function(e) {
        e.preventDefault();
        dismissNotice(7);
    });

    // Handle hiding notice for 15 days
    $('#dtwap-noticeBtnhide15').click(function(e) {
        e.preventDefault();
        dismissNotice(15);
    });
    
    $('.dtwap-noticeBookmark').click(function(e) {
        e.preventDefault();
         dismissNotice('bookmark');
    });
    
    $('#dtwap-noticeBtnhidenever').click(function(e) {
        e.preventDefault();
        var notice_name = 'dtwap_dismissible_plugin';
        var data = {'action': 'dtwap_dismissible_notice','notice_name': notice_name,'nonce':dtwap_object.nonce};
        jQuery.post(
            dtwap_object.ajax_url,
            data,
            function(response) {
                 $('#dtwap_dismissible_plugin').hide();
            }
        );
    });
    
    

    function dismissNotice(days) {
        $.ajax({
            url: dtwap_object.ajax_url,
            type: 'POST',
            data: {
                action: 'dtwap_dismissible_notice_hide',
                days: days,
                nonce: dtwap_object.nonce
            },
            success: function(response) {
                $('.dtwap-dismissible').hide();
            },
            error: function(response) {
                alert('There was an error. Please try again.');
            }
        });
    }

    
    
});
