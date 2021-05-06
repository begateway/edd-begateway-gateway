<?php
/**
 * This file implements webhooks provided by bePaid API.
 *
 * Functions in this file handles Begateway IPN (Instant Payment Notification).
 *
 * @link https://docs.bepaid.by/en/webhooks
 *
 * @package EDD Begateway gateway
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers a webhook for purchase notification.
 *
 * @return void
 */
function edd_begateway_gateway_register_webhook() {
	register_rest_route(
		'edd-begateway-gateway-webhook/v1',
		'/purchase_notification',
		array(
			'methods'  => 'POST,PUT',
			'callback' => 'edd_begateway_gateway_ipn_webhook_handler',
		)
	);
}

/**
 * Validates IPN amount against EDD Payment amount.
 *
 * @return bool True if validation is passed.
 */
function edd_begateway_gateway_validate_ipn_amount( $webhook, $payment_id ) {
	$money = new \BeGateway\Money();
	$money->setCurrency( edd_get_payment_currency_code( $payment_id ) );
	$money->setAmount( edd_get_payment_amount( $payment_id ) );
	$money->setCurrency( $webhook->getResponse()->transaction->currency );
	$money->setCents( $webhook->getResponse()->transaction->amount );

	$transaction = $webhook->getResponse()->transaction;

	return $transaction->currency === $money->getCurrency() &&
		$transaction->amount === $money->getCents();
}

/**
 * Handler functioin for purchase notification webhook.
 *
 * @todo: Consider disabling processing of IPN when BeGateway is disabled.
 *
 * @return WP_REST_Response REST response object.
 */
function edd_begateway_gateway_ipn_webhook_handler() {
	$webhook = new \BeGateway\Webhook();
	edd_begateway_gateway_init_begateway_gateway();
	edd_begateway_gateway_log( 'Received webhook json: ' . file_get_contents( 'php://input' ) );
	if ( $webhook->isAuthorized() ) {
		$type               = $webhook->getResponse()->transaction->type;
		$transaction_status = $webhook->getStatus();
		$transaction_uid    = $webhook->getUid();
		$purchase_key       = $webhook->getTrackingId();
		$payment_id         = edd_get_purchase_id_by_key( $purchase_key );
		$payment            = new EDD_Payment( $payment_id );
		$amount             = edd_get_payment_amount( $payment_id );
		if ( ! $payment ) {
			edd_begateway_gateway_log( 'EDD Payment for Transaction:' . $webhook->getUid() . ' was not found' );
			return new WP_REST_Response();
		}
		if ( ! edd_begateway_gateway_validate_ipn_amount( $webhook, $payment_id ) ) {
			edd_begateway_gateway_log(
				'----------- Invalid amount webhook --------------' . PHP_EOL .
					'Order No: ' . $webhook->getTrackingId() . PHP_EOL .
					'UID: ' . $webhook->getUid() . PHP_EOL .
				'--------------------------------------------'
			);
			return new WP_REST_Response();
		}

		edd_begateway_gateway_log(
			'Transaction type: ' . $type . PHP_EOL .
			'Payment status ' . $transaction_status . PHP_EOL .
			'UID: ' . $webhook->getUid() . PHP_EOL .
			'Message: ' . $webhook->getMessage()
		);

		if ( in_array( $type, array( 'payment', 'authorization' ), true ) ) {
			if ( $webhook->isSuccess() ) {
				if ( 'authorization' === $type ) {
					edd_update_payment_status( $payment_id, 'processing' );
					update_post_meta( $payment_id, '_begateway_transaction_captured', 'no' );
					update_post_meta( $payment_id, '_begateway_transaction_captured_amount', 0 );
				} else {
					edd_update_payment_status( $payment_id, 'processing' );
					update_post_meta( $payment_id, '_begateway_transaction_captured', 'yes' );
					update_post_meta( $payment_id, '_begateway_transaction_captured_amount', $amount );
				}
			} elseif ( $webhook->isFailed() ) {
				edd_update_payment_status( $payment_id, 'failed' );
				edd_insert_payment_note( $payment_id, $webhook->getMessage() );
			}
			// Add card data.
			$payment_method = $webhook->getPaymentMethod();

			if ( $payment_method && isset( $webhook->getResponse()->transaction->$payment_method->token ) ) {
				update_post_meta( $payment_id, '_begateway_transaction_payment_method', $payment_method );
				// Save card data.
				$card = $webhook->getResponse()->transaction->$payment_method;
				update_post_meta( $payment_id, '_begateway_card_last_4', $card->last_4 );
				update_post_meta( $payment_id, '_begateway_card_brand', 'master' !== $card->brand ? $card->brand : 'mastercard' );
			}

			update_post_meta( $payment_id, '_begateway_transaction_refunded_amount', 0 );
			update_post_meta( $payment_id, '_begateway_transaction_id', $transaction_uid );
		}
	} else {
		edd_begateway_gateway_log(
			'----------- Unauthorized webhook --------------' . PHP_EOL .
				'Order No: ' . $webhook->getTrackingId() . PHP_EOL .
				'UID: ' . $webhook->getUid() . PHP_EOL .
			'--------------------------------------------'
		);
	}
	return new WP_REST_Response();
}
add_action( 'rest_api_init', 'edd_begateway_gateway_register_webhook' );
