<?php
/**
 * Goodreads Current Reading Widget.
 *
 * @package Goodreads
 * @subpackage Widgets
 * @since   1.0.0
 */

namespace tw2113;

/**
 * Extend our class and create our new widget.
 *
 * @since 1.0.0
 */
class Goodreads_Profile_Widget extends Goodreads_Base_Widget {

	/**
	 * Saved settings.
	 *
	 * @var array|mixed|void
	 */
	protected $goodreads_settings = [];

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$widget_ops = [
			'classname'   => '',
			'description' => esc_html__( 'Display user profile data', 'mb_goodreads' ),
		];
		parent::__construct( 'goodreads_profile', esc_html__( 'Goodreads Profile', 'mb_goodreads' ), $widget_ops );

		$this->goodreads_settings = get_option( 'mb_goodreads_settings', [] );
	}

	/**
	 * Form method.
	 *
	 * @since 1.0.0
	 *
	 * @param array $instance Widget instance.
	 * @return void
	 */
	public function form( $instance = [] ) {

		$defaults = [
			'title' => esc_html__( 'My profile', 'mb_goodreads' ),
			'limit' => '2',
		];
		$instance = wp_parse_args( (array) $instance, $defaults );
		$title    = trim( strip_tags( $instance['title'] ) );

		$this->form_input(
			[
				'label' => esc_html__( 'Title:', 'mb_goodreads' ),
				'name'  => $this->get_field_name( 'title' ),
				'id'    => $this->get_field_id( 'title' ),
				'type'  => 'text',
				'value' => $title,
			]
		);
	}

	/**
	 * Update method.
	 *
	 * @since 1.0.0
	 *
	 * @param array $new_instance New widget instance.
	 * @param array $old_instance Old widget instance.
	 * @return array
	 */
	public function update( $new_instance = [], $old_instance = [] ) : array {
		$instance          = $old_instance;
		$instance['title'] = trim( strip_tags( $new_instance['title'] ) );

		return $instance;
	}

	/**
	 * Widget display method.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Widget instance.
	 */
	public function widget( $args = [], $instance = [] ) {

		$title   = trim( strip_tags( $instance['title'] ) );
		$user_id = ! empty( $this->goodreads_settings['user_id'] ) ? trim( strip_tags( $this->goodreads_settings['user_id'] ) ) : '';
		$api_key = ! empty( $this->goodreads_settings['client_id'] ) ? trim( strip_tags( $this->goodreads_settings['client_id'] ) ) : '';

		echo $args['before_widget'];

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}

		$error = $this->maybe_display_errors( $user_id, $api_key );

		if ( false === $error ) {
			$transient  = apply_filters( 'profile_filter', 'goodreads_user_profile_' . $user_id );
			$trans_args = [
				'transient_name' => $transient,
				'user_id'        => $user_id,
				'api_key'        => $api_key,
			];
			$user       = $this->getTransient( $trans_args );

			if ( is_wp_error( $user ) ) {
				echo $user->get_error_message();
			} else {
				if ( $user && is_string( $user ) ) {
					/**
					 * Filters the list of classes to apply to our widget output.
					 *
					 * @since 1.0.0
					 *
					 * @param array  $value Array of classes to use.
					 */
					$classes   = implode( ', ', apply_filters( 'goodreads_profile_classes', [ 'profile' ] ) );
					$user_xml  = simplexml_load_string( $user, null, LIBXML_NOCDATA );
					$user_data = [
						'user'    => $user_xml->user,
						'classes' => $classes,
					];

					/**
					 * Filters the markup to use for the output.
					 *
					 * @since 1.0.0
					 *
					 * @param array $book_data Data for our books.
					 */
					$profile_markup = apply_filters( 'profile_markup', '', $user_data );

					echo ( '' !== $profile_markup ) ? $profile_markup : $this->profile( $user_data );

				} else {
					echo '<p>' . esc_html__( 'Nothing to display yet', 'mb_goodreads' ) . '</p>';
				}
			}
		}
		echo $args['after_widget'];
	}

	/**
	 * Render our badge.
	 *
	 * @since 1.0.0
	 *
	 * @param array $userdata Array of data for a badge.
	 * @return string $value Rendered list of brews.
	 */
	public function profile( $userdata ) : string {
		if ( ! is_object( $userdata['user'] ) || empty( $userdata['user'] ) ) {
			return '';
		}

		$profile_data = $this->filtered_profile_data( $userdata['user'] );

		$link      = $profile_data['link'];
		$image_url = $profile_data['image_url'];
		unset( $profile_data['link'], $profile_data['image_url'] );

		$profile_start = '<div class="' . $userdata['classes'] . '">';
		$profile_end   = '</div>';
		$profile_image = $this->profile_photo( $link, $image_url, $profile_data['name'] );
		$profile       = '<p>';

		foreach ( $profile_data as $data_key => $data_value ) {
			$data_key = str_replace( '_count', '', $data_key );
			$profile .= ucfirst( $data_key ) . ': ' . $data_value . '<br/>';
		}
		$profile .= '</p>';

		$profile .= sprintf(
			'<p><small>%s</small></p>',
			sprintf(
				/* Translators: placeholder will hold a link to Goodreads.com */
				esc_html__( 'All data provided by %s', 'mb_goodreads' ),
				'<a href="https://www.goodreads.com">Goodreads.com</a>'
			)
		);

		return $profile_start . $profile_image . $profile . $profile_end;
	}

	/**
	 * Retrieve our Goodreads API data, from a transient first, if available.
	 *
	 * @since 1.0.0
	 *
	 * @param array $trans_args Array of transient name, username, Goodreads API credentials, and listing limit.
	 * @return array|\WP_Error Data from Goodreads
	 */
	public function getTransient( $trans_args = [] ) {
		$theprofile = get_transient( $trans_args['transient_name'] );
		if ( false === $theprofile ) {
			$myprofile = new Goodreads_Profile_API(
				[
					'api_key' => $trans_args['api_key'],
					'user_id' => $trans_args['user_id'],
				]
			);

			$gettheprofile = $myprofile->get_profile();

			/**
			 * Filters the duration to store our transients.
			 *
			 * @since 1.0.0
			 *
			 * @param int $value Time in seconds.
			 */
			$duration = apply_filters( 'goodreads_transient_duration', 60 * 10 );

			// Save only if we get a good response back.
			if ( 200 === wp_remote_retrieve_response_code( $gettheprofile ) ) {
				$theprofile = wp_remote_retrieve_body( $gettheprofile );
				set_transient( $trans_args['transient_name'], $theprofile, $duration );
			} else {
				if ( current_user_can( 'manage_options' ) ) {
					if ( is_array( $gettheprofile ) && isset( $gettheprofile['error'] ) ) {
						$message = $gettheprofile['error'];
					} else {
						$message = $gettheprofile->get_error_message();
					}

					return new \WP_Error(
						'admin_only_error',
						sprintf(
							/* Translators: placeholder will hold Goodreads API error message. */
							esc_html__( 'Admin-only error: %s', 'mb_goodreads' ),
							esc_html( $message )
						)
					);
				}
			}
		}

		return $theprofile;
	}

	/**
	 * Clear out our transient as needed, like if the widget limit changes.
	 *
	 * @since 1.0.0
	 */
	public function clearTransient() {
		$user_id = ! empty( $this->goodreads_settings['user_id'] ) ? trim( strip_tags( $this->goodreads_settings['user_id'] ) ) : '';
		if ( ! empty( $user_id ) ) {
			delete_transient( apply_filters( 'profile_filter', 'goodreads_user_profile_' . $user_id ) );
		}
	}

	/**
	 * Filter down our data to only what we want.
	 *
	 * @since 1.0.0
	 *
	 * @param array $userdata Array of user data.
	 * @return array
	 */
	protected function filtered_profile_data( $userdata = [] ) : array {
		$fields = $this->wanted_profile_fields();
		return array_filter( (array) $userdata, function ( $datum ) use ( $fields ) {
			return in_array( $datum, $fields, true );
		}, ARRAY_FILTER_USE_KEY );
	}

	/**
	 * The fields we want from Goodreads data.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	protected function wanted_profile_fields() : array {
		return [
			'age',
			'name',
			'link',
			'image_url',
			'location',
			'joined',
			'last_active',
			'friends_count',
			'groups_count',
			'reviews_count',
		];
	}

	/**
	 * Return a linked profile photo.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url   Profile URL.
	 * @param string $image Profile image URL.
	 * @param string $name  User's first name.
	 * @return string
	 */
	protected function profile_photo( $url = '', $image = '', $name = '' ) : string {
		return sprintf(
			'<p><a href="%s"><img src="%s" alt="%s" /></a></p>',
			$url,
			$image,
			sprintf(
				/* Translators: placeholder will have Goodread's profile first name */
				esc_attr__( 'Profile photo of %s', 'mb_goodreads' ),
				$name
			)
		);
	}
}
