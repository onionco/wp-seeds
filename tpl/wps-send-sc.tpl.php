<?php if ($message) { ?>
	<i><?php echo $message; ?></i>
<?php } ?>

<?php if ($showForm) { ?>
	<form class="seeds-send-form" method="post" action="<?php echo $actionUrl; ?>">
		<input type="hidden" name="seedsDoSend" value="1"/>

		<p class="seeds-send-form-field-container">
			<label>To Account</label>
			<div class="seeds-send-form-field">
				<select name="seedsSendToAccount">
					<?php display_select_options($users); ?>
				</select>
			</div>
		</p>

		<p class="seeds-send-form-field-container">
			<label>Amount</label>
			<div class="seeds-send-form-field">
				<input type="text" name="seedsSendAmount"/>
			</div>
		</p>

		<p class="seeds-send-form-submit-container">
			<input type="submit" value="Send Seeds"/>
		</p>
	</form>
<?php } ?>