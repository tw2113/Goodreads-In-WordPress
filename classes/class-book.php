<?php
/**
 * Book renderer.
 *
 * @package Goodreads in WordPress
 */

namespace tw2113;

/**
 * Class Book
 */
class Book {

	/**
	 * Book title.
	 *
	 * @var mixed|string
	 * @since 1.0.0
	 */
	protected $title = '';

	/**
	 * Book image.
	 *
	 * @var mixed|string
	 * @since 1.0.0
	 */
	protected $image = '';

	/**
	 * Book URL.
	 *
	 * @var mixed|string
	 * @since 1.0.0
	 */
	protected $link = '';

	/**
	 * Book constructor.
	 *
	 * @param array $args Array of arguments for the book.
	 */
	public function __construct( $args = [] ) {
		$this->title = $args['title'];
		$this->link  = $args['link'];
	}

	/**
	 * Formats a user badge URL.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_book_linked_title() : string {
		$tmpl = '<p><a href="%s">%s</a></p>';

		return sprintf(
			$tmpl,
			esc_url( $this->link ),
			esc_html( $this->title )
		);
	}

	/**
	 * Return full markup for a given book.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed|string
	 */
	public function get_book_markup() {
		return $this->get_book_linked_title();
	}
}
