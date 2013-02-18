<?php
echo '
<style type="text/css">
#records-link {
	padding-right:2em;
}
</style>

<h3>Export Database</h3>
<form method="POST" action="'.$SITE_ROOT.'exportdb.php">
<div class="row">
	<label for="lim">Export</label>
	<select name="lim" id="lim">
	<option value="0">All</option>';
		for($i = 1e6; $i >= 1; $i = $i / 10){
			echo '<option value="'.$i.'">'.$i.'</option>';
		}
	echo '</select>
	<label for="lim">entries.</label>
</div>
<div class="row">
	<input type="checkbox" name="new-entries" id="new-entries" checked>
	<label for="new-entries">Export only new entries.</label>
</div>';
if($current_usr_admin >= 6){
	echo '<span id="records-link">
		<input type="submit" name="database" class="lib-button" value="Download Database">
	</span>';
}
if($current_usr_admin >= 5){
	echo '<span id="barcodes-link">
		<input type="submit" name="barcodes" class="lib-button" value="Download Barcodes">
	</span>';
}
?>