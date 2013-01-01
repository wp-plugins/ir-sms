<script type="text/javascript">
	jQuery(document).ready(function() {

		jQuery('#doaction').click(function() {
			var action = jQuery('#action').val();
			
			if(action == 'trash') {
				var agree = confirm('<?php _e('Are you sure?', 'wp-sms'); ?>');

				if(agree)
					return true;
				else
					return false;
			}
		})

	});
</script>

<div class="wrap">
	<h2><?php _e('Archives SMS', 'wp-sms'); ?></h2>
	<form action="" method="post">
		<table class="widefat fixed" cellspacing="0">
			<thead>
				<tr>
					<th id="cb" scope="col" class="manage-column column-cb check-column"><input type="checkbox" name="checkAll" value=""/></th>
					<th scope="col" class="manage-column column-name" width="20%"><?php _e('ID', 'wp-sms'); ?></th>
					<th scope="col" class="manage-column column-name" width="20%"><?php _e('Date send', 'wp-sms'); ?></th>
					<th scope="col" class="manage-column column-name" width="20%"><?php _e('SMS Text', 'wp-sms'); ?></th>
					<th scope="col" class="manage-column column-name" width="20%"><?php _e('Send to', 'wp-sms'); ?></th>
				</tr>
			</thead>
		

			<tbody>
				<?php
				global $wpdb, $table_prefix;
				$get_result = $wpdb->get_results("SELECT * FROM {$table_prefix}sms_archives");

				if(count($get_result ) > 0)
				{
					foreach($get_result as $gets)
					{
						$i++;
				?>
				<tr class="<?php echo $i % 2 == 0 ? 'alternate':'author-self'; ?>" valign="middle" id="link-2">
					<th class="check-column" scope="row"><input type="checkbox" name="column_ID[]" value="<?php echo $gets->ID ; ?>" /></th>
					<td class="column-name"><?php echo $i; ?></td>
					<td class="column-name"><?php echo $gets->date; ?></td>
					<td class="column-name"><?php echo $gets->message; ?></td>
					<td class="column-name"><?php echo $gets->to; ?></td>
				</tr>
				<?php
					}
				} else { ?>
					<tr>
						<td colspan="5"><?php _e('Not Found!', 'wp-sms'); ?></td>
					</tr>
				<?php } ?>
			</tbody>

			<tfoot>
				<tr>
					<th id="cb" scope="col" class="manage-column column-cb check-column"><input type="checkbox" name="checkAll" value=""/></th>
					<th scope="col" class="manage-column column-name" width="20%"><?php _e('ID', 'wp-sms'); ?></th>
					<th scope="col" class="manage-column column-name" width="20%"><?php _e('Date send', 'wp-sms'); ?></th>
					<th scope="col" class="manage-column column-name" width="20%"><?php _e('SMS Text', 'wp-sms'); ?></th>
					<th scope="col" class="manage-column column-name" width="20%"><?php _e('Send to', 'wp-sms'); ?></th>
				</tr>
			</tfoot>
		</table>

		<div class="tablenav">
			<div class="alignleft actions">
				<select name="action" id="action">
					<option selected="selected"><?php _e('Bulk Actions', 'wp-sms'); ?></option>
					<option value="trash"><?php _e('Remove', 'wp-sms'); ?></option>
				</select>
				<input value="<?php _e('Apply', 'wp-sms'); ?>" name="doaction" id="doaction" class="button-secondary action" type="submit"/>
			</div>
			<br class="clear">
		</div>
	</form>
</div>