<?php

if ( ! class_exists( 'WP_Comments_extras' ) ) {

	/**
	 * Adds the Voting buttons in the comment section and
	 * implements functions required to set and get votes.
	 */
	class WP_Comments_Extras {

		/**
		 * Stores the ID of the current user on `init` hook.
		 *
		 * @var string
		 * @access private
		 */
		private $user_id = null;

		/**
		 * Sets if list voters flag is enabled.
		 *
		 * @var string
		 * @access private
		 */
		private $list_voters_flag = null;

		private $wce_messages = array();

		private $user_self_vote = null;

		private $options = null;

		/**
		 * Enqueues necessary styles and script, and adds relevent action and filter hooks.
		 */
		public function __construct() {
			wp_enqueue_style( 'wce-style', plugins_url( '../../assets/css/wce-style.min.css', __FILE__ ), null, null, null );
			wp_enqueue_style( 'font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' );
			wp_enqueue_script( 'wce-script', plugins_url( '../../assets/js/src/wce-script.min.js', __FILE__ ), array( 'jquery', 'wp-util' ), null, true );
			wp_localize_script( 'wce-script', 'wce_ajax_url', admin_url( 'admin-ajax.php' ) );
			add_action( 'wp_ajax_save_votes', array( $this, 'save_votes' ) );
			add_action( 'wp_ajax_nopriv_save_votes', array( $this, 'save_votes' ) );
			add_action( 'init', array( $this, 'set_info_on_init' ) );
			add_filter( 'comment_reply_link_args', array( $this, 'add_voting_buttons' ), 10, 2 );
		}

		/**
		 * Sets the values at init.
		 */
		public function set_info_on_init() {
			$this->user_id          = get_current_user_id();
			$this->options          = get_option( 'wce-vote-settings' );

			$this->wce_messages['login_false'] = esc_html__( 'You need to log in to vote', 'wce' );

			wp_localize_script( 'wce-script', 'is_user_logged_in', is_user_logged_in() ? 'yes' : 'no' );

			if ( 'on' === $this->options['list-voters'] ) {
				add_action( 'wp_ajax_list_voters', array( $this, 'list_voters' ) );
				add_action( 'wp_ajax_nopriv_list_voters', array( $this, 'list_voters' ) );
				add_action( 'wp_footer', array( $this, 'tmpl_in_footer' ) );
			}

			wp_localize_script( 'wce-script', 'wce_messages', $this->wce_messages );
		}

		/**
		 * Adds the 'vote up' and 'vote down' next to the reply button in comments.
		 *
		 * @param array  $args    Array of filterable parameters.
		 * @param object $comment Comment object.
		 */
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

			$args['after'] = '<span class="wce-vote-button ' . $voted . '" data-comment-id="' . $comment_id . '" data-vote-type="up">' . $vote_icon . '<span class="wce-vote-count">' . $this->count_votes( $users, 'up' ) . '</span></span>';

			$voted = null;

			if ( 'down' === $users[ $user_id ] ) {
				$voted     = 'wce-voted-down';
				$vote_icon = '<i class="fa fa-thumbs-down" aria-hidden="true"></i>';
			} else {
				$vote = null;
				$vote_icon = '<i class="fa fa-thumbs-o-down" aria-hidden="true"></i>';
			}

			$args['after'] .= '<span class="wce-vote-button ' . $voted . '" data-comment-id="' . $comment_id . '" data-vote-type="down">' . $vote_icon . '<span class="wce-vote-count">' . $this->count_votes( $users, 'down' ) . '</span></span>';

			if ( 'on' === $this->options['list-voters'] ) {
				$args['after'] .= '<span class="three-dots-container" title="' . esc_html( 'Show list of voters' ) . '" data-comment-id="' . $comment_id . '"><span></span><span></span><span></span></span>';

				$args['after'] .= '<div class="wce-voter-list-overlay"></div>';

				$args['after'] .= '<span class="wce-voter-list"><div>' . esc_html__( 'Voters', 'wce' ) . '<span class="close-voter-list"><i class="fa fa-times" aria-hidden="true"></i></span></div></span>';
			}

			$args['after'] .= '</div>';

			return $args;
		}

		/**
		 * The actual logic that sets and gets the vote in the database.
		 */
		public function save_votes() {
			$comment_id = absint( filter_input( INPUT_POST, 'comment_id', FILTER_SANITIZE_STRING ) );
			$vote_type  = filter_input( INPUT_POST, 'vote_type', FILTER_SANITIZE_STRING );
			$user_id    = $this->user_id;
			$users      = get_comment_meta( $comment_id, 'votes', true );
			$vote_data  = array();
			$vote_other = 'up' === $vote_type ? 'up' : 'down';
			$self_vote  = $this->options['user-self-vote'];

			$comment_author_id = absint( get_comment( $comment_id, ARRAY_A )['user_id'] );

			if ( empty( $self_vote ) && $comment_author_id === $user_id ) {
				wp_send_json_error(
					array( esc_html( 'You cannot vote your own comment!' ) )
				);
				return;
			}

			if ( empty( $users ) ) {
				// The first vote.
				$vote_data = array( $user_id => $vote_type );
				if ( add_comment_meta( $comment_id, 'votes', $vote_data, true ) ) {
					$users_count = get_comment_meta( $comment_id, 'votes', true );
					wp_send_json_success( array( 'count' => $this->count_votes( $users_count, $vote_type ) ) );
				} else {
					wp_send_json_error();
				}
			} elseif ( isset( $users[ $user_id ] ) && $users[ $user_id ] === $vote_type ) {
				// Voting twice of the same type.
				wp_send_json_error();
			} elseif ( isset( $users[ $user_id ] ) ) {
				// Switching votes.
				$users[ $user_id ] = $vote_other;
				if ( update_comment_meta( $comment_id, 'votes', $users ) ) {
					wp_send_json_success(
						array(
							'vote switched',
							$vote_type,
							array(
								'count_up' => $this->count_votes( $users, 'up' ),
								'count_down' => $this->count_votes( $users, 'down' ),
							),
						)
					);
				} else {
					wp_send_json_error();
				}
			} else {
				// Add more votes.
				$users[ $user_id ] = $vote_type;
				if ( update_comment_meta( $comment_id, 'votes', $users ) ) {
					$users_count = get_comment_meta( $comment_id, 'votes', true );
					wp_send_json_success( array( 'count' => $this->count_votes( $users_count, $vote_type ) ) );
				} else {
					wp_send_json_error();
				}
			}

			wp_die();
		}

		/**
		 * Actual logic that lists all voters for a particular comment.
		 */
		public function list_voters() {
			$comment_id = absint( filter_input( INPUT_POST, 'comment_id', FILTER_SANITIZE_STRING ) );
			$users      = get_comment_meta( $comment_id, 'votes', true );
			$voter_data = array();

			foreach ( $users as $user_id => $vote_type ) {
				$user_obj = get_user_by( 'ID', $user_id );
				$voter_data[] = array(
					'voter_name' => $user_obj->display_name,
					'vote_type'  => $vote_type,
				);
			}
			echo wp_json_encode( $voter_data );
			wp_die();
		}

		/**
		 * Template to list voters
		 */
		public function tmpl_in_footer() {
			?>
			<script type="text/html" id="tmpl-list-voters">
				<div class="voter-item">
					<div class="wce-voter-name">{{ data.voter_name }}</div>
					<div class="wce-vote-type">
					<# if ( 'up' === data.vote_type ) { #>
						<i class="fa fa-thumbs-up wce-voter-up" aria-hidden="true"></i>
					<# } else { #>
						<i class="fa fa-thumbs-down wce-voter-down" aria-hidden="true"></i>
					<# } #>
					</div>
				</div>
			</script>
			<?php
		}

		/**
		 * Counts the number of votes of a given type.
		 *
		 * @param array  $users List of all users mapped to their votes.
		 * @param string $type  The type of vote.
		 */
		public function count_votes( $users, $type ) {
			return count( array_filter( $users, function( $value ) use ( $type ) {
				return $type === $value;
			}));
		}
	}
}
