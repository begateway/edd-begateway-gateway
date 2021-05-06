<?php
/**
 * This file provides a list of ajax callbacks for EDD Begateway gateway.
 *
 * @package EDD Begateway gateway
 */

/**
 * Handles Capture request.
 *
 * @link https://docs.bepaid.by/en/gateway/transactions/capture
 * @return void
 */
function ajax_edd_begateway_gateway_capture() {
	$errors = edd_begateway_gateway_validate_admin_ajax();
	if ( ! empty( $errors ) ) {
		wp_send_json_error( $errors );
		return;
	}
	$payment_id     = (int) $_REQUEST['order_id'];
	$payment_method = get_post_meta( $payment_id, '_begateway_transaction_payment_method', true );
	$payment        = new EDD_Payment( $payment_id );
	$amount         = edd_get_payment_amount( $payment_id );
	$result         = edd_begateway_gateway_capture( $payment, $amount );

	if ( ! is_wp_error( $result ) ) {
		wp_send_json_success( __( 'Capture success', 'edd-begateway-gateway' ) );
	} else {
		wp_send_json_error( $result->get_error_message() );
	}
}
add_action( 'wp_ajax_edd_begateway_gateway_capture', 'ajax_edd_begateway_gateway_capture' );

/**
 * Handles Void request.
 *
 * @link https://docs.bepaid.by/en/gateway/transactions/void
 * @return void
 */
function ajax_edd_begateway_gateway_cancel() {
	$errors = edd_begateway_gateway_validate_admin_ajax();
	if ( ! empty( $errors ) ) {
		wp_send_json_error( $errors );
		return;
	}
	$payment_id = (int) $_REQUEST['order_id'];
	$payment    = new EDD_Payment( $payment_id );

	// Check if the order is already cancelled ensure no more actions are made.
	if ( 'yes' === get_post_meta( $payment_id, '_begateway_transaction_voided', true ) ) {
		wp_send_json_success( __( 'Order already cancelled', 'edd-begateway-gateway' ) );
		return;
	}

	$result = edd_begateway_gateway_cancel( $payment );

	if ( ! is_wp_error( $result ) ) {
		wp_send_json_success( __( 'Cancel success', 'edd-begateway-gateway' ) );
	} else {
		wp_send_json_error( $result->get_error_message() );
	}
}
add_action( 'wp_ajax_edd_begateway_gateway_cancel', 'ajax_edd_begateway_gateway_cancel' );

/**
 * Handles Refund request.
 *
 * @link https://docs.bepaid.by/en/gateway/transactions/refund
 * @return void
 */
function ajax_edd_begateway_gateway_refund() {
	$errors = edd_begateway_gateway_validate_admin_ajax( true );
	if ( ! empty( $errors ) ) {
		wp_send_json_error( $errors );
		return;
	}

	$payment_id = (int) $_REQUEST['order_id'];
	$payment    = new EDD_Payment( $payment_id );
	$amount     = $_REQUEST['amount'];
	$amount     = str_replace( ',', '.', $amount );
	$amount     = floatval( $amount );
	$result     = edd_begateway_gateway_refund( $payment, $amount );

	if ( ! is_wp_error( $result ) ) {
		wp_send_json_success( __( 'Refund success', 'edd-begateway-gateway' ) );
	} else {
		wp_send_json_error( $result->get_error_message() );
	}
}
add_action( 'wp_ajax_edd_begateway_gateway_refund', 'ajax_edd_begateway_gateway_refund' );
add_action( 'wp_ajax_edd_begateway_gateway_refund_partly', 'ajax_edd_begateway_gateway_refund' );

/**
 * Handles Capture request.
 *
 * @link https://docs.bepaid.by/en/gateway/transactions/capture
 * @return void
 */
function ajax_edd_begateway_gateway_capture_partly() {
	edd_begateway_gateway_validate_admin_ajax( true );
	if ( ! empty( $errors ) ) {
		wp_send_json_error( $errors );
		return;
	}
	$amount     = $_REQUEST['amount'];
	$payment_id = (int) $_REQUEST['order_id'];
	$payment    = new EDD_Payment( $payment_id );
	$amount     = str_replace( ',', '.', $amount );
	$amount     = floatval( $amount );
	$result     = edd_begateway_gateway_capture( $payment, $amount );

	if ( ! is_wp_error( $result ) ) {
		wp_send_json_success( __( 'Capture partly success', 'edd-begateway-gateway' ) );
	} else {
		wp_send_json_error( $result->get_error_message() );
	}

}
add_action( 'wp_ajax_edd_begateway_gateway_capture_partly', 'ajax_edd_begateway_gateway_capture_partly' );
