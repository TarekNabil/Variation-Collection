jQuery( document ).ready( function( $ ) {

    $( ".single_variation_wrap" ).on( "show_variation", function ( event, variation ) {
    	var variation_id = variation.variation_id;
      	$('.custom_variations').hide();
        $('.custom-variation-for-'+ variation_id).show();
        $('.custom-variation-for-'+ variation_id+' ul li').css("visibility", "inherit").css("opacity", "inherit");
    });
});
