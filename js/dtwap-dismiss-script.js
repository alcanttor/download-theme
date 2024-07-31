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
    $('#inquiryForm').submit(function(e) {
        e.preventDefault();
        var adminEmail = $('#dtwap-adminEmail').val();
        var message = $('#dtwap-message').val();

        $.ajax({
            url: dtwap_object.ajax_url, // WordPress AJAX URL
            type: 'POST',
            data: {
                action: 'dt_send_inquiry_email',
                adminEmail: adminEmail,
                message: message
            },
            success: function(response) {
                alert(response.data.message);
                $('#dtwap-notice-modal').hide();
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
