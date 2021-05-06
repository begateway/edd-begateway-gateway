<?php
/**
 * This file provides additional functions to simplify
 * work with Begateway gateway settings.
 *
 * @package EDD Begateway gateway
 */

defined( 'ABSPATH' ) || exit;

/**
 * Logs EDD Begateway gateway activity.
 *
 * @param string $message Log message.
 *
 * @return void
 */
function edd_begateway_gateway_log( $message ) {
	if ( edd_get_option( 'begateway_debug' ) ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( $message );
		}
	}
}

/**
 * Gets webhook endpoint URL.
 *
 * @return string Webhook endpoint URL.
 */
function edd_begateway_gateway_get_notify_url() {
	return rest_url( '/edd-begateway-gateway-webhook/v1/purchase_notification' );
}

/**
 * Checks if BeGateway gateway is enabled.
 *
 * @return true|false True if BeGateway gateway is enabled.
 */
function edd_begateway_gateway_is_enabled() {
	$gateways = edd_get_enabled_payment_gateways();
	return array_key_exists( EDD_BEGATEWAY_NAME, $gateways ) ? true : false;
}

/**
 * Initialize base begateway gateway configurations.
 *
 * @return void
 */
function edd_begateway_gateway_init_begateway_gateway() {
	\BeGateway\Settings::$gatewayBase  = 'https://' . edd_get_option( 'begateway_domain_gateway' );
	\BeGateway\Settings::$checkoutBase = 'https://' . edd_get_option( 'begateway_domain_checkout' );
	\BeGateway\Settings::$shopId       = edd_get_option( 'begateway_shop_id' );
	\BeGateway\Settings::$shopKey      = edd_get_option( 'begateway_secret_key' );
}

/**
 * Checks if a payment method supports selected action.
 * This function might be needed in the future when there will be different conditions for each action.
 *
 * @param string $action     One of three possible payment actions ( refund, cancel, capture ).
 * @param int    $payment_id EDD Payment ID.
 * @return boolean
 */
function edd_begateway_gateway_can_payment_method( $action, $payment_id ) {
	$payment_method = get_post_meta( $payment_id, '_begateway_transaction_payment_method', true );
	$not_allowed    = array();
	switch ( $action ) {
		case 'refund':
			array_push( $not_allowed, 'erip' );
			break;
		case 'cancel':
			array_push( $not_allowed, 'erip' );
			break;
		case 'capture':
			array_push( $not_allowed, 'erip' );
			break;
		default:
			// SHOW ERROR.
	}
	return ! in_array( $payment_method, $not_allowed, true );
}

/**
 * Validates data at the beginning of proccessing by ajax request handlers.
 *
 * @param bool $check_amount Indicates if it's needed to check $_REQUEST['amount'].
 *
 * @return string $errors Validatioin errors if any.
 */
function edd_begateway_gateway_validate_admin_ajax( $check_amount = false ) {
	if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'begateway' ) ) {
		exit( 'Invalid nonce' );
	}
	$errors = '';
	if ( ! isset( $_REQUEST['order_id'] ) ) {
		$errors .= __( 'Payment ID is not set', 'edd-begateway-gateway' ) . PHP_EOL;
	}
	if ( $check_amount ) {
		if ( ! isset( $_REQUEST['amount'] ) ) {
			$errors .= __( 'Operation amount is not set', 'edd-begateway-gateway' ) . PHP_EOL;
		}
	}
	if ( ! edd_begateway_gateway_is_enabled() ) {
		$errors .= __( 'BeGateway payment gateway is disabled', 'edd-begateway-gateway' ) . PHP_EOL;
	}
	return $errors;
}
