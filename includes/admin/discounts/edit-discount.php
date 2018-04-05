<?php
/**
 * Edit Discount Page
 *
 * @package     EDD
 * @subpackage  Admin/Discounts
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Bail if no discount passed
if ( ! isset( $_GET['discount'] ) || ! is_numeric( $_GET['discount'] ) ) {
	wp_die( __( 'Something went wrong.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 400 ) );
}

// Load discount
$discount_id = absint( $_GET['discount'] );

/** @var EDD_Discount */
$discount = edd_get_discount( $discount_id );

// Bail if discount does not exist
if ( empty( $discount ) ) {
	wp_die( __( 'Something went wrong.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 400 ) );
}

// Setup discount vars
$product_requirements = $discount->get_product_reqs();
$excluded_products    = $discount->get_excluded_products();
$condition            = $discount->get_product_condition();
$single_use           = $discount->get_once_per_customer();
$flat_display         = ( 'flat'    === $discount->get_type() ) ? '' : ' style="display:none;"';
$percent_display      = ( 'percent' === $discount->get_type() ) ? '' : ' style="display:none;"';
$condition_display    = ! empty( $product_requirements )        ? '' : ' style="display:none;"';

// Output
?><div class="wrap">
	<h1><?php _e( 'Edit Discount', 'easy-digital-downloads' ); ?></h1>

	<form id="edd-edit-discount" action="" method="post">

		<?php do_action( 'edd_edit_discount_form_top', $discount->id, $discount ); ?>

		<table class="form-table">
			<tbody>

			<?php do_action( 'edd_edit_discount_form_before_name', $discount->id, $discount ); ?>

			<tr>
				<th scope="row" valign="top">
					<label for="edd-name"><?php _e( 'Name', 'easy-digital-downloads' ); ?></label>
				</th>
				<td>
					<input name="name" required="required" id="edd-name" type="text" value="<?php echo esc_attr( stripslashes( $discount->name ) ); ?>" />
					<p class="description"><?php _e( 'The name of this discount', 'easy-digital-downloads' ); ?></p>
				</td>
			</tr>

			<?php do_action( 'edd_edit_discount_form_before_code', $discount->id, $discount ); ?>

			<tr>
				<th scope="row" valign="top">
					<label for="edd-code"><?php _e( 'Code', 'easy-digital-downloads' ); ?></label>
				</th>
				<td>
					<input type="text" required="required" id="edd-code" name="code" value="<?php echo esc_attr( $discount->code ); ?>" pattern="[a-zA-Z0-9-_]+" class="code" />
					<p class="description"><?php _e( 'Enter a code for this discount, such as 10PERCENT. Only alphanumeric characters are allowed.', 'easy-digital-downloads' ); ?></p>
				</td>
			</tr>

			<?php do_action( 'edd_edit_discount_form_before_type', $discount->id, $discount ); ?>

			<tr>
				<th scope="row" valign="top">
					<label for="edd-type"><?php _e( 'Type', 'easy-digital-downloads' ); ?></label>
				</th>
				<td>
					<select name="type" id="edd-type">
						<option value="percent" <?php selected( $discount->type, 'percent' ); ?>><?php _e( 'Percentage', 'easy-digital-downloads' ); ?></option>
						<option value="flat"<?php selected( $discount->type, 'flat' ); ?>><?php _e( 'Flat amount', 'easy-digital-downloads' ); ?></option>
					</select>
					<p class="description"><?php _e( 'The kind of discount to apply for this discount.', 'easy-digital-downloads' ); ?></p>
				</td>
			</tr>

			<?php do_action( 'edd_edit_discount_form_before_amount', $discount->id, $discount ); ?>

			<tr>
				<th scope="row" valign="top">
					<label for="edd-amount"><?php _e( 'Amount', 'easy-digital-downloads' ); ?></label>
				</th>
				<td>
					<input type="text" class="edd-price-field" required="required" id="edd-amount" name="amount" value="<?php echo edd_sanitize_amount( $discount->amount ); ?>" />
					<p class="description edd-amount-description flat"<?php echo $flat_display; ?>><?php printf( __( 'Enter the discount amount in %s', 'easy-digital-downloads' ), edd_get_currency() ); ?></p>
					<p class="description edd-amount-description percent"<?php echo $percent_display; ?>><?php _e( 'Enter the discount percentage. 10 = 10%', 'easy-digital-downloads' ); ?></p>
				</td>
			</tr>

			<?php do_action( 'edd_edit_discount_form_before_products', $discount->id, $discount ); ?>

			<tr>
				<th scope="row" valign="top">
					<label for="edd-products"><?php printf( __( '%s Requirements', 'easy-digital-downloads' ), edd_get_label_singular() ); ?></label>
				</th>
				<td>
					<p>
						<?php echo EDD()->html->product_dropdown( array(
							'name'        => 'product_reqs[]',
							'id'          => 'edd-products',
							'selected'    => $product_requirements,
							'multiple'    => true,
							'chosen'      => true,
							'placeholder' => sprintf( __( 'Select one or more %s', 'easy-digital-downloads' ), edd_get_label_plural() )
						) ); ?><br/>
					</p>
					<div id="edd-discount-product-conditions"<?php echo $condition_display; ?>>
						<p>
							<select id="edd-product-condition" name="product_condition">
								<option value="all"<?php selected( 'all', $condition ); ?>><?php printf( __( 'Cart must contain all selected %s', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></option>
								<option value="any"<?php selected( 'any', $condition ); ?>><?php printf( __( 'Cart needs one or more of the selected %s', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></option>
							</select>
						</p>
						<p>
							<label>
								<input type="radio" class="tog" name="scope" value="global"<?php checked( 'global', $discount->scope ); ?>/>
								<?php _e( 'Apply discount to entire purchase.', 'easy-digital-downloads' ); ?>
							</label><br/>
							<label>
								<input type="radio" class="tog" name="scope" value="not_global"<?php checked( 'not_global', $discount->scope ); ?>/>
								<?php printf( __( 'Apply discount only to selected %s.', 'easy-digital-downloads' ), edd_get_label_plural() ); ?>
							</label>
						</p>
					</div>
					<p class="description"><?php printf( __( 'Select %s relevant to this discount. If left blank, this discount can be used on any product.', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></p>
				</td>
			</tr>

			<?php do_action( 'edd_edit_discount_form_before_excluded_products', $discount->id, $discount ); ?>

			<tr>
				<th scope="row" valign="top">
					<label for="edd-excluded-products"><?php printf( __( 'Excluded %s', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></label>
				</th>
				<td>
					<?php echo EDD()->html->product_dropdown( array(
						'name'        => 'excluded_products[]',
						'id'          => 'excluded_products',
						'selected'    => $excluded_products,
						'multiple'    => true,
						'chosen'      => true,
						'placeholder' => sprintf( __( 'Select one or more %s', 'easy-digital-downloads' ), edd_get_label_plural() )
					) ); ?><br/>
					<p class="description"><?php printf( __( '%s that this discount code cannot be applied to.', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></p>
				</td>
			</tr>

			<?php do_action( 'edd_edit_discount_form_before_start', $discount->id, $discount ); ?>

			<tr>
				<th scope="row" valign="top">
					<label for="edd-start"><?php _e( 'Start date', 'easy-digital-downloads' ); ?></label>
				</th>
				<td>
					<input name="start_date" id="edd-start" type="text" value="<?php echo '0000-00-00 00:00:00' == $discount->start_date ? '' : esc_attr( $discount->start_date ); ?>"  class="edd_datepicker"/>
					<p class="description"><?php _e( 'Enter the start date for this discount code in the format of mm/dd/yyyy. For no start date, leave blank. If entered, the discount can only be used after or on this date.', 'easy-digital-downloads' ); ?></p>
				</td>
			</tr>

			<?php do_action( 'edd_edit_discount_form_before_expiration', $discount->id, $discount ); ?>

			<tr>
				<th scope="row" valign="top">
					<label for="edd-expiration"><?php _e( 'Expiration date', 'easy-digital-downloads' ); ?></label>
				</th>
				<td>
					<input name="end_date" id="edd-expiration" type="text" value="<?php echo '0000-00-00 00:00:00' == $discount->end_date ? '' : esc_attr( $discount->end_date ); ?>"  class="edd_datepicker" data-format="yyyy-mm-dd"/>
					<p class="description"><?php _e( 'Enter the expiration date for this discount code in the format of mm/dd/yyyy. For no expiration, leave blank', 'easy-digital-downloads' ); ?></p>
				</td>
			</tr>

			<?php do_action( 'edd_edit_discount_form_before_max_uses', $discount->id, $discount ); ?>

			<tr>
				<th scope="row" valign="top">
					<label for="edd-max-uses"><?php _e( 'Max Uses', 'easy-digital-downloads' ); ?></label>
				</th>
				<td>
					<input type="text" id="edd-max-uses" name="max_uses" value="<?php echo esc_attr( $discount->max_uses ); ?>" style="width: 40px;"/>
					<p class="description"><?php _e( 'The maximum number of times this discount can be used. Leave blank for unlimited.', 'easy-digital-downloads' ); ?></p>
				</td>
			</tr>

			<?php do_action( 'edd_edit_discount_form_before_min_cart_amount', $discount->id, $discount ); ?>

			<tr>
				<th scope="row" valign="top">
					<label for="edd-min-cart-amount"><?php _e( 'Minimum Amount', 'easy-digital-downloads' ); ?></label>
				</th>
				<td>
					<input type="text" id="edd-min-cart-amount" name="min_cart_price" value="<?php echo esc_attr( $discount->min_cart_price ); ?>" style="width: 40px;"/>
					<p class="description"><?php _e( 'The minimum amount that must be purchased before this discount can be used. Leave blank for no minimum.', 'easy-digital-downloads' ); ?></p>
				</td>
			</tr>

			<?php do_action( 'edd_edit_discount_form_before_status', $discount->id, $discount ); ?>

			<tr>
				<th scope="row" valign="top">
					<label for="edd-status"><?php _e( 'Status', 'easy-digital-downloads' ); ?></label>
				</th>
				<td>
					<select name="status" id="edd-status">
						<option value="active" <?php selected( $discount->status, 'active' ); ?>><?php _e( 'Active', 'easy-digital-downloads' ); ?></option>
						<option value="inactive"<?php selected( $discount->status, 'inactive' ); ?>><?php _e( 'Inactive', 'easy-digital-downloads' ); ?></option>
					</select>
					<p class="description"><?php _e( 'The status of this discount code.', 'easy-digital-downloads' ); ?></p>
				</td>
			</tr>

			<?php do_action( 'edd_edit_discount_form_before_use_once', $discount->id, $discount ); ?>

			<tr>
				<th scope="row" valign="top">
					<label for="edd-use-once"><?php _e( 'Use Once Per Customer', 'easy-digital-downloads' ); ?></label>
				</th>
				<td>
					<input type="checkbox" id="edd-use-once" name="once_per_customer" value="1"<?php checked( true, $single_use ); ?>/>
					<span class="description"><?php _e( 'Limit this discount to a single-use per customer?', 'easy-digital-downloads' ); ?></span>
				</td>
			</tr>
			</tbody>
		</table>

		<?php do_action( 'edd_edit_discount_form_bottom', $discount->id, $discount ); ?>

		<p class="submit">
			<input type="hidden" name="edd-action" value="edit_discount" />
			<input type="hidden" name="discount-id" value="<?php echo esc_attr( $discount->id ); ?>" />
			<input type="hidden" name="edd-redirect" value="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-discounts&edd-action=edit_discount&discount=' . $discount_id ) ); ?>" />
			<input type="hidden" name="edd-discount-nonce" value="<?php echo wp_create_nonce( 'edd_discount_nonce' ); ?>" />
			<input type="submit" value="<?php _e( 'Update Discount Code', 'easy-digital-downloads' ); ?>" class="button-primary" />
		</p>
	</form>
</div>
