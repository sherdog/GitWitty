<p>Select the feature banner that you would like to have shown on your websites homepage</p>
<style>
.bannerContainer { float:left; margin:10px; padding:10px;}
.bannerContainer.highlighted { background:#CCC; }
#chooseBannerAncher { text-align:center; margin:10px 0px; display:block; }
#chooseBannerAncher2 { text-align:center; margin:10px 0px; display:block; }
</style>
<script>
jQuery(document).ready(function(){
	jQuery('.chooseBannerAncher').click(function() {
		jQuery('.highlighted').removeClass('highlighted');
		jQuery(this).parent('div').parent('div').addClass('highlighted');
	});
	jQuery('.chooseBannerAncher2').click(function() {
		jQuery('.highlighted').removeClass('highlighted');
		jQuery(this).parent('div').addClass('highlighted');
	});
});
</script>


<form method="post" id="adminForm" name="adminForm">
<?php
$db =& JFactory::getDBO();

$query  = "SELECT title, filename FROM gitwitty_assets.main_images i LEFT JOIN gitwitty_assets.types  t ON i.type = t.id  WHERE t.name='Health' AND t.pos='feature'";
$db->setQuery($query);
$rows = $db->loadObjectList();
$imgPath = JURI::root().'images'.DS.'features'.DS;
$i=0;
foreach($rows as $row) {
	echo '<div class="bannerContainer" id="banner_'.$i.'">'.
	'<div class="imageContainer"><img width="200" height="100" src="'.$imgPath.$row->filename .'" /></div>' .
	'<a href="javascript:void(0);" id="chooseBannerAncher2" class="chooseBannerAncher2" onclick="document.adminForm.b_'.$i.'.checked=\'true\'; document.adminForm.checked.value=\''.$i.'\'"><img src="http://master.gitwitty.com/images/btnUseThisTemplate.png" border="0" /></a>' .
	'<span style="display:none;"><input type="radio" id="b_'.$i.'" name="banner[]" value="'.$row->filename.'" /></span>'. 
	'</div>';	
$i++;
}

?>
<input type="hidden" name="checked" value="0" />
<input type="hidden" name="task" value="saveBanner" />
</form>