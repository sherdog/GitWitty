<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<div class="componentheading"><?php echo JText::_('MAILING_LISTS'); ?></div>
<?php
	$k = 0;
	$my = JFactory::getUser();
	foreach($this->rows as $i => $oneList){
		$row =& $this->rows[$i];
		$frontEndAccess = true;
		$frontEndManagement = false;
	    if(acymailing::level(3)){
	    	if((int)$my->id == (int)$row->userid)  $frontEndManagement = true;
	    	if(!empty($my->id)){
	    		if($row->access_manage == 'all' OR acymailing::isAllowed($row->access_manage)){
	    			 $frontEndManagement = true;
	    		}
	    	}
	    	if($row->access_sub != 'all' AND ($row->access_sub == 'none' OR empty($my->id) OR !acymailing::isAllowed($row->access_sub))){
	    		$frontEndAccess = false;
	    	}
	    }
		if(!$frontEndManagement AND (!$frontEndAccess OR !$row->published OR !$row->visible)) continue;
?>
	<div class="<?php echo "acymailing_list acymailing_row$k"; ?>">
			<div class="list_name"><a href="<?php echo acymailing::completeLink('archive&listid='.$row->listid.'-'.$row->alias.$this->item)?>"><?php echo $row->name; ?></a></div>
			<div class="list_description"><?php echo $row->description; ?></div>
	</div>
<?php
		$k = 1-$k;
	}
?>
