/* global jQuery, wpbc */

( function ( $, wp ) {

	let videoPreviewMarkup,
		templateData,
		isBrightcoveMetabox = false,
		cmb2BCVideoMetabox = $( '.cmb2-brightcove-video-metabox' ),
		videoPreviewTemplate = wp.template( 'cmb2-brightcove-video-preview' );

	const CMB2BrightcoveMetaBox_callback = function ( e ) {
		e.preventDefault();

		// Set flag to identify the event trigger for "insert:shortcode" event.
		isBrightcoveMetabox = true;

		const bcContainer = $( this ).parent(),
			bcVideoIdElem = bcContainer.find( '.bc_video_id' ),
			bcVideoDurationElem = bcContainer.find( '.bc_video_duration' ),
			bcPlayerIdElem = bcContainer.find( '.bc_player_id' ),
			bcAccountIdElem = bcContainer.find( '.bc_account_id' ),
			videoPreviewElem = bcContainer.find( '.brightcove-video-preview' );

		wpbc.triggerModal();

		wpbc.broadcast.on( 'insert:shortcode', function () {
			// Check if the event is triggered from metabox or content media button.
			if ( !isBrightcoveMetabox ) {
				return;
			}

			isBrightcoveMetabox = false;

			// Get data from parsed shortcode attributes.
			const brightcoveVideoDetails = wp.shortcode.attrs( wpbc.shortcode ),
				brightcoveVideoData = brightcoveVideoDetails.named,
				videoDuration = wpbc.modal.brightcoveMediaManager.detailsView.$el.find( '.detail-duration span' ).text();

			bcVideoIdElem.val( brightcoveVideoData.video_id );
			bcVideoDurationElem.val( videoDuration );
			bcPlayerIdElem.val( brightcoveVideoData.player_id );
			bcAccountIdElem.val( brightcoveVideoData.account_id );

			templateData = {
				'id': brightcoveVideoData.video_id,
				'account_id': brightcoveVideoData.account_id,
				'player_id': brightcoveVideoData.player_id,
			};

			videoPreviewMarkup = videoPreviewTemplate( templateData );
			videoPreviewElem.html( videoPreviewMarkup );
		} );
	};

	const CMB2RemoveBrightcoveVideo_callback = function ( e ) {
		e.preventDefault();

		const bcContainer = $( this ).parent().parent();

		// Clear all fields value.
		bcContainer.find( '.bc_video_id' ).val( '' );
		bcContainer.find( '.bc_video_duration' ).val( '' );
		bcContainer.find( '.bc_player_id' ).val( '' );
		bcContainer.find( '.bc_account_id' ).val( '' );

		// Clear brightcove iframe/preview.
		bcContainer.find( '.brightcove-video-preview' ).html('');
	};

	// Add Brightcove Video.
	cmb2BCVideoMetabox.on(
		'click',
		'.brightcove-add-media-btn',
		CMB2BrightcoveMetaBox_callback
	);

	// Remove/Clear selected brightcove video.
	cmb2BCVideoMetabox.on(
		'click',
		'.bc-remove-video',
		CMB2RemoveBrightcoveVideo_callback
	)

} )( jQuery, window.wp );
