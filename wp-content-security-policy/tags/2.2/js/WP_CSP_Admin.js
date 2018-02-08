jQuery(document).ready(function(){
	WPCSPHandleLogAdmin();
	jQuery('#wpcsp_tabsAdmin').tabs();
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
	var Target = jQuery(this).data('target');
	
	jQuery( this ).addClass('WPCSPHiddenEntry');
	jQuery( '.btnWPCSPViewErrors', RowInfo ).removeClass('WPCSPHiddenEntry');
	jQuery( Target ).addClass('WPCSPHiddenEntry') ;
});
	

jQuery(document).on('click','.btnWPCSPConvertToV3', function() {
	var ThisButton = jQuery(this);
	var RowInfo = jQuery(this).closest('tr');
	var Target = jQuery(this).data('target');
	var InfoBox = jQuery( RowInfo ).find('.WPCSPInfoBox');
	jQuery('.WPCSPInfoBox:visible').css('display','none');

	if ( jQuery(this).hasClass('btnWPCSPConvertToV3')) {
		var CSPV3Values = "'unsafe-inline' https: 'strict-dynamic'" ;
		jQuery("#script-src").val( CSPV3Values );
		jQuery( Target ).html("script-src set to CSP v3 default values &quot;"+CSPV3Values +"&quot; - use the save button to save these changes")
	}
});
jQuery(document).on('click','.btnWPCSPViewErrors, .btnWPCSPAddSafeDomain, .btnWPCSPIgnoreDomain', function() {

	var ThisButton = jQuery(this);
	var RowInfo = jQuery(this).closest('tr');
	var Target = jQuery(this).data('target');
	var violateddirective = jQuery(RowInfo).data('violateddirective');
	var InfoBox = jQuery( RowInfo ).find('.WPCSPInfoBox');
	
	var HideCurrentRowOnSuccess = false ;
	var data = {} ;
	
	if ( jQuery(this).hasClass('btnWPCSPViewErrors')) {
		data = {
				subaction : 'getdata',
				violateddirective : violateddirective,
				blockeduri: jQuery(RowInfo).data('blockeduri')
			};
		jQuery( this ).addClass('WPCSPHiddenEntry');
		jQuery( '.btnWPCSPHideErrors', RowInfo ).removeClass('WPCSPHiddenEntry');
	}
	if ( jQuery(this).hasClass('btnWPCSPAddSafeDomain')) {
		data = {
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
				subaction : 'addIgnoreDomain',
				violateddirective : violateddirective,
				scheme: jQuery(RowInfo).find('.WPCSPBlockedURLScheme').val() ,
				domain: jQuery(RowInfo).find('.WPCSPBlockedURLDomain').val() ,
				path: jQuery(RowInfo).find('.WPCSPBlockedURLPath').val() ,
				file: jQuery(RowInfo).find('.WPCSPBlockedURLFile').val() 
			};
		HideCurrentRowOnSuccess = true ;
	}
	jQuery('.WPCSPInfoBox:visible').css('display','none');
	
	data['_wpnonce'] = WPCSP.restAdminNonce ;
	jQuery(ThisButton).fadeTo('slow',0.3);
	jQuery.ajax({
	    url: WPCSP.restAdminURL ,
	    data: data,
	    dataType: 'json',
	    type: "POST",
	    error: function( response, textStatus, errorThrown ) {  
	    	jQuery(ThisButton).fadeTo('fast',1);
	    	jQuery(InfoBox).html( textStatus + ": " + response.responseText + '<br>Please Retry.' ).css('display','block');
	    			} ,
	    success: function( response ) {
	    	jQuery(ThisButton).fadeTo('fast',1);
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
	    		jQuery( 'td', Target ).html( DisplayHTML ) ;
	    		jQuery(Target).removeClass('WPCSPHiddenEntry');
	    		if ( HideCurrentRowOnSuccess === true ) {
	        		jQuery(RowInfo).addClass('WPCSPHiddenEntry');
	    		}
	    	}
	    	else {
	    		jQuery(InfoBox).html( DisplayHTML ).css('display','block');
	    	}
	    }
	});	
});


jQuery(document).on('click','.btnWPCSPClearLogFile, .btnWPCSPTestURLChecker', function() {
	
	// This defines where the target is defined.
	var RowInfo = false ;
	var ThisButton = jQuery(this);
	
	if ( jQuery(this).hasClass('btnWPCSPClearLogFile')) {
		data = {
				subaction : 'clearLogFile'
			};
		RowInfo = jQuery(this).closest('p'); 
	}
	if ( jQuery(this).hasClass('btnWPCSPTestURLChecker')) {
		data = {
				subaction : 'TestURLChecker'
			};
		RowInfo = jQuery(this).closest('tr'); 
	}
	var Target = jQuery(this).data('target'); 
	jQuery(Target).html('').addClass('WPCSPHiddenEntry') ;
	
	if ( data !== undefined ) {
		jQuery(ThisButton).fadeTo('slow',0.3);
		data['_wpnonce'] = WPCSP.restAdminNonce ;
		
		jQuery.ajax({
		    url: WPCSP.restAdminURL ,
		    data: data,
		    dataType: 'json',
		    type: "POST",
		    error: function( response, textStatus, errorThrown ) {
		    	jQuery(ThisButton).fadeTo('fast',1);
		    	jQuery(Target).html( textStatus + ": " + response.responseText + '<br>Please Retry.' ).removeClass('WPCSPHiddenEntry');
		    	} ,
		    success: function( response ) {
		    	jQuery(ThisButton).fadeTo('fast',1);
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
