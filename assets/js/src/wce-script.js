( function( $ ) {

	var self = {
		vote_button        : '',
		list_voters_button : '',
		close_voter_list   : '',
		voters_list        : '',
		voters_overlay     : '',
	};

	self.init = function() {
		self.vote_button        = $( '.wce-vote-button' );
		self.list_voters_button = $( '.three-dots-container' );
		self.close_voter_list   = $( '.close-voter-list' );
		self.voters_list        = $( '.wce-voter-list' );
		self.voters_overlay     = $( '.wce-voter-list-overlay' );

		self.vote_click();
		self.list_voters_button_click();
		self.close_voter_list_click();
	};

	self.vote_click = function() {
		self.vote_button.on( 'click', function() {

			if ( 'no' === is_user_logged_in ) {
				alert( wce_messages.login_false );
				return;
			}

			var that       = $( this );
			var wce        = $( '.wce-vote-button' );
			var comment_id = that.data( 'comment-id' );
			var vote_type  = that.data( 'vote-type' );

			$.ajax({
				url : wce_ajax_url,
				type: 'POST',
				data: {
					action     : 'save_votes',
					comment_id : comment_id,
					vote_type  : vote_type,
				},
			})
			.done( function( response ) {
				if ( true === response.success && $.isArray( response.data ) && 'vote switched' === response.data[0] ) {
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

						that
							.find( '.wce-vote-count' )
							.text( response.data[2].count_up );

						that
							.siblings( '.wce-vote-button' )
							.find( '.wce-vote-count' )
							.text( response.data[2].count_down );
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

						that
							.find( '.wce-vote-count' )
							.text( response.data[2].count_down );

						that
							.siblings( '.wce-vote-button' )
							.find( '.wce-vote-count' )
							.text( response.data[2].count_up );
					}
				} else {
					if ( 'up' === vote_type ) {
						that
							.find( 'i' )
							.removeClass( 'fa-thumbs-o-up' )
							.addClass( 'fa-thumbs-up' );

						that
							.find( '.wce-vote-count' )
							.text( response.data.count );
					}

					if ( 'down' === vote_type ) {
						that
							.find( 'i' )
							.removeClass( 'fa-thumbs-o-down' )
							.addClass( 'fa-thumbs-down' );

						that
							.find( '.wce-vote-count' )
							.text( response.data.count );
					}
				}
			});
		});
	};

	self.list_voters_button_click = function() {
		self.list_voters_button.on( 'click', function() {
			var that        = $( this );
			var comment_id  = that.data( 'comment-id' );
			var voters_list = that.siblings( '.wce-voter-list' );
			var template    = wp.template( 'list-voters' );

			$.ajax({
				url: wce_ajax_url,
				type: 'POST',
				data: {
					action     : 'list_voters',
					comment_id : comment_id,
				}
			})
			.done( function( response ) {

					voters_list.show();

					$( '.wce-voter-list-overlay' ).fadeIn();

					var parsed = JSON.parse( response );

					for( var i = 0; i < parsed.length; i++ ) {
						voters_list.append(
							template({
								voter_name : parsed[i].voter_name,
								vote_type  : parsed[i].vote_type,
							})
						);
					}
			});
		});
	}

	self.close_voter_list_click = function() {
		self.close_voter_list.on( 'click', function() {
			self.voters_list.fadeOut();
			self.voters_overlay.fadeOut();
			self.voters_list.find( '.voter-item' ).remove();
		});
	}

	return self.init();

})( jQuery );