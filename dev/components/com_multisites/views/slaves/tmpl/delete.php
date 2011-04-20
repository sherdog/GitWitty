<?php defined('_JEXEC') or die('Restricted access'); ?>

<script language="javascript" type="text/javascript">
<!--
	function submitbutton(pressbutton) {
		if (pressbutton == 'doDeleteSlave') {

			submitform( pressbutton );
		} else {
			submitform( pressbutton );
		}
	}
//-->
</script>
<div class="border">
	<div class="padding">
		<div id="toolbar-box">
   		<div class="t">
      		<div class="t">
      			<div class="t"></div>
      		</div>
   		</div>
		</div>
	</div>
	<div class="m">
<?php echo $this->toolbarContent;
      if (!empty($this->toolbarTitle)) {
      	echo $this->toolbarTitle;
      }
?>
		<div class="clr"></div>
	</div>
</div>
<?php if ( !empty($this->ads)) { ?>
<table border="0">
   <tr><td><?php echo $this->ads; ?></td></tr>
</table>
<?php } ?>
<?php
   // Compute a <form action and convert it with SEF routing to force Joomla use the converted SEF link 
   // instead of the native non SEF one that might be mis-understood when SEF is enabled.
   $action = 'index.php?option=com_multisites';
   if ( isset( $this->Itemid) && $this->Itemid != 0 ) {
      $action .= '&Itemid=' . $this->Itemid;
   }
?>   
<form action="<?php echo JRoute::_( $action); ?>" method="post" name="adminForm" id="adminForm">
	<div>
		<span class="note">
			<strong><?php 
				$str = JText::sprintf( 'SLAVE_DELETE_MSG', $this->site->sitename, ''); 
				echo $str;
			?></strong>
		</span>
		<div class="clr"></div>
	</div>
	<table><tr><td>
	<table class="adminform" border="1">
	   <caption><?php echo JText::_( 'SLAVE_DELETE_CAPTION' ); ?></caption>
	   <tbody>
	      <tr>
	         <td class="helpMenu"><label for="site_prefix"><strong><?php echo JText::_( 'SLAVE_DELETE_SITE_PREFIX' ); ?>:</strong></label></td>
	         <td><?php echo $this->site->site_prefix; ?></td>
	      </tr>
	      <tr>
	         <td class="helpMenu"><label for="site_alias"><strong><?php echo JText::_( 'SLAVE_DELETE_SITE_ALIAS' ); ?>:</strong></label></td>
	         <td><?php echo $this->site->site_alias; ?></td>
	      </tr>
	      <tr>
	         <td class="helpMenu"><label for="site_alias"><strong><?php echo JText::_( 'SLAVE_DELETE_SITE_TITLE' ); ?>:</strong></label></td>
	         <td><?php echo $this->site->sitename; ?></td>
	      </tr>
	      <tr valign="top">
	         <td align="right"><label for="domains"><strong><?php echo JText::_( 'SLAVE_DELETE_DOMAINS' ); ?>:</strong></label></td>
	         <td><?php echo implode( "<br/>", $this->site->domains); ?></td>
	      </tr>
	      <tr>
	         <td class="helpMenu"><label for="siteComment"><strong><?php echo JText::_( 'SLAVE_DELETE_COMMENT' ); ?>:</strong></label></td>
	         <td><?php echo $this->site->siteComment; ?></td>
	      </tr>
	   </tbody>
	</table>
	</td></tr/></table>


	<input type="hidden" name="id"         value="<?php echo $this->site->id; ?>" />
	<input type="hidden" name="task"       value="" />
	<input type="hidden" name="deleteDB"   value="1" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>