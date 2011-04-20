<?php defined('_JEXEC') or die('Restricted access'); ?>

<script language="javascript" type="text/javascript">
<!--
	function submitbutton(pressbutton) {
		if (pressbutton == 'doDeleteSite') {

			submitform( pressbutton );
		} else {
			submitform( pressbutton );
		}
	}
//-->
</script>
<?php if ( !empty($this->ads)) { ?>
<table border="0">
   <tr><td><?php echo $this->ads; ?></td></tr>
</table>
<?php } ?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<table><tr><td>
	<table class="adminform" border="1">
	   <caption><?php echo JText::_( 'Website template information' ); ?></caption>
	   <tbody>
	      <tr>
	         <td class="helpMenu"><label for="id"><strong><?php echo JText::_( 'ID' ); ?>:</strong></label></td>
	         <td><?php echo $this->template->id; ?></td>
	      </tr>
	      <tr>
	         <td class="helpMenu"><label for="fromSiteID"><strong><?php echo JText::_( 'From Site ID' ); ?>:</strong></label></td>
	         <td><?php echo $this->template->fromSiteID; ?></td>
	      </tr>
      <tr>
	         <td class="helpMenu"><label for="fromDB"><strong><?php echo JText::_( 'From DB' ); ?>:</strong></label></td>
	         <td><?php echo $this->template->fromDB; ?></td>
	      </tr>
		      <tr valign="top">
	         <td align="right"><label for="domains"><strong><?php echo JText::_( 'List of domain names' ); ?>:</strong></label></td>
	         <td><?php echo implode( "<br/>", $this->template->toDomains); ?></td>
	      </tr>
	      <tr>
	         <td class="helpMenu"><label for="toSiteName"><strong><?php echo JText::_( 'To Site Name' ); ?>:</strong></label></td>
	         <td><?php echo $this->template->toSiteName; ?></td>
	      </tr>
	      <tr>
	         <td class="helpMenu"><label for="adminUserID"><strong><?php echo JText::_( 'To Admin User ID' ); ?>:</strong></label></td>
	         <td><?php echo $this->template->adminUserID; ?></td>
	      </tr>
	      <tr>
	         <td class="helpMenu"><label for="toPrefix"><strong><?php echo JText::_( 'To DB Prefix' ); ?>:</strong></label></td>
	         <td><?php echo $this->template->toPrefix; ?></td>
	      </tr>
	      <tr>
	         <td class="helpMenu"><label for="deploy_dir"><strong><?php echo JText::_( 'To deploy folder' ); ?>:</strong></label></td>
	         <td><?php echo $this->template->deploy_dir; ?></td>
	      </tr>
	      <tr>
	         <td class="helpMenu"><label for="media_dir"><strong><?php echo JText::_( 'To media folder' ); ?>:</strong></label></td>
	         <td><?php echo $this->template->media_dir; ?></td>
	      </tr>
	      <tr>
	         <td class="helpMenu"><label for="images_dir"><strong><?php echo JText::_( 'To image folder' ); ?>:</strong></label></td>
	         <td><?php echo $this->template->images_dir; ?></td>
	      </tr>
	      <tr>
	         <td class="helpMenu"><label for="templates_dir"><strong><?php echo JText::_( 'To template folder' ); ?>:</strong></label></td>
	         <td><?php echo $this->template->templates_dir; ?></td>
	      </tr>
	   </tbody>
	</table>
	</td></tr/></table>

	<input type="hidden" name="id" value="<?php echo $this->template->id; ?>" />
	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="task" value="" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>