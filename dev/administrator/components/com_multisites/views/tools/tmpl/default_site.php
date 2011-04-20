<?php defined('_JEXEC') or die('Restricted access'); ?>
	<li id="tree_<?php echo $this->node_id++; ?>"><a href="#<?php echo $label = $this->site->id; ?>"><?php
	if ( $this->site->id == ':master_db:') { 
?><!-- icon:dbsharing.gif#17; openicon:dbsharing.gif#16 --><?php
   }
   else {
?><!-- icon:dbsharing.gif#15; openicon:dbsharing.gif#15 --><?php
   }
	echo $label = $this->site->id;
?></a>{__children__}</li>
	