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
 * Adds message that will be shown in admin panel.
 *
 * @param string $message Message text.
 * @param string $type    Message type.
 * @return void
 */
function edd_begateway_gateway_add_message( $message, $type = 'notice' ) {
	$messages = get_transient( 'edd_begateway_gateway_messages' );
	if ( false === $messages ) {
		$messages = array();
	}
	$messages[ $type ] = $message;
	set_transient( 'edd_begateway_gateway_messages', $messages, 0 );
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
