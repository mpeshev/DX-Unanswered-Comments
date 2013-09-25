jQuery(document).ready(function($) {
	if( -1 !== window.location.href.indexOf( 'top_level=true' ) ) {
		$('.subsubsub .all a').removeClass('current');
		$('.subsubsub .non-replied-root a').addClass('current');
	} else if( -1 !== window.location.href.indexOf( 'comment_status=non_replied' ) ) {
		$('.subsubsub .all a').removeClass('current');
		$('.subsubsub .non-replied a').addClass('current');
	}
});