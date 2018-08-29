/*
 *
 * This code was developped by teicee inspiring from some GLPI developments examples.
 * Last update: 29-AUG-2018
 * Main developper: Philippe Chauvat with the big help of Clément Rivière and Aurélien Bonanni.
 * The license for this code is the same one used for the 'domains' plugin
 *
 * This JS function gets information from whois databases and updates all the existing fields involved
 */
function checkwhois() {
    //
    // Do we request a whois database checking?
    var whoischeck = $('select[name="automatically_request_whoisdb"]').val();
    if (whoischeck == 0) return ;
    $.ajax({
	url: '/plugins/domains/ajax/whois.php?domain=' + $('input[name=name]').val(),
	beforeSend: function() {
	    //
	    // The following sentence should be added into the traduction dictionaries
	    var _loader = $('<div id=\'loadingslide\'><div class=\'loadingindicator\'>__(Requesting whois database)</div></div>');
	    $('#loadingdivid').show() ;
	    $('#loadingdivid .contents').html(_loader);
	}
    })
	.always( function() {
	    $('#loadingslide').remove();
	    $('#loadingdivid').hide() ;
	})
	.done( function(data) {
	    var result ;
	    try {
		// it seems JSON encode does not stringify things. Let's do it
		result = JSON.parse(JSON.stringify(data));
	    }
	    catch (err) {
		alert("__('Parsing JSON result failed'): " + err) ;
	    }
	    switch (result.error.value)  {
	    case 0:
		//
		// Takes for granted variable names sent back by PHP code are the ones used into the form
		jQuery.each(result,function(i,v) {
		    $('input[name=' + i + ']').val(v) ;
		    $('tr[name=tr_whois]').hide() ;
		}) ;
		break ;
	    default:
		//
		// There is a new div added into the form to display error messages
		$('td[name=whois_error_message]').html(result.error.msg) ;
		$('tr[name=tr_whois]').show() ;
		//
		// Cleaning up the date fields
		$('input[name=date_expiration]').val("") ;
		$('input[name=_date_expiration]').val("") ;
		$('input[name=date_creation]').val("") ;
		$('input[name=_date_creation]').val("") ;
		break ;
	    }
	}) ;
}
