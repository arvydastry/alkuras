<?php
/**
 * Paieškos forma.
 *
 * @package Alkuras
 */

defined( 'ABSPATH' ) || exit;
?>
<form role="search" method="get" class="field" action="<?php echo esc_url( home_url( '/' ) ); ?>" style="max-width:34rem">
	<input type="search" name="s" placeholder=" " value="<?php echo esc_attr( get_search_query() ); ?>">
	<label><?php esc_html_e( 'Ieškoti', 'alkuras' ); ?></label>
</form>
