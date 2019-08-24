<div class="wrap">
	<h1>Seeds Settings</h1>

	<?php if ($noticeMessage) { ?>
		<div class="notice-success notice">
			<p><?php echo htmlspecialchars($noticeMessage); ?></p>
		</div>
	<?php } ?>

	<form method="post" action="<?php echo $actionUrl; ?>" method="post">
		<input type="hidden" name="seeds_save_options" value="1"/>
		<table class="form-table">
			<tr>
				<th scope="row">Create And Burn Pages</th>
				<td>
					<select name="seeds_show_minting">
						<?php display_select_options($mintingOptions,$seeds_show_minting); ?>
					</select>
					<p class="description">
						If this option is enabled, it will be possible to create new seeds
						and burn existing ones.
					</p>
				</td>
			</tr>
		</table>
		<p class="sumbit">
			<input type="submit" class="button button-primary" value="Save Settings">
		</p>
	</form>
</div>
