<?php get_header(); ?>
	<script>
		function edd_begateway_gateway_payment(e) {
			// check if BeGateway library is loaded well
			if (typeof edd_begateway_gateway_payment_widget === "function" ) {
				e.preventDefault();
				edd_begateway_gateway_widget();
				return false;
			} else {
				return true;
			}
		}
	</script>
	<div class="entry-content">
		<div id="edd_checkout_wrap">
			<h2><?php echo esc_html( $begateway_payment_form['title'] ); ?></h2>
			<p><?php echo esc_html( $begateway_payment_form['description'] ); ?></p>
			<table id="edd_checkout_cart">
				<thead>
					<tr class="edd_cart_header_row">
						<?php do_action( 'edd_checkout_table_header_first' ); ?>
						<th class="edd_cart_item_name"><?php _e( 'Item Name', 'easy-digital-downloads' ); ?></th>
						<th class="edd_cart_item_price"><?php _e( 'Item Price', 'easy-digital-downloads' ); ?></th>
						<?php do_action( 'edd_checkout_table_header_last' ); ?>
					</tr>
				</thead>
				<tbody>
					<?php $cart_items = edd_get_cart_contents(); ?>
					<?php do_action( 'edd_cart_items_before' ); ?>
					<?php if ( $cart_items ) : ?>
						<?php foreach ( $cart_items as $key => $item ) : ?>
							<tr class="edd_cart_item" id="edd_cart_item_<?php echo esc_attr( $key ) . '_' . esc_attr( $item['id'] ); ?>" data-download-id="<?php echo esc_attr( $item['id'] ); ?>">
								<?php do_action( 'edd_checkout_table_body_first', $item ); ?>
								<td class="edd_cart_item_name">
									<?php
										if ( current_theme_supports( 'post-thumbnails' ) && has_post_thumbnail( $item['id'] ) ) {
											echo '<div class="edd_cart_item_image">';
												echo get_the_post_thumbnail( $item['id'], apply_filters( 'edd_checkout_image_size', array( 25,25 ) ) );
											echo '</div>';
										}
										$item_title = edd_get_cart_item_name( $item );
										echo '<span class="edd_checkout_cart_item_title">' . esc_html( $item_title ) . '</span>';

										/**
										 * Runs after the item in cart's title is echoed
										 * @since 2.6
										 *
										 * @param array $item Cart Item
										 * @param int $key Cart key
										 */
										do_action( 'edd_checkout_cart_item_title_after', $item, $key );
									?>
								</td>
								<td class="edd_cart_item_price">
									<?php
									echo edd_cart_item_price( $item['id'], $item['options'] );
									do_action( 'edd_checkout_cart_item_price_after', $item );
									?>
								</td>
								<?php do_action( 'edd_checkout_table_body_last', $item ); ?>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
					<?php do_action( 'edd_cart_items_middle' ); ?>
					<!-- Show any cart fees, both positive and negative fees -->
					<?php if( edd_cart_has_fees() ) : ?>
						<?php foreach( edd_get_cart_fees() as $fee_id => $fee ) : ?>
							<tr class="edd_cart_fee" id="edd_cart_fee_<?php echo $fee_id; ?>">

								<?php do_action( 'edd_cart_fee_rows_before', $fee_id, $fee ); ?>

								<td class="edd_cart_fee_label"><?php echo esc_html( $fee['label'] ); ?></td>
								<td class="edd_cart_fee_amount"><?php echo esc_html( edd_currency_filter( edd_format_amount( $fee['amount'] ) ) ); ?></td>
								<td>
									<?php if( ! empty( $fee['type'] ) && 'item' == $fee['type'] ) : ?>
										<a href="<?php echo esc_url( edd_remove_cart_fee_url( $fee_id ) ); ?>"><?php _e( 'Remove', 'easy-digital-downloads' ); ?></a>
									<?php endif; ?>

								</td>

								<?php do_action( 'edd_cart_fee_rows_after', $fee_id, $fee ); ?>

							</tr>
						<?php endforeach; ?>
					<?php endif; ?>

					<?php do_action( 'edd_cart_items_after' ); ?>
				</tbody>
				<tfoot>
					<?php if( edd_use_taxes() && ! edd_prices_include_tax() ) : ?>
						<tr class="edd_cart_footer_row edd_cart_subtotal_row"<?php if ( ! edd_is_cart_taxed() ) echo ' style="display:none;"'; ?>>
							<?php do_action( 'edd_checkout_table_subtotal_first' ); ?>
							<th colspan="<?php echo edd_checkout_cart_columns(); ?>" class="edd_cart_subtotal">
								<?php _e( 'Subtotal', 'easy-digital-downloads' ); ?>:&nbsp;<span class="edd_cart_subtotal_amount"><?php echo edd_cart_subtotal(); ?></span>
							</th>
							<?php do_action( 'edd_checkout_table_subtotal_last' ); ?>
						</tr>
					<?php endif; ?>

					<tr class="edd_cart_footer_row edd_cart_discount_row" <?php if( ! edd_cart_has_discounts() )  echo ' style="display:none;"'; ?>>
						<?php do_action( 'edd_checkout_table_discount_first' ); ?>
						<th colspan="<?php echo edd_checkout_cart_columns(); ?>" class="edd_cart_discount">
							<?php edd_cart_discounts_html(); ?>
						</th>
						<?php do_action( 'edd_checkout_table_discount_last' ); ?>
					</tr>

					<?php if( edd_use_taxes() ) : ?>
						<tr class="edd_cart_footer_row edd_cart_tax_row"<?php if( ! edd_is_cart_taxed() ) echo ' style="display:none;"'; ?>>
							<?php do_action( 'edd_checkout_table_tax_first' ); ?>
							<th colspan="<?php echo edd_checkout_cart_columns(); ?>" class="edd_cart_tax">
								<?php _e( 'Tax', 'easy-digital-downloads' ); ?>:&nbsp;<span class="edd_cart_tax_amount" data-tax="<?php echo edd_get_cart_tax( false ); ?>"><?php echo esc_html( edd_cart_tax() ); ?></span>
							</th>
							<?php do_action( 'edd_checkout_table_tax_last' ); ?>
						</tr>

					<?php endif; ?>

					<tr class="edd_cart_footer_row">
						<?php do_action( 'edd_checkout_table_footer_first' ); ?>
						<th colspan="<?php echo edd_checkout_cart_columns(); ?>" class="edd_cart_total"><?php _e( 'Total', 'easy-digital-downloads' ); ?>: <span class="edd_cart_amount" data-subtotal="<?php echo edd_get_cart_subtotal(); ?>" data-total="<?php echo edd_get_cart_total(); ?>"><?php edd_cart_total(); ?></span></th>
						<?php do_action( 'edd_checkout_table_footer_last' ); ?>
					</tr>
				</tfoot>
			</table>   
			<form action="<?php echo esc_url( $begateway_payment_form['payment_url'] ); ?>" method="post" id="begateway_payment_form" onSubmit="return edd_begateway_gateway_payment(event);">
				<input type="hidden" name="token" value="<?php echo esc_html( $begateway_payment_form['payment_token'] ); ?>">
				<input type="submit" class="button alt" id="submit_begateway_payment_form" value="<?php echo esc_html( $begateway_payment_form['make_payment_title'] ); ?>" />
				<a class="button cancel" href="<?php echo esc_url( $begateway_payment_form['cancel_payment_url'] ); ?>"><?php echo esc_html( $begateway_payment_form['cancel_payment_title'] ); ?></a>
			</form>
		</div>
	</div>
<?php get_footer(); ?>
