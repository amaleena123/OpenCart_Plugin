$(document).ready(function() {
	$('.chckbox_mpsc').on('click', function() {	
		      var n = $(this).attr('name');
		      $("#detail_"+n).toggle();

		      if( $( "#detail_"+n).css('display') == 'none' ){
		      	$("#detail_"+n+" > td input").prop('disabled', true); 
		      }else
		      	$("#detail_"+n+" > td input").removeAttr("disabled");
	});

	$('.chckbox_mpsc').each(function(){
		if($(this).is(':checked')){
	      var n = $(this).attr('name');
	      $("#detail_"+n).show();
	      $("#detail_"+n+" > td input").removeAttr("disabled");
    	}
	});
});