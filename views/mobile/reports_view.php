<div class="report_info">
	<div class="verified <?php
    if ($incident->incident_verified == 1)
    {
        echo 'verified_yes">'.Kohana::lang('ui_main.verified').'</div>';
    } else {
        echo '">'.Kohana::lang('ui_main.unverified').'</div>';
    }
    ?>
	<h2><?php echo $incident->incident_title; ?></h2>
	<ul class="details">
		<li>
			<small><?php echo Kohana::lang('ui_main.date'); ?></small>: 
			<?php echo date('M j Y', strtotime($incident->incident_date)); ?>
			<small><?php echo Kohana::lang('ui_main.time'); ?></small>: 
			<?php echo date('H:i', strtotime($incident->incident_date)); ?>
		</li>		
		<li>
			<small><?php echo Kohana::lang('ui_main.description'); ?></small>: <br />
			<?php echo $incident->incident_description; ?>
		</li>
	</ul>
</div>
<div style="clear:both;"></div>
<a href="http://maps.google.com/?t=map&q=<?php echo $incident->location->latitude; ?>,<?php echo $incident->location->longitude; ?>">
    <img id="staticmap" src="http://maps.google.com/maps/api/staticmap?markers=<?php echo $incident->location->latitude; ?>,<?php echo $incident->location->longitude; ?>&zoom=13&maptype=hybrid&sensor=false&size=128x128" />
</a>
