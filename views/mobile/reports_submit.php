<div class="report_submit">
	<h2 class="main_title"><?php echo Kohana::lang('ui_main.reports_submit_new');?></h2>
	<?php if ($site_submit_report_message != ''):
	?>
	<div class="report-message">
		<h3><?php echo $site_submit_report_message;?></h3>
	</div>
	<?php endif;?>

	<div class="report_form">
		<!-- start report form block -->
		<?php print form::open(NULL, array('enctype' => 'multipart/form-data', 'id' => 'reportForm', 'name' => 'reportForm', 'class' => 'gen_forms'));?>
		<input type="hidden" name="latitude" id="latitude" value="<?php echo $form['latitude'];?>">
		<input type="hidden" name="longitude" id="longitude" value="<?php echo $form['longitude'];?>">
		<input type="hidden" name="country_name" id="country_name" value="<?php echo $form['country_name'];?>" />
		<input type="hidden" name="incident_zoom" id="incident_zoom" value="<?php echo $form['incident_zoom'];?>" />
		<?php if ($form_error):
		?>
		<!-- red-box -->
		<div class="red-box">
			<h3>Error!</h3>
			<ul>
				<?php
foreach ($errors as $error_item => $error_description)
{
// print "<li>" . $error_description . "</li>";
print (!$error_description) ? '' : "<li>" . $error_description . "</li>";
}
				?>
			</ul>
		</div>
		<?php endif;?>
		<div class="row">
			<input type="hidden" name="form_id" id="form_id" value="<?php echo $id?>">
		</div>
		<div class="report_row">
			<?php if(count($forms) > 1){
			?>
			<div class="row">
				<h4><span><?php echo Kohana::lang('ui_main.select_form_type');?></span><span class="sel-holder"> <?php print form::dropdown('form_id', $forms, $form['form_id'],
' onchange="formSwitch(this.options[this.selectedIndex].value, \''.$id.'\')"')
					?></span><div id="form_loader" style="float:left;"></div></h4>
			</div>
			<?php }?>
			<h4><?php echo Kohana::lang('ui_main.reports_title');?> <span class="required">*</span></h4>
			<?php print form::input('incident_title', $form['incident_title'], ' class="text long"');?>
		</div>
		<div class="report_row">
			<h4><?php echo Kohana::lang('ui_main.reports_description');?> <span class="required">*</span></h4>
			<?php print form::textarea('incident_description', $form['incident_description'], ' rows="10" class="textarea long" ')
			?>
		</div>
		
		<div class="report_row">
			<h4>Location Name <span class="required">*</span>
			<br />
			<span class="example"><?php echo Kohana::lang('ui_main.detailed_location_example');?></span></h4>
			<?php print form::input('location_name', $form['location_name'], ' class="text long"');?>
		</div>
		<div class="report_row" id="datetime_edit">
			<div class="date-box">
				<h4><?php echo Kohana::lang('ui_main.reports_date');?></h4>
				<?php print form::input('incident_date', $form['incident_date'], ' class="text short"');?>
				<script type="text/javascript">
										$().ready(function() {
					$("#incident_date").datepicker({
					showOn: "both",
					buttonImage: "<?php echo url::file_loc('img');?>media/img/icon-calendar.gif",
						buttonImageOnly: true
						});
						});
				</script>
			</div>
			<div class="time">
				<h4><?php echo Kohana::lang('ui_main.reports_time');?></h4>
				<?php
					for ($i = 1; $i <= 12; $i++)
					{
						$hour_array[sprintf("%02d", $i)] = sprintf("%02d", $i);
						// Add Leading Zero
					}
					for ($j = 0; $j <= 59; $j++)
					{
						$minute_array[sprintf("%02d", $j)] = sprintf("%02d", $j);
						// Add Leading Zero
					}
					$ampm_array = array('pm' => 'pm', 'am' => 'am');
					print form::dropdown('incident_hour', $hour_array, $form['incident_hour']);
					print '<span class="dots">:</span>';
					print form::dropdown('incident_minute', $minute_array, $form['incident_minute']);
					print '<span class="dots">:</span>';
					print form::dropdown('incident_ampm', $ampm_array, $form['incident_ampm']);
				?>
				<?php if ($site_timezone != NULL):
				?>
				<small>(<?php echo $site_timezone;?>)</small>
				<?php endif;?>
			</div>
			<div style="clear:both; display:block;" id="incident_date_time"></div>
		</div>
		<div class="report_row">
			<h4><?php echo Kohana::lang('ui_main.reports_categories');?> <span class="required">*</span></h4>
			<div class="report_category" id="categories">
				<?php
					$selected_categories = (!empty($form['incident_category']) AND is_array($form['incident_category'])) ? $selected_categories = $form['incident_category'] : array();

					$columns = 2;
					echo category::tree($categories, $selected_categories, 'incident_category', $columns);
				?>
			</div>
		</div>
		<?php
			// Action::report_form - Runs right after the report categories
			Event::run('ushahidi_action.report_form');
		?>

		<?php echo $custom_forms
		?>

		<div class="report_optional">
			<h3><?php echo Kohana::lang('ui_main.reports_optional');?></h3>
			<div class="report_row">
				<h4><?php echo Kohana::lang('ui_main.reports_first');?></h4>
				<?php print form::input('person_first', $form['person_first'], ' class="text long"');?>
			</div>
			<div class="report_row">
				<h4><?php echo Kohana::lang('ui_main.reports_last');?></h4>
				<?php print form::input('person_last', $form['person_last'], ' class="text long"');?>
			</div>
			<div class="report_row">
				<h4><?php echo Kohana::lang('ui_main.reports_email');?></h4>
				<?php print form::input('person_email', $form['person_email'], ' class="text long"');?>
			</div>
			<?php
				// Action::report_form_optional - Runs in the optional information of the report form
				Event::run('ushahidi_action.report_form_optional');
			?>
		</div>
		<?php if ( ! $multi_country AND count($cities) > 1):
		?>
		<div class="report_row">
			<h4><?php echo Kohana::lang('ui_main.reports_find_location');?></h4>
			<?php print form::dropdown('select_city', $cities, '', ' class="select" ');?>
		</div>
		<?php endif;?>
		
		<?php Event::run('ushahidi_action.report_form_location', $id);?>
		
		<!-- Removed map -->
		
		<!-- Move location to top -->
		
		<!-- Removed news/video/photo fields-->
		
		<div class="report_row">
			<input name="submit" type="submit" value="<?php echo Kohana::lang('ui_main.reports_btn_submit');?>" class="btn_submit" />
		</div>
		<?php print form::close();?>
	</div>
	<!-- end report form block -->
