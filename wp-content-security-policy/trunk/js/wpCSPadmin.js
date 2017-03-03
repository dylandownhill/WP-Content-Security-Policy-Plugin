jQuery(document).ready(function(){
	WPCSPHandleLogAdmin();
});
jQuery(document).on('change','.WPCSPBlockedURLPath, .WPCSPBlockedURLFile', WPCSPHandleLogAdmin 	);


function WPCSPHandleLogAdmin( ) {
	jQuery('.WPCSPBlockedURLPath').each( function(  key, element  ) {
		var RowInfo = jQuery(this).closest('tr');
		
		var SchemeValue = jQuery( RowInfo ).find('.WPCSPBlockedURLScheme').val();
		var DomainValue = jQuery( RowInfo ).find('.WPCSPBlockedURLDomain').val();
		var PathValue = jQuery( RowInfo ).find('.WPCSPBlockedURLPath').val();
		var FileValue = jQuery( RowInfo ).find('.WPCSPBlockedURLFile').val();

		//Clear down old errors and hide the entries.
		jQuery( RowInfo ).find('.WPCSPBlockedURLPathError, .WPCSPBlockedURLFileError').html('').addClass('WPCSPHiddenEntry') ;
		
		if ( DomainValue == '' && PathValue != '' ) {
			jQuery( RowInfo ).find('.WPCSPBlockedURLPath').val( '' );
			jQuery( RowInfo ).find('.WPCSPBlockedURLPathError').html('Path setting needs a domain.').removeClass('WPCSPHiddenEntry');
		}
		if ( DomainValue == '' && FileValue != '' ) {
			jQuery( RowInfo ).find('.WPCSPBlockedURLFile').val( '' );
			jQuery( RowInfo ).find('.WPCSPBlockedURLFileError').html('File setting needs a domain.').removeClass('WPCSPHiddenEntry');
		}
		if ( PathValue == '' && FileValue != '' ) {
			jQuery( RowInfo ).find('.WPCSPBlockedURLFile').val( '' );
			jQuery( RowInfo ).find('.WPCSPBlockedURLFileError').html('File setting needs a path.').removeClass('WPCSPHiddenEntry');
		}
	});
}



jQuery(document).on('click','.btnWPCSPHideErrors', function() {
	var RowInfo = jQuery(this).closest('tr');
	var Target = jQuery(RowInfo).data('target');
	
	jQuery( this ).addClass('WPCSPHiddenEntry');
	jQuery( '.btnWPCSPViewErrors', RowInfo ).removeClass('WPCSPHiddenEntry');
	jQuery( Target ).addClass('WPCSPHiddenEntry') ;
});
	
jQuery(document).on('click','.btnWPCSPViewErrors, .btnWPCSPAddSafeDomain, .btnWPCSPIgnoreDomain', function() {
	
	var RowInfo = jQuery(this).closest('tr');
	var Target = jQuery(RowInfo).data('target');
	var violateddirective = jQuery(RowInfo).data('violateddirective');
	var HideCurrentRowOnSuccess = false ;
	
	if ( jQuery(this).hasClass('btnWPCSPViewErrors')) {
		data = {
				action : 'WPCSPAjax',
				subaction : 'getdata',
				violateddirective : violateddirective,
				blockeduri: jQuery(RowInfo).data('blockeduri')
			};
		jQuery( this ).addClass('WPCSPHiddenEntry');
		jQuery( '.btnWPCSPHideErrors', RowInfo ).removeClass('WPCSPHiddenEntry');
	}
	if ( jQuery(this).hasClass('btnWPCSPAddSafeDomain')) {
		data = {
				action : 'WPCSPAjax',
				subaction : 'addSafeDomain',
				violateddirective : violateddirective,
				scheme: jQuery(RowInfo).find('.WPCSPBlockedURLScheme').val() ,
				domain: jQuery(RowInfo).find('.WPCSPBlockedURLDomain').val() ,
				path: jQuery(RowInfo).find('.WPCSPBlockedURLPath').val() ,
				file: jQuery(RowInfo).find('.WPCSPBlockedURLFile').val() 
			};
		HideCurrentRowOnSuccess = true ;
	}
	if ( jQuery(this).hasClass('btnWPCSPIgnoreDomain')) {
		data = {
				action : 'WPCSPAjax',
				subaction : 'addIgnoreDomain',
				violateddirective : violateddirective,
				scheme: jQuery(RowInfo).find('.WPCSPBlockedURLScheme').val() ,
				domain: jQuery(RowInfo).find('.WPCSPBlockedURLDomain').val() ,
				path: jQuery(RowInfo).find('.WPCSPBlockedURLPath').val() ,
				file: jQuery(RowInfo).find('.WPCSPBlockedURLFile').val() 
			};
		HideCurrentRowOnSuccess = true ;
	}
	
	jQuery.ajax({
	    url: WPCSP.ajaxurl,
	    data: data,
	    dataType: 'json',
	    type: "POST",
	    error: function() {  
	    			} ,
	    success: function( response ) {
	    	var DisplayHTML = '' ;
	    	if ( response['html'] !== '' ){
	    		DisplayHTML = "<p>" + response['html'] + "</p>";
	    	}
	    	if ( response['success'] === true ){
	    		var NewTable = "" ;
	    		jQuery.each( response['data'], function( FilterOption, RowData ) {

	    			NewTable += "<tr><td class='tdWPCSPReferrer'>" + RowData['document_uri'] + "</td>" +
	    							"<td class='tdWPCSPUserAgent'>" + RowData['useragent'] + "</td>" +
	    							"<td class='tdWPCSPNumErrors'>" + RowData['numerrors'] + "</td>"+
	    							"</tr>";
	    		});
	    		if ( NewTable != '' ) {
		    		NewTable = "<table><thead><tr><td class='tdWPCSPReferrer'>URL Affected</td><td class='tdWPCSPUserAgent'>UserAgent</td><td class='tdWPCSPNumErrors'>Count</td></tr></thead>" + 
		    					NewTable + "</tbody></table>";
		    		DisplayHTML += NewTable ;
	    		}
	    	}
    		jQuery( 'td', Target ).html( DisplayHTML ) ;
    		jQuery(Target).removeClass('WPCSPHiddenEntry');
    		if ( HideCurrentRowOnSuccess === true ) {
        		jQuery(RowInfo).addClass('WPCSPHiddenEntry');
    		}
	    }
	});	
});


jQuery(document).on('click','.btnWPCSPClearLogFile, .btnWPCSPTestURLChecker', function() {
	
	// This defines where the target is defined.
	var RowInfo = false ;
	
	if ( jQuery(this).hasClass('btnWPCSPClearLogFile')) {
		data = {
				action : 'WPCSPAjax',
				subaction : 'clearLogFile'
			};
		RowInfo = jQuery(this).closest('p'); 
		Target = jQuery(RowInfo).data('target'); 
	}
	if ( jQuery(this).hasClass('btnWPCSPTestURLChecker')) {
		data = {
				action : 'WPCSPAjax',
				subaction : 'TestURLChecker'
			};
		RowInfo = jQuery(this).closest('tr'); 
	}
	var Target = jQuery(RowInfo).data('target'); 
	if ( data !== undefined ) {
		jQuery.ajax({
		    url: WPCSP.ajaxurl,
		    data: data,
		    dataType: 'json',
		    type: "POST",
		    error: function() {  
		    			} ,
		    success: function( response ) {
		    	var DisplayHTML = '' ;
		    	if ( response['html'] !== '' ){
		    		DisplayHTML = "<p>" + response['html'] + "</p>";
		    	}
	    		jQuery( Target ).html( DisplayHTML ).removeClass('WPCSPHiddenEntry');
	    		jQuery('.wpcsp-logoferrors').fadeTo("fast",0).html();
		    }
		});
	}
});
