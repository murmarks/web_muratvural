<?php
/**
 * MemberPress provider class.
 *
 * @package WPCode\Pixel
 */

/**
 * MemberPress provider class.
 */
class WPCode_Pixel_Provider_MemberPress extends WPCode_Pixel_Provider {

	/**
	 * Name of provider.
	 *
	 * @var string
	 */
	public $name = 'MemberPress';

	/**
	 * The provider slug.
	 *
	 * @var string
	 */
	public $slug = 'memberpress';

	/**
	 * Array of events that are not supported by the current provider, so we can hide them with CSS.
	 *
	 * @var string[]
	 */
	public $unsupported_events = array(
		'view-content',
		'add-to-cart',
	);

	/**
	 * Should this provider be loaded?
	 *
	 * @return bool
	 */
	public function should_load() {
		return defined( 'MEPR_VERSION' ) && version_compare( MEPR_VERSION, '1.3.43', '>' );
	}

	/**
	 * Array of frontend locations used for tracking specific to this
	 * provider.
	 * Keys are provider-specific location names, values are the
	 * functions of each pixel type that should be run in that location.
	 *
	 * @see WPCode_Pixel_Auto_Insert_Type::get_snippets()
	 * @see WPCode_Pixel_Auto_Insert::load_pixel_snippets()
	 *
	 * @var string[]
	 */
	public $frontend_locations = array(
		'mepr-above-checkout-form' => 'begin_checkout',
		'site_wide_footer'         => 'purchase',
	);

	/**
	 * Array of locations used in the frontend relevant to this pixel.
	 *
	 * @var string[]
	 */
	public $single_frontend_locations = array();

	/**
	 * Server-side hooks for this provider.
	 *
	 * @return void
	 */
	public function add_server_hooks() {

		// When the checkout form is loaded, send a server event if the Token is set.
		add_action( 'mepr-above-checkout-form', array( $this, 'server_event_begin_checkout' ) );

		// Order completed event.
		add_action( 'mepr-txn-status-complete', array( $this, 'server_event_purchase' ) );
		add_action( 'mepr-txn-status-confirmed', array( $this, 'server_event_purchase' ) );

		add_action( 'mepr-signup', array( $this, 'maybe_track_signup' ) );
		// When the order is placed, attempt to save pixel-specific data to assign to the order when it later gets marked as completed.
		add_action( 'mepr-txn-status-pending', array( $this, 'server_order_saved' ), 10, 2 );
	}

	/**
	 * This allows users to easily enable tracking for all signups regardless of the payment status.
	 * using a simple filter.
	 *
	 * @param MeprTransaction $txn The transaction object.
	 *
	 * @return void
	 */
	public function maybe_track_signup( $txn ) {
		if ( apply_filters( 'wpcode_pixel_memberpress_track_all_signups', false ) ) {
			$this->server_event_purchase( $txn );
		}
	}

	/**
	 * Get checkout data specific to MemberPress.
	 *
	 * @return array
	 */
	public function get_checkout_data() {
		if ( ! class_exists( 'MeprProduct' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return array();
		}

		$current_post = MeprUtils::get_current_post();
		$product      = new MeprProduct( $current_post->ID );

		// Let's check if the current user already has a subscription.
		$user = MeprUtils::get_currentuserinfo();
		if ( false !== $user ) {
			$enabled_prd_ids = $user->get_enabled_product_ids( $product->ID );
			if ( ! empty( $enabled_prd_ids ) && ! $product->simultaneous_subscriptions ) {
				// User already has a subscription for this product and the checkout form will be hidden don't track this.
				return array();
			}
		}

		$data = array(
			'num_items' => 1,
			'currency'  => $this->get_currency(),
			'total'     => $product->price,
			'products'  => array(
				array(
					'id'         => $product->ID,
					'quantity'   => 1,
					'name'       => $product->post_title,
					'price'      => $product->price,
					'discount'   => 0,
					'categories' => array(),
				),
			),
		);

		return $data;
	}

	/**
	 * Get purchase data specific to MemberPress.
	 *
	 * @return array
	 */
	public function get_purchase_data() {
		if ( ! class_exists( 'MeprTransaction' ) ) {
			return array();
		}

		if ( ! isset( $_REQUEST['trans_num'] ) && ! isset( $_REQUEST['transaction_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return array();
		}

		$txn = isset( $_GET['trans_num'] ) ? MeprTransaction::get_one_by_trans_num( sanitize_text_field( wp_unslash( $_GET['trans_num'] ) ) ) : null;
		$txn = null === $txn && isset( $_GET['transaction_id'] ) ? MeprTransaction::get_one( (int) $_GET['transaction_id'] ) : $txn;
		$txn = ! empty( $txn->id ) ? new MeprTransaction( $txn->id ) : null;

		if ( $txn instanceof MeprTransaction && ! empty( $txn->id ) ) {

			if ( ( $order = $txn->order() ) instanceof MeprOrder ) {
				$order_bump_transactions = MeprTransaction::get_all_by_order_id_and_gateway( $order->id, $txn->gateway, $txn->id );
				$transactions            = array_merge( array( $txn ), $order_bump_transactions );
				$processed_sub_ids       = array();
				$order_bumps             = array();
				$amount                  = 0.00;

				foreach ( $transactions as $index => $transaction ) {
					if ( $transaction->is_one_time_payment() ) {
						$amount += (float) $transaction->total;

						if ( $index > 0 ) {
							$order_bumps[] = array( $transaction->product(), $transaction, null );
						}
					} else {
						$subscription = $transaction->subscription();

						if ( $subscription instanceof MeprSubscription ) {
							if ( in_array( (int) $subscription->id, $processed_sub_ids, true ) ) {
								continue;
							}
							$processed_sub_ids[] = (int) $subscription->id;
							if ( ( $subscription->trial && $subscription->trial_days > 0 && $subscription->txn_count < 1 ) || $transaction->txn_type == MeprTransaction::$subscription_confirmation_str ) {
								MeprTransactionsHelper::set_invoice_txn_vars_from_sub( $transaction, $subscription );
							}
							$amount += (float) $transaction->total;

							if ( $index > 0 ) {
								$order_bumps[] = array( $transaction->product(), $transaction, $subscription );
							}
						}
					}
				}
			} else {
				$sub = $txn->subscription();

				if ( $sub instanceof MeprSubscription && ( ( $sub->trial && $sub->trial_days > 0 && $sub->txn_count < 1 ) || $txn->txn_type == MeprTransaction::$subscription_confirmation_str ) ) {
					MeprTransactionsHelper::set_invoice_txn_vars_from_sub( $txn, $sub );
				}

				$amount = $txn->amount;
			}
		}

		$amount = floatval( $amount );

		$data = array(
			'num_items' => 1,
			'currency'  => $this->get_currency(),
			'total'     => $amount,
			'tax'       => $txn->tax_amount,
			'shipping'  => 0,
			'products'  => array(),
			'order_id'  => $txn->id,
		);

		if ( ! empty( $txn->coupon_id ) ) {
			$data['coupon'] = get_the_title( $txn->coupon_id );
		}

		$product            = new MeprProduct( $txn->product_id );
		$data['products'][] = array(
			'id'         => $txn->product_id,
			'quantity'   => 1,
			'name'       => $product->post_title,
			'discount'   => $product->price - $amount,
			'price'      => $amount,
			'categories' => array(),
		);

		return $data;

	}

	/**
	 * Get the WOO currency.
	 *
	 * @return string
	 */
	public function get_currency() {
		$mepr_options = MeprOptions::fetch();

		return ! empty( $mepr_options->currency_code ) ? $mepr_options->currency_code : 'USD';

	}

	/**
	 * Track a server purchase event specific to MemberPress.
	 *
	 * @param MeprTransaction $txn The transaction object.
	 *
	 * @return void
	 */
	public function server_event_purchase( $txn ) {
		if ( ! is_object( $txn ) ) {
			return;
		}

		// Don't report transactions that are not payments.
		if ( ! empty( $txn->txn_type ) && MeprTransaction::$payment_str !== $txn->txn_type ) {
			return;
		}

		$skip_renewals = apply_filters( 'wpcode_pixel_skip_renewals', true );
		if ( $skip_renewals && $txn->is_rebill() ) {
			return;
		}

		$user = $txn->user();

		// Build purchase event data from order.
		$data = array(
			'order_id'   => $txn->id,
			'num_items'  => 1,
			'currency'   => $this->get_currency(),
			'total'      => $txn->total,
			'products'   => array(),
			'user_id'    => $user->ID,
			'user_ip'    => $txn->get_meta( 'wpcode_pixel_user_ip', true ),
			'email'      => $user->user_email,
			'zip'        => get_user_meta( $user->ID, 'mepr-address-zip', true ),
			'city'       => get_user_meta( $user->ID, 'mepr-address-city', true ),
			'state'      => get_user_meta( $user->ID, 'mepr-address-state', true ),
			'country'    => get_user_meta( $user->ID, 'mepr-address-country', true ),
			'user_agent' => $txn->get_meta( 'wpcode_pixel_user_agent', true ),
			'first_name' => $user->first_name,
			'last_name'  => $user->last_name,
		);

		if ( ! empty( $txn->coupon_id ) ) {
			$data['coupon'] = get_the_title( $txn->coupon_id );
		}

		$product            = new MeprProduct( $txn->product_id );
		$data['products'][] = array(
			'id'       => $txn->product_id,
			'quantity' => 1,
			'name'     => $product->post_title,
			'discount' => $product->price - $txn->amount,
			'price'    => $txn->amount,
		);

		// Send purchase data to all pixels.
		wpcode_pixel()->auto_insert->send_server_event( 'purchase', $data, $this );
	}

	/**
	 * Store extra order data specific from each pixel.
	 *
	 * @param int   $order_id The order id to add meta to.
	 * @param array $data The array of key > value pairs to store.
	 *
	 * @return void
	 */
	public function store_extra_data( $order_id, $data ) {
		$txn = new MeprTransaction( $order_id );
		foreach ( $data as $key => $value ) {
			$txn->update_meta( $key, $value );
		}
		// Let's add some extra data we'll need for the purchase.
		$txn->update_meta( 'wpcode_pixel_user_ip', $this->get_user_ip() );
		$txn->update_meta( 'wpcode_pixel_user_agent', $this->get_user_agent() );
	}

	/**
	 * Get order meta specific to WooCommerce.
	 * Improved compatibility by not calling get_post_meta directly.
	 *
	 * @param int    $order_id The order to get meta for.
	 * @param string $key The key to get meta by.
	 *
	 * @return array|mixed|string
	 */
	public function get_order_meta( $order_id, $key ) {
		$txn = new MeprTransaction( $order_id );

		return $txn->get_meta( $key, true );
	}

	/**
	 * Get the user data for a logged-in user, if available.
	 *
	 * @return array
	 * @throws Exception
	 */
	public function get_server_user_data() {
		$data = array();

		if ( is_user_logged_in() ) {
			$user               = new MeprUser( get_current_user_id() );
			$data['user_id']    = get_current_user_id();
			$data['email']      = $user->user_email;
			$data['city']       = get_user_meta( $user->ID, 'mepr-address-city', true );
			$data['state']      = get_user_meta( $user->ID, 'mepr-address-state', true );
			$data['country']    = get_user_meta( $user->ID, 'mepr-address-country', true );
			$data['zip']        = get_user_meta( $user->ID, 'mepr-address-zip', true );
			$data['first_name'] = $user->first_name;
			$data['last_name']  = $user->last_name;
		}

		return $data;
	}

	/**
	 * Get the URL of the checkout page.
	 *
	 * @return string|void
	 */
	public function get_checkout_url() {
		// We should use the registration page for the current membership here.
		return '';
	}

	/**
	 * Send a server event when the checkout page is viewed.
	 *
	 * @param MeprProduct $product The product for which the checkout is being viewed.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function server_event_begin_checkout( $product ) {

		$data = $this->get_checkout_data();

		$user_data = array(
			'user_ip'    => $this->get_user_ip(),
			'user_agent' => $this->get_user_agent(),
		);

		$data = array_merge( $data, $user_data );
		$data = array_merge( $data, $this->get_server_user_data() );

		// Send purchase data to all pixels.
		wpcode_pixel()->auto_insert->send_server_event( 'begin_checkout', $data, $this );
	}
}

new WPCode_Pixel_Provider_MemberPress();
