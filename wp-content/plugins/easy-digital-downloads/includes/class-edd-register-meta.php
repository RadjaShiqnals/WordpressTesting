<?php
/**
 *
 * This class is for registering our meta
 *
 * @package     EDD
 * @subpackage  Classes/Register Meta
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.5
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Register_Meta Class
 *
 * @since 2.5
 */
class EDD_Register_Meta {

	/**
	 * Holds the instance
	 *
	 * Ensures that only one instance of EDD_Register_Meta exists in memory at any one
	 * time and it also prevents needing to define globals all over the place.
	 *
	 * @since  2.5
	 * @var    EDD_Register_Meta
	 */
	private static $instance;

	/**
	 * Setup the meta registration
	 *
	 * @since 2.5
	 */
	private function __construct() {
		$this->hooks();
	}

	/**
	 * Get the one true instance of EDD_Register_Meta.
	 *
	 * @since  2.5
	 * @return $instance
	 */
	public static function instance() {

		if ( ! self::$instance ) {
			self::$instance = new EDD_Register_Meta();
		}

		return self::$instance;
	}

	/**
	 * Register the hooks to kick off meta registration.
	 *
	 * @since  2.5
	 * @return void
	 */
	private function hooks() {
		add_action( 'init', array( $this, 'register_download_meta' ) );
	}

	/**
	 * Register the meta for the download post type.
	 *
	 * @since  2.5
	 * @return void
	 */
	public function register_download_meta() {
		register_meta(
			'post',
			'_edd_download_earnings',
			array(
				'object_subtype'    => 'download',
				'sanitize_callback' => 'edd_sanitize_amount',
				'type'              => 'float',
				'description'       => __( 'The total earnings for the specified product', 'easy-digital-downloads' ),
			)
		);

		register_meta(
			'post',
			'_edd_download_sales',
			array(
				'object_subtype'    => 'download',
				'sanitize_callback' => array( $this, 'intval_wrapper' ),
				'type'              => 'float',
				'description'       => __( 'The number of sales for the specified product.', 'easy-digital-downloads' ),
			)
		);

		register_meta(
			'post',
			'edd_price',
			array(
				'object_subtype'    => 'download',
				'sanitize_callback' => array( $this, 'sanitize_price' ),
				'type'              => 'float',
				'description'       => __( 'The price of the product.', 'easy-digital-downloads' ),
				'show_in_rest'      => true,
			)
		);

		/**
		 * Even though this is an array, we're using 'object' as the type here. Since the variable pricing can be either
		 * 1 or 0 based for the array keys, we use the additional properties to avoid WP Core resetting the variable price IDs
		 */
		register_meta(
			'post',
			'edd_variable_prices',
			array(
				'object_subtype'    => 'download',
				'sanitize_callback' => array( $this, 'sanitize_variable_prices' ),
				'single'            => true,
				'type'              => 'object',
				'description'       => __( 'An array of variable prices for the product.', 'easy-digital-downloads' ),
				'show_in_rest'      => array(
					'schema' => array(
						'type'                 => 'object',
						'properties'           => array(),
						'additionalProperties' => array(
							'type'                 => 'object',
							'properties'           => array(
								'index'  => array(
									'type' => 'integer',
								),
								'name'   => array(
									'type' => 'string',
								),
								'amount' => array(
									'type' => 'number',
								),
							),
							'additionalProperties' => true,
						),
					),
				),
			)
		);

		register_meta(
			'post',
			'edd_download_files',
			array(
				'object_subtype'    => 'download',
				'sanitize_callback' => array( $this, 'sanitize_files' ),
				'type'              => 'array',
				'description'       => __( 'The files associated with the product, available for download.', 'easy-digital-downloads' ),
			)
		);

		register_meta(
			'post',
			'_edd_bundled_products',
			array(
				'object_subtype'    => 'download',
				'sanitize_callback' => array( $this, 'sanitize_array' ),
				'single'            => true,
				'type'              => 'array',
				'description'       => __( 'An array of product IDs to associate with a bundle.', 'easy-digital-downloads' ),
				'show_in_rest'      => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array(
							'type' => 'string',
						),
					),
				),
			)
		);

		register_meta(
			'post',
			'_edd_button_behavior',
			array(
				'object_subtype'    => 'download',
				'sanitize_callback' => 'sanitize_text_field',
				'type'              => 'string',
				'description'       => __( "Defines how this product's 'Purchase' button should behave, either add to cart or buy now", 'easy-digital-downloads' ),
				'show_in_rest'      => true,
			)
		);

		register_meta(
			'post',
			'_edd_default_price_id',
			array(
				'object_subtype'    => 'download',
				'sanitize_callback' => array( $this, 'intval_wrapper' ),
				'type'              => 'int',
				'description'       => __( 'When variable pricing is enabled, this value defines which option should be chosen by default.', 'easy-digital-downloads' ),
				'show_in_rest'      => true,
			)
		);
	}

	/**
	 * Register the meta for the edd_payment post type.
	 *
	 * @since  2.5
	 * @deprecated 3.2.4
	 * @return void
	 */
	public function register_payment_meta() {

		register_meta(
			'post',
			'_edd_payment_user_email',
			array(
				'object_subtype'    => 'edd_payment',
				'sanitize_callback' => 'sanitize_email',
				'type'              => 'string',
				'description'       => __( 'The email address associated with the purchase.', 'easy-digital-downloads' ),
			)
		);

		register_meta(
			'post',
			'_edd_payment_customer_id',
			array(
				'object_subtype'    => 'edd_payment',
				'sanitize_callback' => array( $this, 'intval_wrapper' ),
				'type'              => 'int',
				'description'       => __( 'The Customer ID associated with the payment.', 'easy-digital-downloads' ),
			)
		);

		register_meta(
			'post',
			'_edd_payment_user_id',
			array(
				'object_subtype'    => 'edd_payment',
				'sanitize_callback' => array( $this, 'intval_wrapper' ),
				'type'              => 'int',
				'description'       => __( 'The User ID associated with the payment.', 'easy-digital-downloads' ),
			)
		);

		register_meta(
			'post',
			'_edd_payment_user_ip',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'type'              => 'string',
				'description'       => __( 'The IP address the payment was made from.', 'easy-digital-downloads' ),
			)
		);

		register_meta(
			'post',
			'_edd_payment_purchase_key',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'type'              => 'string',
				'description'       => __( 'The unique purchase key for this payment.', 'easy-digital-downloads' ),
			)
		);

		register_meta(
			'post',
			'_edd_payment_total',
			array(
				'sanitize_callback' => 'edd_sanitize_amount',
				'type'              => 'float',
				'description'       => __( 'The purchase total for this payment.', 'easy-digital-downloads' ),
			)
		);

		register_meta(
			'post',
			'_edd_payment_mode',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'type'              => 'string',
				'description'       => __( 'Identifies if the purchase was made in Test or Live mode.', 'easy-digital-downloads' ),
			)
		);

		register_meta(
			'post',
			'_edd_payment_gateway',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'type'              => 'string',
				'description'       => __( 'The registered gateway that was used to process this payment.', 'easy-digital-downloads' ),
			)
		);

		register_meta(
			'post',
			'_edd_payment_meta',
			array(
				'sanitize_callback' => array( $this, 'sanitize_array' ),
				'type'              => 'array',
				'description'       => __( 'Array of payment meta that contains cart details, downloads, amounts, taxes, discounts, and subtotals, etc.', 'easy-digital-downloads' ),
			)
		);

		register_meta(
			'post',
			'_edd_payment_tax',
			array(
				'sanitize_callback' => 'edd_sanitize_amount',
				'type'              => 'float',
				'description'       => __( 'The total amount of tax paid for this payment.', 'easy-digital-downloads' ),
			)
		);

		register_meta(
			'post',
			'_edd_completed_date',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'type'              => 'string',
				'description'       => __( 'The date this payment was changed to the `completed` status.', 'easy-digital-downloads' ),
			)
		);
	}

	/**
	 * Wrapper for intval
	 * Setting intval as the callback was stating an improper number of arguments, this avoids that.
	 *
	 * @since  2.5
	 * @param  int $value The value to sanitize.
	 * @return int        The value sanitized to be an int.
	 */
	public function intval_wrapper( $value ) {
		return intval( $value );
	}

	/**
	 * Sanitize values that come in as arrays
	 *
	 * @since  2.5
	 * @param  array|string $value The value passed into the meta.
	 * @return array         The sanitized value.
	 */
	public function sanitize_array( $value = array() ) {

		if ( ! is_array( $value ) ) {

			if ( is_object( $value ) ) {
				$value = (array) $value;
			}

			if ( is_serialized( $value ) ) {

				preg_match( '/[oO]\s*:\s*\d+\s*:\s*"\s*(?!(?i)(stdClass))/', $value, $matches );
				if ( ! empty( $matches ) ) {
					return false;
				}

				$value = (array) maybe_unserialize( $value );

			}
		}

		return $value;
	}

	/**
	 * Perform some sanitization on the amount field including not allowing negative values by default
	 *
	 * @since  2.6.5
	 * @param  float $price The price to sanitize.
	 * @return float        A sanitized price
	 */
	public function sanitize_price( $price ) {

		$allow_negative_prices = apply_filters( 'edd_allow_negative_prices', false );

		if ( ! $allow_negative_prices && $price < 0 ) {
			$price = 0;
		}

		return edd_sanitize_amount( $price );
	}

	/**
	 * Sanitize the variable prices
	 *
	 * Ensures prices are correctly mapped to an array starting with an index of 0
	 *
	 * @since 2.5
	 * @param array $prices Variable prices.
	 * @return array $prices Array of the remapped variable prices
	 */
	public function sanitize_variable_prices( $prices = array() ) {
		$prices = $this->remove_blank_rows( $prices );

		if ( ! is_array( $prices ) ) {
			return array();
		}

		foreach ( $prices as $id => $price ) {

			if ( empty( $price['amount'] ) && empty( $price['name'] ) ) {

				unset( $prices[ $id ] );
				continue;

			} elseif ( empty( $price['amount'] ) ) {

				$price['amount'] = 0;

			}

			$prices[ $id ]['amount'] = $this->sanitize_price( $price['amount'] );
			$prices[ $id ]['name']   = sanitize_text_field( $price['name'] );

		}

		return $prices;
	}

	/**
	 * Sanitize the file downloads
	 *
	 * Ensures files are correctly mapped to an array starting with an index of 0
	 *
	 * @since 2.5
	 * @param array $files Array of all the file downloads.
	 * @return array $files Array of the remapped file downloads
	 */
	public function sanitize_files( $files = array() ) {
		$files = $this->remove_blank_rows( $files );

		// Files should always be in array format, even when there are none.
		if ( ! is_array( $files ) ) {
			$files = array();
		}

		// Clean up filenames to ensure whitespace is stripped.
		foreach ( $files as $id => $file ) {

			if ( ! empty( $files[ $id ]['file'] ) ) {
				$files[ $id ]['file'] = trim( $file['file'] );
			}

			if ( ! empty( $files[ $id ]['name'] ) ) {
				$files[ $id ]['name'] = sanitize_text_field( $file['name'] );
			}
		}

		// Make sure all files are rekeyed starting at 0.
		return $files;
	}

	/**
	 * Don't save blank rows.
	 *
	 * When saving, check the price and file table for blank rows.
	 * If the name of the price or file is empty, that row should not
	 * be saved.
	 *
	 * @since 2.5
	 * @param array $updated_meta Array of all the meta values.
	 * @return array $new New meta value with empty keys removed
	 */
	private function remove_blank_rows( $updated_meta ) {

		if ( is_array( $updated_meta ) ) {
			foreach ( $updated_meta as $key => $value ) {
				if ( empty( $value['name'] ) && empty( $value['amount'] ) && empty( $value['file'] ) ) {
					unset( $updated_meta[ $key ] );
				}
			}
		}

		return $updated_meta;
	}
}
EDD_Register_Meta::instance();
