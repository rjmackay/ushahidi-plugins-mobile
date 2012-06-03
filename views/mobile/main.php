<div class="block">
	<h2 class="submit"><a href="<?php echo url::site()."mobile/reports/submit/" ?>"><?php echo Kohana::lang('ui_main.submit'); ?></a></h2>
</div>
<div class="block">
	<h2 class="other"><?php echo Kohana::lang('mobile.find_a_report'); ?></h2>
	<div class="collapse shown">
		<?php
			echo '<form action="'.url::site().'mobile/reports/search" method="get">';
		?>
			<label for="town"><?php echo Kohana::lang('mobile.your_area'); ?></label>
				<input type="text" name="town" value="">
				<input type="submit" value="<?php echo Kohana::lang('ui_main.search'); ?> &rarr;">
  		</form>
		
	</div>
</div>
<div class="block">
	<h2 class="other"><a href="<?php echo url::site("mobile/categories/") ?>"><?php echo Kohana::lang('mobile.browse_by_category'); ?></a></h2>
</div>
<div class="block">
	<h2 class="expand"><?php echo Kohana::lang('mobile.recent_reports'); ?></h2>
	<div class="collapse">
		<ul>
			<?php
			foreach ($incidents as $incident)
			{
				$incident_date = $incident->incident_date;
				$incident_date = date('H:i M d', strtotime($incident->incident_date));
				echo "<li><strong><a href=\"".url::site("mobile/reports/view/".$incident->id)."\">".$incident->incident_title."</a></strong>";
				echo "&nbsp;&nbsp;<i>$incident_date</i></li>";
			}
			?>
		</ul>
	</div>
</div>
<div class="block">
	<h2 class="expand"><?php echo Kohana::lang('mobile.related_news'); ?></h2>
	<div class="collapse">
		<ul>
			<?php
			foreach ($feeds as $feed)
			{
				$feed_date = date('H:i M d', strtotime($feed->item_date));
				echo "<li><strong><a href=\"".$feed->item_link."\">".$feed->item_title."</a></strong>";
				//echo "&nbsp;&nbsp;<i>$incident_date</i></li>";
				echo "</li>";
			}
		?>
		</ul>
	</div>
</div>