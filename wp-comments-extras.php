<?php
/**
 * Plugin Name: WP Comments Extras
 */

class WP_Comments_extras {

	public function __construct() {
		wp_enqueue_script( 'wce-script', plugins_url( '/assets/js/src/wce-script.js', __FILE__ ), array( 'jquery' ), null, true );
		wp_localize_script( 'wce-script', 'wce_ajax_url', admin_url( 'admin-ajax.php' ) );
		add_action( 'wp_ajax_save_votes', array( $this, 'save_votes' ) );
		add_action( 'wp_ajax_nopriv_save_votes', array( $this, 'save_votes' ) );
		add_filter( 'comment_reply_link_args', array( $this, 'add_voting_buttons' ), 10, 2 );
	}

	public function add_voting_buttons( $args, $comment ) {
		$args['after'] = '<span class="wce-vote-button" data-comment-id="' . $comment->comment_ID . '" data-vote-type="up">UP</span>';
		$args['after'] .= '<span class="wce-vote-button" data-comment-id="' . $comment->comment_ID . ' data-vote-type="down">DOWN</span></div>';
		return $args;
	}

	public function save_votes() {
		
		wp_die();
	}
}

new WP_Comments_extras();