<?php

if ( ! class_exists( 'WP_Comments_Extras_Admin_Settings' ) ) {

	/**
	 * This class generates the settings page.
	 */
	class WP_Comments_Extras_Admin_Settings {

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'wce_settings_page' ) );
		}

		/**
		 * Add a menu page.
		 */
		public function wce_settings_page() {
			add_menu_page(
				'WP Comments Extras Settings',
				'WP Comments Extras Settings',
				'manage_options',
				'wce-settings',
				array( $this, 'render_settings_page' )
			);
			add_action( 'admin_init', array( $this, 'wce_sections_and_fields' ) );
		}

		/**
		 * Render settings page.
		 */
		public function render_settings_page() {
			?>
			<form action="options.php" method="POST">
				<?php settings_fields( 'wce-settings' ); ?>
				<?php do_settings_sections( 'wce-settings' ); ?>
				<?php submit_button(); ?>
			</form>
			<?php
		}

		/**
		 * Sections and Fields.
		 */
		public function wce_sections_and_fields() {
			add_settings_section(
				'wce-feature-section',
				esc_html__( 'Feature List', 'wce' ),
				array( $this, 'render_feature_list_section' ),
				'wce-settings'
			);

			add_settings_field(
				'wce-vote-settings',
				esc_html__( 'Vote settings', 'wce' ),
				array( $this, 'render_list_voters' ),
				'wce-settings',
				'wce-feature-section'
			);

			register_setting(
				'wce-settings',
				'wce-vote-settings'
			);
		}

		/**
		 * Render title.
		 */
		public function render_feature_list_section() {
			printf( '<h4>%s</h4>', esc_html__( 'Voting configuration:', 'wce' ) );
		}

		/**
		 * Render checkbox.
		 */
		public function render_list_voters() {
			$option = get_option( 'wce-vote-settings' );
			?>
			<p>
			<input name="wce-vote-settings[list-voters]" id="wce-vote-settings[list-voters]" type="checkbox" <?php checked( 'on', $option['list-voters'], true )?>>
			<label for="wce-vote-settings[list-voters]">Enable list voters button</label>
			</p>

			<p>
			<input name="wce-vote-settings[user-self-vote]" id="wce-vote-settings[user-self-vote]" type="checkbox" <?php checked( 'on', $option['user-self-vote'], true )?>>
			<label for="wce-vote-settings[user-self-vote]">User can vote their own comment</label>
			</p>
			<?php
		}
	}
}
