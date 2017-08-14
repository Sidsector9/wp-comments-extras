( function( $ ) {

	var self = {
		vote_button : '',
	};

	self.init = function() {
		self.vote_button = $( '.wce-vote-button' );
		self.vote_click();
	};

	self.vote_click = function() {
		self.vote_button.on( 'click', function() {
			var that       = $( this );
			var wce        = $( '.wce-vote-button' );
			var comment_id = that.data( 'comment-id' );
			var vote_type  = that.data( 'vote-type' );

			$.ajax({
				url : wce_ajax_url,
				type: 'POST',
				data: {
					action: 'save_votes',
					comment_id: comment_id,
					vote_type: vote_type,
				},
			})
			.done( function( response ) {
				if ( $.isArray( response.data ) && 'vote switched' === response.data[0] ) {
					if ( 'up' === response.data[1] ) {
						that
							.siblings( '.wce-vote-button' )
							.find( 'i' )
							.removeClass( 'fa-thumbs-down' )
							.addClass( 'fa-thumbs-o-down' );

						that
							.find( 'i' )
							.removeClass( 'fa-thumbs-o-up' )
							.addClass( 'fa-thumbs-up' );
					}

					if ( 'down' === response.data[1] ) {
						that
							.siblings( '.wce-vote-button' )
							.find( 'i' )
							.removeClass( 'fa-thumbs-up' )
							.addClass( 'fa-thumbs-o-up' );

						that
							.find( 'i' )
							.removeClass( 'fa-thumbs-o-down' )
							.addClass( 'fa-thumbs-down' );
					}
				} else {
					if ( 'up' === vote_type ) {
						that
							.find( 'i' )
							.removeClass( 'fa-thumbs-o-up' )
							.addClass( 'fa-thumbs-up' );
					}

					if ( 'down' === vote_type ) {
						that
							.find( 'i' )
							.removeClass( 'fa-thumbs-o-down' )
							.addClass( 'fa-thumbs-down' );
					}
				}
			});
		});
	};

	return self.init();

})( jQuery );