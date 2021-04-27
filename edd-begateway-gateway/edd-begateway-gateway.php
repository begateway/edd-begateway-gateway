<?php
/**
 * Plugin Name: Easy Digital Downloads - BeGateway Gateway
 * Description: BeGateway gateway extension for Easy Digital Downloads.
 * Plugin URI: https://github.com/begateway/edd-begateway-gateway
 * Author: BeGateway development team
 * Version:     1.0
 * Text Domain: edd-begateway-gateway
 * Domain Path: /languages/
 */

const EDD_BEGATEWAY_NAME = 'begateway';

require_once dirname( __FILE__ ) . '/begateway-api-php/lib/BeGateway.php';
require_once dirname( __FILE__ ) . '/includes/edd-begateway-gateway-settings.php';
require_once dirname( __FILE__ ) . '/includes/edd-begateway-gateway-helpers.php';
require_once dirname( __FILE__ ) . '/includes/edd-begateway-gateway-webhook.php';
require_once dirname( __FILE__ ) . '/includes/edd-begateway-gateway-transaction-actions.php';


// Make sure we don't expose any info if called directly.
if ( ! function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

/**
 * Internationalization
 *
 * @return void
 */
function edd_begateway_gateway_textdomain() {
	load_plugin_textdomain( 'edd-begateway-gateway', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'edd_begateway_gateway_textdomain' );

/**
 * Defines location of *.mo files for edd-begateway-gateway textdomain.
 *
 * @param string $mofile - Default *.mo file location path.
 * @param string $domain - Text domain name.
 *
 * @return string Path to *.mo file.
 */
function edd_begateway_gateway_define_mo_files( $mofile, $domain ) {
	if ( 'edd-begateway-gateway' === $domain && false !== strpos( $mofile, WP_LANG_DIR . '/plugins/' ) ) {
		$locale = apply_filters( 'plugin_locale', determine_locale(), $domain );
		$mofile = WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __FILE__ ) ) . '/languages/' . $domain . '-' . $locale . '.mo';
	}
	return $mofile;
}
add_filter( 'load_textdomain_mofile', 'edd_begateway_gateway_define_mo_files', 10, 2 );

/**
 * Adds EDD BeGateway gateway templates dir to the list of template paths.
 *
 * @param array $file_paths A list of template paths.
 * @return array $file_paths Updated list of template paths.
 */
function edd_begateway_gateway_define_templates_dir( $file_paths ) {
	$path            = plugin_dir_path( __FILE__ ) . 'templates';
	$file_paths[101] = $path;
	return $file_paths;
}
add_filter( 'edd_template_paths', 'edd_begateway_gateway_define_templates_dir' );
/**
 * Registers the gateway.
 *
 * @param array $gateways Payment gateways.
 * @return array
 */
function edd_begateway_gateway_gateways( $gateways ) {
	$gateways[ EDD_BEGATEWAY_NAME ] = array(
		'admin_label'    => __( 'BeGateway', 'edd-begateway-gateway' ),
		'checkout_label' => __( 'BeGateway', 'edd-begateway-gateway' ),
	);
	return $gateways;
}
add_filter( 'edd_payment_gateways', 'edd_begateway_gateway_gateways' );

/**
 * Remove CC Form for BeGateway gateway.
 *
 * BeGateway does not need a CC form, so remove it.
 *
 * @return void
 */
function edd_begateway_gateway_remove_cc_form() {
	do_action( 'edd_after_cc_fields' );
}
add_action( 'edd_begateway_cc_form', 'edd_begateway_gateway_remove_cc_form' );

/**
 * Sets all fields required by BeGateway as required on purchase form.
 *
 * @param  array $fields An array of fields that should be required.
 *
 * @return array $fields The required fields
 */
function edd_begateway_gateway_set_required_fields( $fields ) {

	$fields['card_zip']        = array(
		'error_id'      => 'invalid_zip_code',
		'error_message' => __( 'Please enter your zip / postal code', 'edd-begateway-gateway' ),
	);
	$fields['billing_country'] = array(
		'error_id'      => 'invalid_country',
		'error_message' => __( 'Please select your billing country', 'edd-begateway-gateway' ),
	);
	$fields['card_address']    = array(
		'error_id'      => 'invalid_card_address',
		'error_message' => __( 'Please enter billing address', 'edd-begateway-gateway' ),
	);
	$fields['card_city']       = array(
		'error_id'      => 'invalid_card_city',
		'error_message' => __( 'Please enter billing city', 'edd-begateway-gateway' ),
	);

	return $fields;
}
add_filter( 'edd_purchase_form_required_fields', 'edd_begateway_gateway_set_required_fields' );

/**
 * Registers and adds JS libs.
 *
 * @todo: Settings.
 * @param array $data - Checkout data.
 * @see https://docs.bepaid.by/ru/widget/widget.
 */
function enqueue_widget_scripts( $data ) {
	$url    = explode( '.', \BeGateway\Settings::$checkoutBase );
	$url[0] = 'js';
	$url    = 'https://' . implode( '.', $url ) . '/widget/be_gateway.js';

	wp_register_script( 'edd_begateway_gateway_widget', $url, null, null, false );
	wp_register_script(
		'edd_begateway_gateway_widget_start',
		WP_PLUGIN_URL . '/edd-begateway-gateway/js/script.js',
		array( 'edd_begateway_gateway_widget' ),
		null,
		false
	);

	wp_localize_script(
		'edd_begateway_gateway_widget_start',
		'edd_begateway_gateway_checkout_vars',
		$data
	);

	wp_enqueue_script( 'edd_begateway_gateway_widget_start' );
}

/**
 * Process PayPal Purchase
 *
 * @see: https://docs.bepaid.by/en/checkout/payment-token
 * @see: https://docs.bepaid.by/en/widget/widget
 * @param array $purchase_data Purchase Data.
 * @return void
 */
function edd_begateway_gateway_purchase( $purchase_data ) {

	if ( ! wp_verify_nonce( $purchase_data['gateway_nonce'], 'edd-gateway' ) ) {
		wp_die( __( 'Nonce verification has failed', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	// Get up BeGateway settiings.
	$begateway_settings = array(
		'shop_id'          => edd_get_option( 'begateway_shop_id' ),
		'secret_key'       => edd_get_option( 'begateway_secret_key' ),
		'gateway_domain'   => edd_get_option( 'begateway_domain_gateway' ),
		'checkout_domain'  => edd_get_option( 'begateway_domain_checkout' ),
		'transaction_type' => edd_get_option( 'begateway_transaction_type' ),
		'test_mode'        => 'test' === edd_get_option( 'begateway_mode' ) ? true : false,
		'bankcard_enabled' => (bool) edd_get_option( 'begateway_enable_bankcard' ),
		'halva_enabled'    => (bool) edd_get_option( 'begateway_enable_bankcard_halva' ),
		'erip_enabled'     => (bool) edd_get_option( 'begateway_enable_erip' ),
		'erip_service_no'  => edd_get_option( 'begateway_erip_service_no' ),
	);

	// Collect payment data.
	$payment_data = array(
		'price'        => $purchase_data['price'],
		'date'         => $purchase_data['date'],
		'user_email'   => $purchase_data['user_email'],
		'purchase_key' => $purchase_data['purchase_key'],
		'currency'     => edd_get_currency(),
		'downloads'    => $purchase_data['downloads'],
		'user_info'    => $purchase_data['user_info'],
		'cart_details' => $purchase_data['cart_details'],
		'gateway'      => EDD_BEGATEWAY_NAME,
		'status'       => ! empty( $purchase_data['buy_now'] ) ? 'private' : 'pending',
	);

	// Record the pending payment.
	$payment = edd_insert_payment( $payment_data );
	// Check payment.
	if ( $payment ) {
		edd_begateway_gateway_init_begateway_gateway();
		// Initialize BeGateway token.
		$token = new \BeGateway\GetPaymentToken();
		if ( 'authorization' === $begateway_settings['transaction_type'] ) {
			$token->setAuthorizationTransactionType();
		}
		$token->money->setCurrency( $payment_data['currency'] );
		$token->money->setAmount( $payment_data['price'] );

		$token->setDescription( __( 'Order', 'edd-begateway-gateway' ) . ' # ' . $payment_data['purchase_key'] );
		$token->setTrackingId( $payment_data['purchase_key'] );

		$card_data = $purchase_data['card_info'];
		$user_info = $purchase_data['user_info'];
		// Set user data.
		$token->customer->setFirstName( $user_info['first_name'] );
		$token->customer->setLastName( $user_info['last_name'] );
		$token->customer->setEmail( $user_info['email'] );
		// Set additional card data.
		$token->customer->setCountry( $card_data['card_country'] );
		$token->customer->setCity( $card_data['card_city'] );
		$token->customer->setZip( $card_data['card_zip'] );
		$token->customer->setAddress( $card_data['card_address'] . $card_data['card_address_2'] );
		if ( in_array( $card_data['card_country'], array( 'US', 'CA' ), true ) ) {
			$token->customer->setState( $card_data['card_state'] );
		}
		// Set callback URLs.
		$success_url  = get_permalink( edd_get_option( 'success_page', '' ) );
		$failure_page = get_permalink( edd_get_option( 'failure_page', '' ) );
		$cancel_url   = get_permalink( edd_get_option( 'purchase_page', '' ) );
		$token->setSuccessUrl( esc_url_raw( $success_url ) );
		$token->setFailUrl( esc_url_raw( $failure_page ) );
		$token->setCancelUrl( esc_url_raw( $cancel_url ) );
		$token->setDeclineUrl( esc_url_raw( $cancel_url ) );

    $notification_url = edd_begateway_gateway_get_notify_url();
    $notification_url = str_replace( '0.0.0.0:8085', 'webhook.begateway.com:8443', $notification_url );

		$token->setNotificationUrl( $notification_url );

		$date = strtotime( $payment_data['date'] );
		$token->setExpiryDate( date( 'c', $date * 60 + time() + 1) );

		$lang = explode( '_', get_locale() );
		$lang = $lang[0];
		$token->setLanguage( $lang );

		if ( $begateway_settings['bankcard_enabled'] ) {
			$cc = new \BeGateway\PaymentMethod\CreditCard();
			$token->addPaymentMethod( $cc );
		}

		if ( $begateway_settings['halva_enabled'] ) {
			$halva = new \BeGateway\PaymentMethod\CreditCardHalva();
			$token->addPaymentMethod( $halva );
		}

		if ( $begateway_settings['erip_enabled'] ) {
			$erip = new \BeGateway\PaymentMethod\Erip(
				array(
					'order_id'       => $payment_data['purchase_key'],
					'account_number' => $payment_data['purchase_key'],
					'service_no'     => $begateway_settings['erip_service_no'],
				)
			);
			$token->addPaymentMethod( $erip );
		}

		if ( $begateway_settings['test_mode'] ) {
			$token->setTestMode( true );
		}

		$token->additional_data->setContract( array( 'recurring', 'card_on_file' ) );

		$response = $token->submit();
		if ( ! $response->isSuccess() ) {
			// Add some error message.
			edd_begateway_gateway_log( 'BeGateway token error:  ' . $response->getMessage() . PHP_EOL . ' -- ' . __FILE__ . ' - Line:' . __LINE__ );
			return;
		}
		$payment_url = $response->getRedirectUrlScriptName();
		enqueue_widget_scripts(
			array(
				'checkout_url' => \BeGateway\Settings::$checkoutBase,
				'token'        => $response->getToken(),
				'cancel_url'   => esc_url_raw( $cancel_url ),
			)
		);

		$begateway_payment_form_variables = array(
			'title'                => edd_get_option( 'begateway_name' ),
			'description'          => edd_get_option( 'begateway_description' ),
			'payment_url'          => $payment_url,
			'payment_token'        => $response->getToken(),
			'cancel_payment_url'   => esc_url_raw( $cancel_url ),
			'make_payment_title'   => __( 'Make payment', 'edd-begateway-gateway' ),
			'cancel_payment_title' => __( 'Cancel order', 'edd-begateway-gateway' ),

		);
		set_query_var( 'begateway_payment_form', $begateway_payment_form_variables );
		edd_get_template_part( 'edd-begateway-gateway', 'processing' );
	} else {
		// @todo: Add error message.
		edd_send_back_to_checkout();
	}

}
add_action( 'edd_gateway_begateway', 'edd_begateway_gateway_purchase' );

/**
 * Add possibility to show begateway transaction ID at order details page.
 *
 * @param int $payment_id EDD Payment ID.
 * @return void
 */
function edd_begateway_gateway_add_order_detail( $payment_id ) {
	$gateway = edd_get_payment_gateway( $payment_id );
	if ( EDD_BEGATEWAY_NAME !== $gateway ) {
		return;
	}

	$transaction_id_value = get_post_meta( $payment_id, '_begateway_transaction_id', true );
	$transaction_id       = $transaction_id_value ? $transaction_id_value : '------';
	echo '<div class="edd-admin-box-inside">
					<p>
						<span class="label">' . esc_html( __( 'Transaction UID', 'edd-begateway-gateway' ) ) . ':</span>
						<span>' . esc_html( $transaction_id ) . '</span>
					</p>
				</div>
	';
}

add_action( 'edd_view_order_details_payment_meta_after', 'edd_begateway_gateway_add_order_detail' );

/**
 * Adds EDD BeGateway gateway messages to admin notices.
 */
function edd_begateway_gateway_messages() {
	$messages = get_transient( 'edd_begateway_gateway_messages' );
	if ( false !== $messages && ! empty( $messages ) ) {
		foreach ( $messages as $type => $message ) {
			echo '<div class="' . esc_html( $type ) . ' is-dismissible"><p><strong>' . esc_html( $message ) . '</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
		}
	}
	delete_transient( 'edd_begateway_gateway_messages' );

}
add_action( 'admin_notices', 'edd_begateway_gateway_messages' );
