<?php
/**
 * Class for fetching book statistics.
 *
 * @package Goodreads WordPress Widgets
 * @since   1.0.0
 */

namespace tw2113;

/**
 * Class Goodreads_Review_Statistics_API
 *
 * @since 1.0.0
 */
class Goodreads_Books_By_ISBN_API extends Goodreads_API {

	/**
	 * Goodreads API endpoint to query.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $endpoint = '/book/isbn/';

	/**
	 * Book ISBN number. Can be either ISBn10 or ISBN13.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $isbn;

	/**
	 * Goodreads_Books_By_ISBN_API constructor.
	 *
	 * @param array $args Array of arguments.
	 */
	public function __construct( $args ) {
		parent::__construct( $args );

		$this->isbn = $args['isbn'];
	}

	/**
	 * Retrieve latest user badge.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_book(): array {
		$url = sprintf( '%s%s%s',
			$this->base_uri,
			$this->endpoint,
			$this->isbn
		);

		$results = wp_remote_get(
			add_query_arg(
				[
					'key' => $this->api_key,
				],
				$url
			)
		);

		return $results;
	}
}
