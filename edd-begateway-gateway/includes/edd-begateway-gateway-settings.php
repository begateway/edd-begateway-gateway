<?php
/**
 * This file provides Begateway gateway settings.
 *
 * @link https://docs.bepaid.by/en/gateway/transactions
 *
 * @package EDD Begateway gateway
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers EDD BeGateway Gateway settings for BeGateway gateway subsection.
 *
 * @param  array $gateway_settings  Gateway tab settings.
 * @return array                    Gateway tab settings with EDD BeGateway Gateway settings.
 */
function edd_begateway_gateway_register_gateway_settings( $gateway_settings ) {
	$gateway_settings[ EDD_BEGATEWAY_NAME ] = array(
		'begateway_settings'              => array(
			'id'   => 'begateway_settings',
			'name' => '<strong>' . __( 'BeGateway Settings', 'edd-begateway-gateway' ) . '</strong>',
			'type' => 'header',
		),
		'begateway_name'                  => array(
			'id'          => 'begateway_name',
			'name'        => __( 'Title', 'edd-begateway-gateway' ),
			'type'        => 'text',
			'desc'        => __( 'This is the title displayed to the user during checkout', 'edd-begateway-gateway' ),
			'allow_blank' => false,
			'std'         => __( 'BeGateway', 'edd-begateway-gateway' ),
		),
		'begateway_description'           => array(
			'id'   => 'begateway_description',
			'name' => __( 'Description', 'edd-begateway-gateway' ),
			'type' => 'textarea',
			'desc' => __( 'This is the description which the user sees during checkout', 'edd-begateway-gateway' ),
			'std'  => __( 'Visa, Mastercard', 'edd-begateway-gateway' ),
		),
		'begateway_shop_id'               => array(
			'id'          => 'begateway_shop_id',
			'name'        => __( 'Shop ID', 'edd-begateway-gateway' ),
			'type'        => 'text',
			'desc'        => __( 'Please enter your Shop Id.', 'edd-begateway-gateway' ),
			'allow_blank' => false,
			'std'         => '361',
		),
		'begateway_secret_key'            => array(
			'id'          => 'begateway_secret_key',
			'name'        => __( 'Secret key', 'edd-begateway-gateway' ),
			'type'        => 'text',
			'desc'        => __( 'Please enter your Shop secret key.', 'edd-begateway-gateway' ),
			'allow_blank' => false,
			'std'         => 'b8647b68898b084b836474ed8d61ffe117c9a01168d867f24953b776ddcb134d',
		),
		'begateway_domain_gateway'        => array(
			'id'          => 'begateway_domain_gateway',
			'name'        => __( 'Payment gateway domain', 'edd-begateway-gateway' ),
			'type'        => 'text',
			'desc'        => __( 'Please enter payment gateway domain of your payment processor', 'edd-begateway-gateway' ),
			'allow_blank' => false,
			'std'         => 'demo-gateway.begateway.com',
		),
		'begateway_domain_checkout'       => array(
			'id'          => 'begateway_domain_checkout',
			'name'        => __( 'Payment page domain', 'edd-begateway-gateway' ),
			'type'        => 'text',
			'desc'        => __( 'Please enter payment page domain of your payment processor', 'edd-begateway-gateway' ),
			'allow_blank' => false,
			'std'         => 'checkout.begateway.com',
		),
		'begateway_transaction_type'      => array(
			'id'      => 'begateway_transaction_type',
			'name'    => __( 'Transaction type', 'edd-begateway-gateway' ),
			'type'    => 'select',
			'options' => array(
				'payment'       => __( 'Payment', 'edd-begateway-gateway' ),
				'authorization' => __( 'Authorization', 'edd-begateway-gateway' ),
			),
			'desc'    => __( 'Select Payment (Authorization & Capture) or Authorization.', 'edd-begateway-gateway' ),
		),
		'begateway_enable_bankcard'       => array(
			'id'   => 'begateway_enable_bankcard',
			'name' => __( 'Enable Bankcard Payments', 'edd-begateway-gateway' ),
			'type' => 'checkbox',
			'desc' => __( 'This enables VISA/Mastercard and etc card payments', 'edd-begateway-gateway' ),
			'std'  => 1,
		),
		'begateway_enable_bankcard_halva' => array(
			'id'   => 'begateway_enable_bankcard_halva',
			'name' => __( 'Enable Halva card payments', 'edd-begateway-gateway' ),
			'type' => 'checkbox',
			'desc' => __( 'This enables Halva card payments', 'edd-begateway-gateway' ),
			'std'  => 0,
		),
		'begateway_enable_erip'           => array(
			'id'   => 'begateway_enable_erip',
			'name' => __( 'Enable ERIP payments', 'edd-begateway-gateway' ),
			'type' => 'checkbox',
			'desc' => __( 'This enables ERIP payments', 'edd-begateway-gateway' ),
			'std'  => 0,
		),
		'begateway_erip_service_no'       => array(
			'id'   => 'begateway_erip_service_no',
			'name' => __( 'ERIP service code', 'edd-begateway-gateway' ),
			'type' => 'text',
			'desc' => __( 'Enter ERIP service code provided you by your payment service provider', 'edd-begateway-gateway' ),
			'std'  => '99999999',
		),
		'begateway_payment_valid'         => array(
			'id'          => 'begateway_payment_valid',
			'name'        => __( 'Payment valid (minutes)', 'edd-begateway-gateway' ),
			'type'        => 'text',
			'desc'        => __( 'The value sets a period of time within which an order must be paid', 'edd-begateway-gateway' ),
			'allow_blank' => false,
			'std'         => '60',
		),
		'begateway_mode'                  => array(
			'id'      => 'begateway_mode',
			'name'    => __( 'Payment mode', 'edd-begateway-gateway' ),
			'type'    => 'select',
			'options' => array(
				'test' => __( 'Test', 'edd-begateway-gateway' ),
				'live' => __( 'Live', 'edd-begateway-gateway' ),
			),
			'desc'    => __( 'Select module payment mode', 'edd-begateway-gateway' ),
			'std'     => 'test',
		),
		'begateway_debug'                 => array(
			'id'   => 'begateway_debug',
			'name' => __( 'Debug Log', 'edd-begateway-gateway' ),
			'type' => 'checkbox',
			'std'  => 'no',
			'desc' =>  __( 'Log events', 'edd-begateway-gateway' ),
		),
	);
	return $gateway_settings;
}
add_filter( 'edd_settings_gateways', 'edd_begateway_gateway_register_gateway_settings', 10, 1 );

/**
 * Register the payment gateways setting section.
 *
 * @param  array $gateway_sections Array of sections for the gateways tab.
 * @return array                   Added BeGateways gateway into sub-sections.
 */
function edd_begateway_gateway_register_gateway_section( $gateway_sections ) {
	$gateway_sections[ EDD_BEGATEWAY_NAME ] = __( 'BeGateway', 'edd-begateway-gateway' );
	return $gateway_sections;
}
add_filter( 'edd_settings_sections_gateways', 'edd_begateway_gateway_register_gateway_section', 10, 1 );
