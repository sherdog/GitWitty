<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<div id="acy_content">
<div id="acymailing_edit">
<style type="text/css">
div.familymenu a
{
	display:block;
	float:left;
	border: 1px solid #dcdce9;
	text-align: center;
	margin: 2px;
	padding : 2px 6px;
	height:18px;
	font-size:12px;
	text-decoration:none;
}
div.familymenu a:hover
{
	background-color : #eee;
}
.familymenu a.selected{
	background-color : #eee;
	border: 2px solid #dcdce9;
	padding : 1px 5px;
}
#inserttagdiv{
	float:right;
}
#plugarea{
	float:left;
	clear:both;
	padding : 10px;
	width:700px;
}
table.adminlist tr.selectedrow td{
	background-color:#FDE2BA;
}
</style>
<div class="familymenu" >
<?php
	if(empty($this->tagsfamilies)){ ?>
		You should see tags here!<br/>
		You probably had an error during installation<br/>
		Please make sure your plugins folders are writables :<br/>
			 * Joomla / Plugins<br/>
			 * Joomla / Plugins / System<br/>
		And then <a href="index.php?option=com_acymailing&amp;ctrl=update&amp;task=install&amp;tmpl=component">click here</a> to trigger the install process again.
	<?php }
	foreach ($this->tagsfamilies as $id => $oneFamily){
		if(empty($oneFamily)) continue;
		if($oneFamily->function == $this->fctplug){
			$help = empty($oneFamily->help) ? '' : $oneFamily->help;
			$class = ' class="selected" ';
		}
		else $class = '';
		echo '<a'.$class.' href="'.acymailing::completeLink($this->ctrl.'&task=tag&type='.$this->type.'&fctplug='.$oneFamily->function,true).'" >'.$oneFamily->name.'</a>';
	}
?>
</div>
<?php if(!empty($help) AND $this->app->isAdmin()){?>
<div style="float:right;padding:2px 4px;background-color:#CCCCCC;border:1px solid #AAAAAA;">
<?php include_once(ACYMAILING_BUTTON.DS.'pophelp.php');
	$helpButton = new JButtonPophelp();
	echo $helpButton->fetchButton('Pophelp',$help);
	?>
</div>
<div id="iframedoc" style="clear:both"></div>
<?php } ?>
<div id="inserttagdiv">
	<input class="inputbox" style="display:none" id="tagstring" name="tagstring" size="100" value="" onclick="this.select();"> <button style='display:none' id='insertButton' onclick="insertTag();"><?php echo JText::_('INSERT_TAG')?></button>
</div>
<form action="<?php echo JRoute::_('index.php?option=com_acymailing&tmpl=component'); ?>" method="post" name="adminForm" autocomplete="off">
<div id="plugarea">
	<?php echo $this->defaultContent;?>
</div>
<div class="clr"></div>
<input type="hidden" name="option" value="<?php echo ACYMAILING_COMPONENT; ?>" />
<input type="hidden" name="task" value="tag" />
<input type="hidden" id="fctplug" name="fctplug" value="<?php echo $this->fctplug; ?>"/>
<input type="hidden" name="type" value="<?php echo $this->type; ?>" />
<input type="hidden" name="ctrl" value="<?php echo $this->ctrl; ?>" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>
</div>