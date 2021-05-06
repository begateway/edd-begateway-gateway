<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

?>

<ul class="order_action">
	<?php $order_is_cancelled = ( 'yes' === $vars['refunded'] ) || ( 'yes' === $vars['voided'] ); ?>
	<?php if ( $order_is_cancelled && ( 'cancelled' !== $vars['status'] ) ) : ?>
		<li class="edd-admin-box-inside begateway-admin-section-li-small">
			<?php echo __( 'Payment is cancelled', 'edd-begateway-gateway' ); ?>
		</li>
	<?php endif; ?>

	<li class="edd-admin-box-inside begateway-admin-section-li">
		<span class="begateway-balance__label label">
			<?php echo __( 'Remaining balance', 'edd-begateway-gateway' ); ?>:
		</span>
		<span class="begateway-balance__amount">
			<span class='begateway-balance__currency'>
				&nbsp;
			</span>
			<?php echo ( $vars['authorized_amount'] - $vars['settled_amount'] ); ?>
		</span>
	</li>
	<li class="edd-admin-box-inside begateway-admin-section-li">
		<span class="begateway-balance__label label">
			<?php echo __( 'Total authorized', 'edd-begateway-gateway' ); ?>:
		</span>
		<span class="begateway-balance__amount">
			<span class='begateway-balance__currency'>
				&nbsp;
			</span>
			<?php echo $vars['authorized_amount']; ?>
		</span>
	</li>
	<li class="edd-admin-box-inside begateway-admin-section-li">
		<span class="begateway-balance__label label">
			<?php echo __( 'Total captured', 'edd-begateway-gateway' ); ?>:
		</span>
		<span class="begateway-balance__amount">
			<span class='begateway-balance__currency'>
				&nbsp;
			</span>
			<?php echo $vars['settled_amount']; ?>
		</span>
	</li>
	<li class="edd-admin-box-inside begateway-admin-section-li">
		<span class="begateway-balance__label label">
			<?php echo __( 'Total refunded', 'edd-begateway-gateway' ); ?>:
		</span>
		<span class="begateway-balance__amount">
			<span class='begateway-balance__currency'>
				&nbsp;
			</span>
			<?php echo wc_price( $vars['refunded_amount'] ); ?>
		</span>
	</li>
	<li style='font-size: xx-small'>&nbsp;</li>



	<?php if ( $vars['settled_amount'] === 0 && ! in_array( $vars['status'], array( 'cancelled', 'created' ), true ) && ! $order_is_cancelled && $vars['can_capture'] ) : ?>
		<li class="edd-admin-box-inside begateway-full-width">
			<a class="button button-primary" data-action="begateway_capture" id="begateway_capture" data-nonce="<?php echo wp_create_nonce( 'begateway' ); ?>" data-order-id="<?php echo $vars['payment_id'] ?>" data-confirm="<?php echo __( 'You are about to CAPTURE this payment', 'edd-begateway-gateway' ); ?>">
				<?php echo sprintf( __( 'Capture full amount (%s)', 'edd-begateway-gateway' ), wc_price( $vars['authorized_amount'] ) ); ?>
			</a>
		</li>
	<?php endif; ?>

	<?php if ( $vars['settled_amount'] === 0 && ! in_array( $vars['status'], array( 'cancelled', 'created'), true ) && ! $order_is_cancelled && $vars['can_cancel'] ) : ?>
		<li class="edd-admin-box-inside begateway-full-width">
			<a class="button" data-action="begateway_cancel" id="begateway_cancel" data-confirm="<?php echo __( 'You are about to CANCEL this payment', 'edd-begateway-gateway' ); ?>" data-nonce="<?php echo wp_create_nonce( 'begateway' ); ?>" data-order-id="<?php echo $vars['payment_id']; ?>">
				<?php echo __( 'Cancel transaction', 'edd-begateway-gateway' ); ?>
			</a>
		</li>
		<li style='font-size: xx-small'>&nbsp;</li>
	<?php endif; ?>

	<?php if ( $vars['authorized_amount'] > $vars['settled_amount'] && ! in_array( $vars['status'], array( 'cancelled', 'created'), true ) && !$order_is_cancelled && ! $vars['is_captured'] && $vars['can_capture'] ): ?>
		<li class="edd-admin-box-inside begateway-admin-section-li-header">
			<?php echo __( 'Partly capture', 'edd-begateway-gateway' ); ?>
		</li>
		<li class="edd-admin-box-inside begateway-balance last label">
			<span class="begateway-balance__label label" style="margin-right: 0;">
				<?php echo __( 'Capture amount', 'edd-begateway-gateway' ); ?>:
			</span>
			<span class="begateway-partly_capture_amount">
				<input id="begateway-capture_partly_amount-field" class="begateway-capture_partly_amount-field" type="text" autocomplete="off" size="6" value="<?php echo ( $vars['authorized_amount'] - $vars['settled_amount'] ); ?>" />
			</span>
		</li>
		<li class="edd-admin-box-inside begateway-full-width">
			<a class="button" id="begateway_capture_partly" data-nonce="<?php echo wp_create_nonce( 'begateway' ); ?>" data-order-id="<?php echo $vars['payment_id']; ?>">
				<?php echo __( 'Capture specified amount', 'edd-begateway-gateway' ); ?>
			</a>
		</li>
		<li style='font-size: xx-small'>&nbsp;</li>
	<?php endif; ?>
	<?php if ( $vars['settled_amount'] > $vars['refunded_amount'] && ! in_array( $vars['status'], array( 'cancelled', 'created') ) && !$order_is_cancelled && $vars['can_refund'] ): ?>
		<li class=" edd-admin-box-inside begateway-admin-section-li-header">
			<?php echo __( 'Partly refund', 'edd-begateway-gateway' ); ?>
		</li>
		<li class="edd-admin-box-inside begateway-balance last">
			<span class="begateway-balance__label" style='margin-right: 0;'>
				<?php echo __( 'Refund amount', 'edd-begateway-gateway' ); ?>:
			</span>
			<span class="begateway-partly_refund_amount">
				<input id="begateway-refund_partly_amount-field" class="begateway-refund_partly_amount-field" type="text" size="6" autocomplete="off" value="<?php echo ( $vars['settled_amount'] - $vars['refunded_amount'] ) ?>" />
			</span>
		</li>
		<li class="edd-admin-box-inside begateway-full-width">
			<a class="button" id="begateway_refund_partly" data-nonce="<?php echo wp_create_nonce( 'begateway' ); ?>" data-order-id="<?php echo $vars['payment_id']; ?>">
				<?php echo __( 'Refund specified amount', 'edd-begateway-gateway' ); ?>
			</a>
		</li>
		<li style='font-size: xx-small'>&nbsp;</li>
	<?php endif; ?>

	<li class="edd-admin-box-inside begateway-admin-section-li-header-small label">
		<?php echo __( 'Payment method', 'edd-begateway-gateway' ) ?>
	</li>
	<li class="edd-admin-box-inside begateway-admin-section-li-small">
		<?php echo ucfirst( $vars['payment_method'] ); ?>
	</li>
	<li class="edd-admin-box-inside begateway-admin-section-li-header-small label">
		<?php echo __( 'Transaction UID', 'edd-begateway-gateway' ) ?>
	</li>
	<li class="edd-admin-box-inside begateway-admin-section-li-small">
		<?php echo $vars['transaction_id']; ?>
	</li>
	<?php if ( null != $vars['card_last_4'] ): ?>
		<li class="edd-admin-box-inside begateway-admin-section-li-header-small label">
			<?php echo __( 'Card number', 'edd-begateway-gateway' ); ?>
		</li>
		<li class="edd-admin-box-inside begateway-admin-section-li-small">
			<?php echo 'xxxx ' . $vars['card_last_4']; ?>
		</li>
		<li class="edd-admin-box-inside begateway-admin-section-li-header-small label">
			<?php echo __( 'Card brand', 'edd-begateway-gateway' ); ?>
		</li>
		<li class="edd-admin-box-inside begateway-admin-section-li-small">
			<?php echo ucfirst( $vars['card_brand'] ); ?>
		</li>
	<?php endif ?>
</ul>
