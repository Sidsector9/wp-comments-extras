<?php
/**
 * Plugin Name: WP Comments Extras
 */

class WP_Comments_extras {

	private $user_id = null;

	public function __construct() {
		wp_enqueue_style( 'wce-style', plugins_url( '/assets/css/wce-style.css', __FILE__ ), null, null, null );
		wp_enqueue_style( 'font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' );
		wp_enqueue_script( 'wce-script', plugins_url( '/assets/js/src/wce-script.js', __FILE__ ), array( 'jquery' ), null, true );
		wp_localize_script( 'wce-script', 'wce_ajax_url', admin_url( 'admin-ajax.php' ) );
		add_action( 'wp_ajax_save_votes', array( $this, 'save_votes' ) );
		add_action( 'wp_ajax_nopriv_save_votes', array( $this, 'save_votes' ) );
		add_action( 'init', array( $this, 'get_user_id' ) );
		add_filter( 'comment_reply_link_args', array( $this, 'add_voting_buttons' ), 10, 2 );
	}

	public function get_user_id() {
		$this->user_id = get_current_user_id();
	}

	public function add_voting_buttons( $args, $comment ) {
		$comment_id = $comment->comment_ID;
		$users      = get_comment_meta( $comment_id, 'votes', true );
		$user_id    = $this->user_id;

		if ( 'up' === $users[ $user_id ] ) {
			$voted     = 'wce-voted-up';
			$vote_icon = '<i class="fa fa-thumbs-up" aria-hidden="true"></i>';
		} else {
			$vote = null;
			$vote_icon = '<i class="fa fa-thumbs-o-up" aria-hidden="true"></i>';
		}
		$args['after'] = '<span class="wce-vote-button ' . $voted . '" data-comment-id="' . $comment_id . '" data-vote-type="up">' . $vote_icon . '</span>';

		$voted = null;
		if ( 'down' === $users[ $user_id ] ) {
			$voted     = 'wce-voted-down';
			$vote_icon = '<i class="fa fa-thumbs-down" aria-hidden="true"></i>';
		} else {
			$vote = null;
			$vote_icon = '<i class="fa fa-thumbs-o-down" aria-hidden="true"></i>';
		}
		$args['after'] .= '<span class="wce-vote-button ' . $voted . '" data-comment-id="' . $comment_id . '" data-vote-type="down">' . $vote_icon . '</span></div>';

		return $args;
	}

	public function save_votes() {
		$comment_id = absint( filter_input( INPUT_POST, 'comment_id', FILTER_SANITIZE_STRING ) );
		$vote_type  = filter_input( INPUT_POST, 'vote_type', FILTER_SANITIZE_STRING );
		$user_id    = $this->user_id;
		$users      = get_comment_meta( $comment_id, 'votes', true );
		$vote_data  = array();
		$vote_other = 'up' === $vote_type ? 'up' : 'down';

		if ( empty( $users ) ) {
			// The first vote.
			$vote_data = array( $user_id => $vote_type );
			add_comment_meta( $comment_id, 'votes', $vote_data, true )
			? wp_send_json_success()
			: wp_send_json_error();
		} elseif ( isset( $users[ $user_id ] ) && $users[ $user_id ] === $vote_type ) {
			// Voting twice of the same type.
			wp_send_json_error( $data );
		} elseif ( isset( $users[ $user_id ] ) ) {
			// Switching votes.
			$users[ $user_id ] = $vote_other;
			update_comment_meta( $comment_id, 'votes', $users )
			? wp_send_json_success( array( 'vote switched', $vote_type ) )
			: wp_send_json_error( array( 'vote switch failed' ) );
		} else {
			// Add more votes.
			$users[ $user_id ] = $vote_type;
			update_comment_meta( $comment_id, 'votes', $users );
		}

		wp_die();
	}
}

new WP_Comments_extras();