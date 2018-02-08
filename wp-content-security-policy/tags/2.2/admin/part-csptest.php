<table>
	<tr class='wpcsp_test_row'>
		<td class="">
			<?php _e('External Test Site','wpcsp') ?>
			<a href='https://securityheaders.io/?q=<?php echo urlencode( site_url() ) ;?>' target="_blank">https://securityheaders.io/ for <?php echo esc_html( site_url() ) ; ?></a>
		</td>
	</tr>
	
	<tr class='wpcsp_test_row '>
		<td class="">
			<input type="button" class='btnWPCSP btnWPCSPTestURLChecker' data-target='#btnWPCSPTestURLCheckerOutput' value='<?php _e('Internal Test URL Checker','wpcsp') ?>' />
			<div id='btnWPCSPTestURLCheckerOutput'></div></td>
	</tr>
</table>