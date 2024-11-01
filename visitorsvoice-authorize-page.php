<div class="wrap">
	<div id="icon-visitorsvoice-page" ><br></div>
	<h2 class="visitorsvoice-header"><?php _e("Visitors Voice Plugin", "visitorsvoice");?></h2>
	</br>
	<?php _e('Visitors Voice will show your visitors search behavior for each url and give suggestions how you can improve your content and metadata.', 'visitorsvoice');?>
	</br></br>
	<?php _e('If you don\'t have a Visitors Voice account, use our <a href="http://en.visitorsvoice.com/sign-up-for-free-trial/" target="_new"> free trial</a> or apply for a <a href="http://en.visitorsvoice.com/apply-for-a-developer-account/" target="_new"> developer account</a>.', 'visitorsvoice');?>
	<br/><br/>
	<?php _e('Read more about <a href="http://en.visitorsvoice.com/site-search-analytics-for-wordpress/" target="_new">Visitors Voice for WordPress</a>.', 'visitorsvoice');?>
	<br/><br/>
	<form name="visitorsvoice_settings" method="post" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>">
		<?php wp_nonce_field('visitorsvoice-nonce'); ?>
		<input type="hidden" name="action" value="visitorsvoice_set_api_key">
		<table class="widefat" style="width: 650px;">
			<thead>
				<tr>
					<th class="row-title"><?php _e("Configure the plugin - step 1(2)", "visitorsvoice");?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<?php _e("Please enter your Visitors Voice API key in the field below and click 'Authorize' to get started.","visitorsvoice");?>
						<?php _e("You will find your API Key at the top of the <b>My Account</b>-settings screen in your <a href='http://service.visitorsvoice.com/' target='_new'>Visitors Voice Dashboard</a>.<br/><br/>", "visitorsvoice");?>

						<ul>
							<li>
								<label><?php _e("API Key", "visitorsvoice");?>:</label>
								<input type="text" name="api_key" class="regular-text" />
								<input type="submit" name="Submit" value="<?php _e("Authorize", "visitorsvoice"); ?>" class="button-primary" /> 
								</br></br><?php _e("Want to use test account? API-key: 12345", "visitorsvoice");?>
							</li>
						</ul>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
	
</div>