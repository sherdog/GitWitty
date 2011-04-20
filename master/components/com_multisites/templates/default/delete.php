<?php defined('_JEXEC') or die('Restricted access');
	$document = & JFactory::getDocument();
	$document->addStyleSheet( 'components/com_multisites/templates/' .basename( dirname( __FILE__)). '/css/delete.css');

?>

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
<div id="jmsdelete">
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
   <form action="index.php" method="post" name="adminForm">
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
   	<input type="hidden" name="option"     value="<?php echo $this->option; ?>" />
   	<input type="hidden" name="task"       value="" />
   	<input type="hidden" name="Itemid"     value="<?php echo $this->Itemid; ?>" />
   	<input type="hidden" name="deleteDB"   value="1" />
   	<?php echo JHTML::_( 'form.token' ); ?>
   </form>
</div>
