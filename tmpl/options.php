<?php
/**
 * Template file for options page.
 *
 * @package Goodreads Widget
 */

?>

<div class="wrap">
	<form method="post" action="options.php">
		<?php
		settings_fields( 'mb_goodreads' );
		do_settings_sections( 'mb_goodreads_do_options' );
		submit_button( esc_attr__( 'Save Changes', 'mb_goodreads' ) );
		?>
	</form>
</div>
