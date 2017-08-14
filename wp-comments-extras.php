<?php
/**
 * Plugin Name: WP Comments Extras
 */

class WP_Comments_extras {

	public function __construct() {
		wp_enqueue_script( 'wce-script', plugins_url( '/assets/js/src/wce-script.js', __FILE__ ), array( 'jquery' ), null, true );
		add_filter( 'comment_reply_link_args', array( $this, 'add_voting_buttons' ), 10, 2 );
	}

	public function add_voting_buttons( $args, $comment ) {
		$args['after'] = '<span class="wce-voting-button wce-voting-up">UP</span>';
		$args['after'] .= '<span class="wce-voting-button wce-voting-down">DOWN</span></div>';
		return $args;
	}
}

new WP_Comments_extras();