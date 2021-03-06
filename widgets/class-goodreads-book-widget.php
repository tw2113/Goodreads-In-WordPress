<?php
/**
 * Goodreads Book Widget.
 *
 * @package Goodreads in WordPress
 * @subpackage Widgets
 * @since   1.0.0
 */

namespace tw2113;

/**
 * Extend our class and create our new widget.
 *
 * @since 1.0.0
 */
class Goodreads_Book_Widget extends Goodreads_Base_Widget {

	use transients;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$widget_ops = [
			'classname'   => '',
			'description' => esc_html__( 'Display individual book data by ISBN', 'mb_goodreads' ),
		];
		parent::__construct( 'goodreads_book', esc_html__( 'Goodreads Book Data', 'mb_goodreads' ), $widget_ops );

		$this->set_settings();
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
			'title' => esc_html__( 'About this book', 'mb_goodreads' ),
			'isbn'  => '',
		];
		$instance = wp_parse_args( (array) $instance, $defaults );
		$title    = trim( strip_tags( $instance['title'] ) );
		$isbn     = trim( strip_tags( $instance['isbn'] ) );

		$this->form_input(
			[
				'label' => esc_html__( 'Title:', 'mb_goodreads' ),
				'name'  => $this->get_field_name( 'title' ),
				'id'    => $this->get_field_id( 'title' ),
				'type'  => 'text',
				'value' => $title,
			]
		);

		$this->form_input(
			[
				'label' => esc_html__( 'ISBN (ISBN10 or ISBN13):', 'mb_goodreads' ),
				'name'  => $this->get_field_name( 'isbn' ),
				'id'    => $this->get_field_id( 'isbn' ),
				'type'  => 'text',
				'value' => $isbn,
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
		$instance['isbn']  = trim( strip_tags( $new_instance['isbn'] ) );

		if ( $new_instance['isbn'] !== $old_instance['isbn'] ) {
			delete_transient( apply_filters( 'book_filter', "goodreads_book_{$new_instance['isbn']}" ) );
		}

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
		$isbn    = trim( strip_tags( $instance['isbn'] ) );
		$user_id = ! empty( $this->goodreads_settings['user_id'] ) ? trim( strip_tags( $this->goodreads_settings['user_id'] ) ) : '';
		$api_key = ! empty( $this->goodreads_settings['api_key'] ) ? trim( strip_tags( $this->goodreads_settings['api_key'] ) ) : '';

		echo $args['before_widget'];

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}

		$error = $this->maybe_display_errors( $user_id, $api_key );

		if ( false === $error ) {
			$transient  = apply_filters( 'book_filter', 'goodreads_book_' . $isbn );
			$trans_args = [
				'transient_name' => $transient,
				'object'         => new Goodreads_Books_By_ISBN_API(
					[
						'api_key' => $api_key,
						'isbn'    => $isbn,
						'user_id' => $user_id,
					]
				),
			];
			$book       = $this->get_transient( $trans_args );

			if ( is_wp_error( $book ) ) {
				echo $book->get_error_message();
			} else {
				if ( $book && is_string( $book ) ) {
					/**
					 * Filters the list of classes to apply to our widget output.
					 *
					 * @since 1.0.0
					 *
					 * @param array  $value Array of classes to use.
					 */
					$classes   = implode( ', ', apply_filters( 'goodreads_book_classes', [ 'book' ] ) );
					$book_xml  = simplexml_load_string( $book, null, LIBXML_NOCDATA );
					$book_data = [
						'book'    => $book_xml->book,
						'classes' => $classes,
					];

					/**
					 * Filters the markup to use for the output.
					 *
					 * @since 1.0.0
					 *
					 * @param array $book_data Data for our books.
					 */
					$book_markup = apply_filters( 'book_markup', '', $book_data );

					echo ( '' !== $book_markup ) ? $book_markup : $this->book( $book_data );

				} else {
					echo '<p>' . esc_html__( 'Nothing to display.', 'mb_goodreads' ) . '</p>';
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
	 * @param array $bookdata Array of data for a badge.
	 * @return string $value Rendered list of brews.
	 */
	public function book( $bookdata ) : string {
		if ( ! is_object( $bookdata['book'] ) || empty( $bookdata['book'] ) ) {
			return '';
		}

		$book_data = $this->filtered_book_data( $bookdata['book'] );

		$link      = $book_data['url'];
		$image_url = $book_data['image_url'];
		$wanted    = $this->construct_wanted_data();

		$book_start = '<div class="' . $bookdata['classes'] . '">';
		$book_end   = '</div>';
		$book_image = $this->book_photo( $link, $image_url, $book_data['title'] );
		$book       = $this->construct_book( $wanted, $book_data );

		return $book_start . $book_image . $book . $book_end;
	}



	/**
	 * Filter down our data to only what we want.
	 *
	 * @since 1.0.0
	 *
	 * @param array $bookdata Array of book data.
	 * @return array
	 */
	protected function filtered_book_data( $bookdata = [] ) : array {
		$fields = $this->wanted_book_fields();
		return array_filter( (array) $bookdata, function( $datum ) use ( $fields ) {
			return in_array( $datum, $fields, true );
		}, ARRAY_FILTER_USE_KEY );
	}

	/**
	 * The fields we want from Goodreads data.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	protected function wanted_book_fields() : array {
		return [
			'title',
			'image_url',
			'isbn',
			'isbn13',
			'url',
			'num_pages',
			'average_rating',
		];
	}

	/**
	 * Reduce down our wanted book data to just desired fields.
	 *
	 * @since 1.0.0
	 *
	 * @return array|null
	 */
	protected function construct_wanted_data() {
		$wanted = $this->wanted_book_fields();
		$wanted = array_flip( $wanted ); // Flip so we can unset by associative array key.
		unset(
			$wanted['url'],
			$wanted['image_url']
		);

		return array_flip( $wanted ); // Reflip so we can iterate over values.
	}

	/**
	 * Construct our book markup and information.
	 *
	 * @since 1.0.0
	 *
	 * @param array $wanted    Wanted fields.
	 * @param array $book_data General book information.
	 * @return string
	 */
	protected function construct_book( $wanted, $book_data ) : string {
		$book        = '<p>%s</p>%s';
		$book_fields = '';

		foreach ( $wanted as $wanted_key ) {
			$data_key     = str_replace( '_', ' ', $wanted_key );
			$book_fields .= ucfirst( $data_key ) . ': ' . $book_data[ $wanted_key ] . '<br/>';
		}

		$copy = sprintf(
			'<p><small>%s</small></p>',
			sprintf(
				/* Translators: placeholder will hold a link to Goodreads.com */
				esc_html__( 'All data provided by %s', 'mb_goodreads' ),
				'<a href="https://www.goodreads.com">Goodreads.com</a>'
			)
		);

		return sprintf(
			$book,
			$book_fields,
			$copy
		);
	}

	/**
	 * Return a linked profile photo.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url   Book URL.
	 * @param string $image Book image URL.
	 * @param string $name  Book's name.
	 * @return string
	 */
	protected function book_photo( $url = '', $image = '', $name = '' ) : string {
		return sprintf(
			'<p><a href="%s"><img src="%s" alt="%s" /></a></p>',
			$url,
			$image,
			sprintf(
				/* Translators: placeholder will have Goodread's profile first name */
				esc_attr__( 'Cover photo of %s', 'mb_goodreads' ),
				$name
			)
		);
	}
}
