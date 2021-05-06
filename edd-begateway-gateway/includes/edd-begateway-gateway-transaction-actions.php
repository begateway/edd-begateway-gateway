<?php
/**
 * This file implements functionality related to transactions management.
 *
 * @link https://docs.bepaid.by/en/gateway/transactions
 *
 * @package EDD Begateway gateway
 */

defined( 'ABSPATH' ) || exit;

/**
 * Refunds payment.
 *
 * @param EDD_Payment $payment EDD Payment object.
 * @param EDD_Payment $amount  Amount of money to be captured.
 * @return true|WP_Error TRUE or WP_Error.
 */
function edd_begateway_gateway_refund( $payment, $amount ) {
	$payment_id      = $payment->ID;
	$transaction_uid = get_post_meta( $payment_id, '_begateway_transaction_id', true );
	$payment_amount  = edd_get_payment_amount( $payment_id );
	$gateway         = edd_get_payment_gateway( $payment_id );

	if ( EDD_BEGATEWAY_NAME === $gateway && edd_begateway_gateway_is_enabled() ) {
		if ( ! $transaction_uid ) {
			return new WP_Error( 'edd_begateway_gateway_error', __( 'No transaction reference UID to refund', 'edd-begateway-gateway' ) );
		}
		edd_begateway_gateway_log( 'Info: Starting to refund ' . $transaction_uid . ' of ' . $payment_id . PHP_EOL . ' -- ' . __FILE__ . ' - Line:' . __LINE__ );
		$response = edd_begateway_gateway_process_transaction(
			'refund',
			$transaction_uid,
			$amount,
			__( 'Refunded from Easy Digital Downloads', 'edd-begateway-gateway' )
		);
		if ( $response->isSuccess() ) {
			$refund_amount  = get_post_meta( $payment_id, '_begateway_transaction_refunded_amount', true ) ? get_post_meta( $payment_id, '_begateway_transaction_refunded_amount', true ) : 0;
			$refund_amount += $amount;
			update_post_meta( $payment_id, '_begateway_transaction_refunded_amount', $refund_amount );

			if ( $refund_amount >= $payment_amount ) {
				update_post_meta( $payment_id, '_begateway_transaction_refunded', 'yes' );
			}
			edd_begateway_gateway_log( 'Info: Refund was successful' . PHP_EOL . ' -- ' . __FILE__ . ' - Line:' . __LINE__ );
			$note = __( 'Refund completed', 'edd-begateway-gateway' ) . PHP_EOL .
							__( 'Transaction UID: ', 'edd-begateway-gateway' ) . $response->getUid();
			$payment->add_note( $note );
			return true;
		} else {
			$note = __( 'Error to refund transaction', 'edd-begateway-gateway' ) . PHP_EOL .
							__( 'Error: ', 'edd-begateway-gateway' ) . $response->getMessage();

			$payment->add_note( $note );
			edd_begateway_gateway_log( 'Issue: Refund has failed there has been an issue with the transaction.' . $response->getMessage() . PHP_EOL . ' -- ' . __FILE__ . ' - Line:' . __LINE__ );
			return new WP_Error( 'edd_begateway_gateway_error', __( 'Error to refund transaction', 'edd-begateway-gateway' ) );
		}
	}
}

/**
 * Cancel payment.
 *
 * @param EDD_Payment $payment EDD Payment object.
 * @return true|WP_Error TRUE or WP_Error.
 */
function edd_begateway_gateway_cancel( $payment ) {
	$payment_id      = $payment->ID;
	$transaction_uid = get_post_meta( $payment_id, '_begateway_transaction_id', true );
	$amount          = edd_get_payment_amount( $payment_id );
	$gateway         = edd_get_payment_gateway( $payment_id );
	if ( EDD_BEGATEWAY_NAME === $gateway && edd_begateway_gateway_is_enabled() ) {
		if ( ! $transaction_uid ) {
			return new WP_Error( 'edd_begateway_gateway_error', __( 'No transaction reference UID to refund', 'edd-begateway-gateway' ) );
		}
		edd_begateway_gateway_log( 'Info: Starting to void ' . $transaction_uid . ' of ' . $payment_id . PHP_EOL . ' -- ' . __FILE__ . ' - Line:' . __LINE__ );
		$response = edd_begateway_gateway_process_transaction(
			'void',
			$transaction_uid,
			$amount
		);
		if ( $response->isSuccess() ) {
			update_post_meta( $payment_id, '_begateway_transaction_voided', 'yes' );
			$note = __( 'Void complete', 'edd-begateway-gateway' ) . PHP_EOL .
							__( 'Transaction UID: ', 'edd-begateway-gateway' ) . $response->getUid();
			$payment->add_note( $note );
			edd_begateway_gateway_log( 'Info: Void was successful' . PHP_EOL . ' -- ' . __FILE__ . ' - Line:' . __LINE__ );
			return true;
		} else {
			$note = __( 'Error to void transaction', 'edd-begateway-gateway' ) . PHP_EOL .
							__( 'Error: ', 'edd-begateway-gateway' ) . $response->getMessage();
			edd_begateway_gateway_log( 'Issue: Void has failed there has been an issue with the transaction.' . $response->getMessage() . PHP_EOL . ' -- ' . __FILE__ . ' - Line:' . __LINE__ );
			return new WP_Error( 'edd_begateway_gateway_error', __( 'Error to void transaction.', 'edd-begateway-gateway' ) );
		}
	}
}

/**
 * Captures payment when the order is changed from on-hold to complete or processing.
 *
 * @param EDD_Payment $payment EDD Payment object.
 * @param EDD_Payment $amount  Amount of money to be captured.
 * @return true|WP_Error TRUE or WP_Error.
 */
function edd_begateway_gateway_capture( $payment, $amount ) {
	$payment_id      = $payment->ID;
	$transaction_uid = get_post_meta( $payment_id, '_begateway_transaction_id', true );
	$payment_amount  = edd_get_payment_amount( $payment_id );
	$gateway         = edd_get_payment_gateway( $payment_id );
	$captured        = get_post_meta( $payment_id, '_begateway_transaction_captured', true );
	if ( EDD_BEGATEWAY_NAME === $gateway && edd_begateway_gateway_is_enabled() ) {
		if ( ! $transaction_uid ) {
			return new WP_Error( 'edd_begateway_gateway_error', __( 'No transaction reference UID to refund', 'edd-begateway-gateway' ) );
		}
		if ( 'yes' === $captured ) {
			return new WP_Error( 'edd_begateway_gateway_error', __( 'Transaction is already captured', 'edd-begateway-gateway' ) );
		}
		edd_begateway_gateway_log( 'Info: Starting to capture ' . $transaction_uid . ' of ' . $payment_id . PHP_EOL . ' -- ' . __FILE__ . ' - Line:' . __LINE__ );
		$response = edd_begateway_gateway_process_transaction(
			'capture',
			$transaction_uid,
			$amount
		);
		if ( $response->isSuccess() ) {
			update_post_meta( $payment_id, '_begateway_transaction_captured', 'yes' );
			update_post_meta( $payment_id, '_begateway_transaction_captured_amount', $amount );
			edd_begateway_gateway_log( 'Info: Capture was successful' . PHP_EOL . ' -- ' . __FILE__ . ' - Line:' . __LINE__ );
			$note = __( 'Capture completed', 'edd-begateway-gateway' ) . PHP_EOL .
							__( 'Transaction UID: ', 'edd-begateway-gateway' ) . $response->getUid();
			$payment->add_note( $note );
			return true;
		} else {
			$note = __( 'Error to capture transaction', 'edd-begateway-gateway' ) . PHP_EOL .
							__( 'Error: ', 'edd-begateway-gateway' ) . $response->getMessage();
			$payment->add_note( $note );
			edd_begateway_gateway_log( 'Issue: Capture has failed there has been an issue with the transaction.' . $response->getMessage() . PHP_EOL . ' -- ' . __FILE__ . ' - Line:' . __LINE__ );
			return new WP_Error( 'edd_begateway_gateway_error', __( 'Error to capture transaction', 'edd-begateway-gateway' ) );
		}
	}
}

/**
 * Process BeGateway transaction.
 *
 * @param string $type Operation type.
 * @param string $uid Transaction UUID.
 * @param number $amount Amount.
 * @param string $reason The reason of this operation.
 *
 * @return Response BeGateway transaction responce.
 */
function edd_begateway_gateway_process_transaction( $type, $uid, $amount, $reason = '' ) {
	edd_begateway_gateway_init_begateway_gateway();
	$klass       = '\\BeGateway\\' . ucfirst( $type ) . 'Operation';
	$transaction = new $klass();
	$transaction->setParentUid( $uid );
	$transaction->money->setCurrency( edd_get_currency() );
	$transaction->money->setAmount( $amount );
	if ( ! empty( $reason ) ) {
		$transaction->setReason( $reason );
	}

	$response = $transaction->submit();

	return $response;
}
