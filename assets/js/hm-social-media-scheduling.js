/**
 * HM Social Media Scheduling
 *
 * @package
 */

/* global wp */

/**
 * Facebook Queue Message
 */
function facebook() {
	const facebook = document.getElementById( 'hm_social_media_data_post_facebook' );
	let fbChecked = false;
	let fbMessage = document.getElementById( 'fb-queue' );
	if ( fbMessage ) {
		fbMessage.parentNode.removeChild( fbMessage );
	}
	if ( facebook ) {
		fbChecked = facebook.checked;

		fbMessage = document.createElement( 'div' );
		fbMessage.setAttribute( 'id', 'fb-queue' );
		fbMessage.innerHTML = wp.i18n.__( 'Post Queued to Facebook', 'hm-social-media-scheduling' );
	}
	if ( fbChecked ) {
		facebook.parentNode.insertBefore( fbMessage, facebook.nextSibling );
	}
}

/**
 * Twitter Queue Message
 */
function twitter() {
	const twitter  = document.getElementById( 'hm_social_media_data_post_twitter' );
	let twChecked = false;
	let twMessage = document.getElementById( 'tw-queue' );
	if ( twMessage ) {
		twMessage.parentNode.removeChild( twMessage );
	}
	if ( twitter ) {
		twChecked = twitter.checked;
		twMessage = document.createElement( 'div' );
		twMessage.setAttribute( 'id', 'tw-queue' );
		twMessage.innerHTML = wp.i18n.__( 'Post Queued to Twitter', 'hm-social-media-scheduling' );
	}
	if ( twChecked ) {
		twitter.parentNode.insertBefore( twMessage, twitter.nextSibling );
	}
}

document.addEventListener( 'click', function ( event ) {
	if ( ! event.target.matches( '.editor-post-publish-button' ) ) {
		return;
	}

	facebook();
	twitter();
} );
