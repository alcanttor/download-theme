/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

jQuery(document).ready( function($){
    jQuery( ".dtwap-dismissible" ).click(function(){
        var notice_name = jQuery( this ).attr( 'id' );
        var data        = {'action': 'dtwap_dismissible_notice','notice_name': notice_name,'nonce':dtwap_object.nonce};
        jQuery.post(
            dtwap_object.ajax_url,
            data,
            function(response) {

            }
        );

    }); 
});
