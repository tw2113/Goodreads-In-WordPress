<?php
/**
 * Class for setting up options page.
 *
 * @package    Goodreads WordPress Widgets
 * @subpackage Goodreads_settings
 * @since      1.0.0
 */

namespace tw2113;

/**
 * Class Goodreads_Settings
 *
 * @since 1.0.0
 */
class Goodreads_Settings {

	/**
	 * Run our hooks.
	 *
	 * @since 1.0.0
	 */
	public function do_hooks() {
		add_action( 'admin_init', [ $this, 'settings_registration' ] );
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
	}

	/**
	 * Register our settings.
	 *
	 * @since 1.0.0
	 */
	public function settings_registration() {
		register_setting( 'mb_goodreads', 'mb_goodreads_settings', [ $this, 'settings_validate' ] );
		$settings = get_option( 'mb_goodreads_settings', '' );
		$api_key  = $settings['api_key'] ?? '';
		$user_id  = $settings['user_id'] ?? '';

		add_settings_section(
			'mb_goodreads_settings',
			esc_html__( 'Goodreads WordPress Widgets API settings', 'mb_goodreads' ),
			[ $this, 'do_section' ],
			'mb_goodreads_do_options'
		);
		add_settings_field(
			'goodreads_api_key',
			'<label for="mb_goodreads_api_key">' . esc_html__( 'API Key', 'mb_goodreads' ) . '</label>',
			[ $this, 'input_fields' ],
			'mb_goodreads_do_options',
			'mb_goodreads_settings',
			[
				'class' => 'regular-text',
				'id'    => 'mb_goodreads_api_key',
				'type'  => 'text',
				'name'  => 'mb_goodreads_settings[api_key]',
				'value' => $api_key,
			]
		);
		add_settings_field(
			'goodreads_user_id',
			'<label for="mb_goodreads_user_id">' . esc_html__( 'User ID', 'mb_goodreads' ) . '</label>',
			[ $this, 'input_fields' ],
			'mb_goodreads_do_options',
			'mb_goodreads_settings',
			[
				'class' => 'regular-text',
				'id'    => 'mb_goodreads_user_id',
				'type'  => 'text',
				'name'  => 'mb_goodreads_settings[user_id]',
				'value' => $user_id,
			]
		);
	}

	/**
	 * Helper method for displaying our options page.
	 *
	 * @since 1.0.0
	 */
	public function do_section() {
		?>
		<p>
		<?php
			printf(
				// translators: placeholder will have link to Goodreads API docs.
				esc_html__( 'Information and API access application can be found at %s.', 'mb_goodreads' ),
				'<a href="https://www.goodreads.com/api">Goodreads API</a>'
			);
		?>
		</p>
		<p>
		<?php
			esc_html_e(
				'User ID can be found in URL when visiting your own profile. Example: https://www.goodreads.com/user/show/#######-michael. Use the numbers in place of the # characters', 'mb_goodreads'
			);
		?>
		</p>
		<?php
	}

	/**
	 * Helper method to display inputs for settings page.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Array of arguments for method.
	 */
	public function input_fields( $args = [] ) {
		$args = wp_parse_args( $args, [
			'class'       => null,
			'id'          => null,
			'type'        => 'text',
			'name'        => null,
			'value'       => '',
			'description' => null,
		] );

		echo '<input type="' . esc_attr( $args['type'] ) . '" class="' . esc_attr( $args['class'] ) . '" id="' . esc_attr( $args['id'] ) . '" name="' . esc_attr( $args['name'] ) . '" placeholder="' . esc_attr( $args['description'] ) . '" value="' . esc_attr( $args['value'] ) . '" />';
	}

	/**
	 * Helper method for sanitization of our options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $input Settings to validate.
	 * @return array
	 */
	public function settings_validate( $input = [] ) : array {
		return array_map( 'sanitize_text_field', $input );
	}

	/**
	 * Set up our admin menu.
	 *
	 * @since 1.0.0
	 */
	public function admin_menu() {
		add_options_page(
			esc_html__( 'Goodreads API Settings', 'mb_goodreads' ),
			esc_html__( 'Goodreads API', 'mb_goodreads' ),
			'manage_options',
			'mb_goodreads_settings',
			[ $this, 'plugin_options' ]
		);
	}

	/**
	 * Render the Goodreads Widgets settings page via template file.
	 *
	 * @since 1.0.0
	 */
	public function plugin_options() {
		include plugin_dir_path( __DIR__ ) . 'tmpl/options.php';
	}
}
$goodreads_settings = new Goodreads_Settings();
$goodreads_settings->do_hooks();
