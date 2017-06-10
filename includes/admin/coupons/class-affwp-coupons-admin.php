<?php
/**
 * 'Coupons' Admin Table
 *
 * @package   AffiliateWP\Admin\Coupons
 * @copyright Copyright (c) 2017, AffiliateWP, LLC
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     2.1
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AffWP_Coupons_Admin class.
 *
 * Renders the Coupons table on Affiliate-edit and screens.
 *
 * @since 2.1
 */
class AffWP_Coupons_Admin {

	/**
	 * Coupons table constructor.
	 *
	 * @access public
	 * @since 2.1
	 *
	 * @see WP_List_Table::__construct()
	 *
	 * @param array $args Optional. Arbitrary display and query arguments to pass through
	 *                    the list table. Default empty array.
	 */
	public function __construct( $args = array() ) {
	}

	/**
	 * Interface to render coupons table on affiliate edit and new screens.
	 *
	 * @since  2.1
	 *
	 * @param  integer $affiliate_id Affiliate ID.
	 *
	 * @return void
	 */
	public function coupons_table( $affiliate_id = 0 ) {

		if ( ! $affiliate_id ) {

			if ( ! isset( $affiliate ) ) {
				$affiliate  = affwp_get_affiliate( absint( $_GET['affiliate_id'] ) );
			}

			$affiliate_id = $affiliate->affiliate_id;

			if ( ! $affiliate_id ) {
				affiliate_wp()->utils->log( 'Unable to determine affiliate ID in coupons_table method.' );
				return false;
			}

		}

		$list_items  = array();
		$integrations = affiliate_wp()->integrations->get_enabled_integrations();

		foreach ( $integrations as $integration_id => $integration_term ) {

			if ( affwp_has_coupon_support( $integration_id ) ) {

				$args = array(
					'affiliate_id' => $affiliate_id,
					'integration'  => $integration_id
				);

				$affiliate_coupons = affwp_get_coupons_by_integration( $args );

				if ( $affiliate_coupons ) {

					foreach ( $affiliate_coupons as $coupon_id ) {
						$list_items[] = '<th>(' . $integration_term . ') <a href="' . affwp_get_coupon_edit_url( $coupon_id, $integration_id, true ) . '">' . __( 'Edit coupon', 'affiliate-wp' ) . '</a></th>';
					}

				}

			} elseif ( affwp_has_coupon_support( $args[ 'integration' ] ) ) {
				$list_items[] = '<th>' . $integration_term . ' <a class="affwp-inline-link" href="' . affwp_get_coupon_create_url( $integration_id ) . '">' . __( 'Create coupon', 'affiliate-wp' ) . '</a></th>';
			} else {
				// Otherwise, coupon support should not be available.
				$list_items[] =  '<th>' . __( 'No currently-active AffiliateWP integrations support coupons at this time.', 'affiliate-wp' ) . '</th>';
			}
		}

		/**
		 * Fires at the top of coupons admin table views.
		 *
		 * @since 2.1
		 */
		do_action( 'affwp_affiliate_coupons_table_top' );

		?>

		<hr />

		<p>
			<style type="text/css">
				#affiliatewp-coupons th {
					padding-left: 10px;
				}
			</style>
			<strong>
				<?php _e( 'Coupons for this affiliate:', 'affiliate-wp' ); ?>
			</strong>
		</p>

		<table id="affiliatewp-coupons" class="form-table wp-list-table widefat fixed posts">
			<thead>
				<tr>
					<th><?php _e( 'Integration', 'affiliate-wp' ); ?></th>
					<th><?php _e( 'Coupon Code', 'affiliate-wp' ); ?></th>
					<th><?php _e( 'ID',          'affiliate-wp' ); ?></th>
					<th><?php _e( 'Referrals',   'affiliate-wp' ); ?></th>
					<th><?php _e( 'View',        'affiliate-wp' ); ?></th>
					<th style="width:5%;"></th>
				</tr>
			</thead>
			<tbody>
				<?php

				if ( $coupons ) {

					foreach ($coupons as $coupon ) {
				?>
						<tr>
							<td>
								<?php echo $coupon->integration; ?>
							</td>
							<td>
								<?php echo $coupon->coupon_code; ?>
							</td>
							<td>
								<?php echo $coupon->affwp_coupon_id; ?>
							</td>
							<td>
								<?php echo $coupon->referrals; ?>
							</td>
							<td>
								<?php echo affwp_get_coupon_edit_url( $coupon->coupon_id, $coupon->integration ); ?>
							</td>
						</tr>
<?php
					}
				}
?>

			</tbody>
			<tfoot>
			</tfoot>
		</table>

		<p class="description">
			<?php echo __( 'The current coupons for this affiliate. Click Create Coupon above to create a coupon for any supported integrations without an existing coupon associated with this affiliate.', 'affiliate-wp' ); ?>
		</p>

	<?php

		/**
		 * Fires at the bottom of coupons admin table views.
		 *
		 * @since 2.1
		 */
		do_action( 'affwp_affiliate_coupons_table_bottom' );
	}

	/**
	 * Interface to create coupons on affiliate edit and new screens.
	 *
	 * @since  2.1
	 *
	 * @param  integer $affiliate_id Affiliate ID.
	 *
	 * @return void
	 */
	public function create_coupons( $affiliate_id = 0 ) {

	$all = false; ?>

	<p>
		<strong>
			<?php echo __( 'Create affiliate coupons:', 'affiliate-wp' ); ?>
		</strong>
	</p>
	<select name="create-coupons" id="create-coupons">

		<option value="<?php echo $all; ?>" <?php selected( $all, $all ); ?>><?php _e( 'Create a coupon for all integrations listed', 'affiliate-wp' ); ?></option>
	<?php
	$integrations = affiliate_wp()->integrations->get_enabled_integrations();

	foreach ( $integrations as $integration_id => $integration_term ) {

		if ( affwp_has_coupon_support( $integration_id ) ) { ?>

			<option value="<?php echo $integration_id; ?>" <?php selected( $integration_id, $integration_id ); ?>><?php echo $integration_term; ?></option>

		<?php }

	}
?>
	</select>

	<input type="text" id="code" name="code" size="24" value="" placeholder="<?php _e( 'Coupon code (optional)', 'affiliate-wp' ); ?>" />

	<?php

	$submit_text = __( 'Create Coupon(s)', 'A submit button which will trigger the creation of one or more affiliate coupons. This element is shown on the affiliate edit and new screens, ', 'affiliate-wp' );

	submit_button( $submit_text, 'button', false, false, array( 'ID' => 'search-submit' ) ); ?>

	<p class="description">
		<?php _e( 'AffiliateWP integrations which are active and currently have coupon support will be shown in the dropdown select above. To create a coupon for a specific integration for this affiliate, select the desired integration and click Create Coupon. You can also optionally set the desired coupon code, or create coupons for this affiliate for every integration listed at once, by selecting "Create a coupon for all integrations listed" in the dropdown select above.', 'affiliate-wp' ); ?>
	</p>
<?php
	}

}