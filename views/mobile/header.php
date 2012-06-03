<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>
<title><?php echo $site_name; ?></title>
<?php
echo plugin::render('stylesheet');
echo html::script("https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js");
echo html::script("https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.13/jquery-ui.min.js");
echo html::stylesheet("https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css");
echo plugin::render('javascript');
?>
<script type="text/javascript">
<!--//--><![CDATA[//><!--
$(function() {
	$("h2.expand").toggler({speed: "fast"});
});
//--><!]]>
</script>
<script type="text/javascript">
<?php echo $js; ?>
</script>
</head>

<body <?php
if ($show_map === TRUE) {
	echo " onload=\"initialize()\"";
}
?>>
	<div id="container">
		<?php if(!empty($breadcrumbs)) { ?>
			<div id="navigation">
				<?php echo '<a href="'. url::site().'mobile">'.Kohana::lang('ui_main.home').'</a>' . $breadcrumbs; ?>
			</div>
		<?php } ?>
		<div id="page">
