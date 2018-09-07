<?php
/**
 * Goodreads API class.
 *
 * @package goodreads
 * @since   1.0.0
 */

namespace tw2113;

/**
 * Class Goodreads API
 *
 * @since 1.0.0
 */
class Goodreads_API {

	/**
	 * Base Goodreads API endpoint.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $base_uri = 'https://www.goodreads.com';

	/**
	 * Goodreads API Client ID.
	 *
	 * @var mixed|string
	 * @since 1.0.0
	 */
	protected $api_key = '';

	/**
	 * Goodreads user ID.
	 *
	 * @var mixed|string
	 * @since 1.0.0
	 */
	protected $user = '';

	/**
	 * Limit of items to return.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $limit = '';

	/**
	 * Goodreads_API constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args API args.
	 */
	public function __construct( $args = [] ) {
		$this->api_key = $args['api_key'];
		$this->user    = $args['user'];
		$this->limit   = $args['limit'];
	}
}
