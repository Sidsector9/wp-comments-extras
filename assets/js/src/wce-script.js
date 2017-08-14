( function( $ ) {

	var self = {
		vote_button: '',
	};

	self.init = function() {
		self.vote_button = $( '.wce-vote-button' );
		self.vote_click();
	};

	self.vote_click = function() {
		self.vote_button.on( 'click', function() {
			var comment_id = $( this ).data( 'comment-id' );
			var vote_type  = $( this ).data( 'vote-type' );

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

			});
		});
	};

	return self.init();

})( jQuery );