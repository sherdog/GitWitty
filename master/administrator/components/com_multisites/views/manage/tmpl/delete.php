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
	<div>
		<span class="note">
			<strong><?php 
				$str = JText::sprintf( 'SITE_DELETE', $this->site->sitename, $this->site_dir); 
				echo $str;
			?></strong>
		</span>
		<div class="clr"></div>
	</div>
	<table><tr><td>
	<table class="adminform" border="1">
	   <caption><?php echo JText::_( 'SITE_DELETE_SITE_INFORMATION' ); ?></caption>
	   <tbody>
	      <tr>
	         <td class="helpMenu"><label for="id"><strong><?php echo JText::_( 'SITE_DELETE_SITE_ID' ); ?>:</strong></label></td>
	         <td><?php echo $this->site->id; ?></td>
	      </tr>
	      <tr valign="top">
	         <td align="right"><label for="domains"><strong><?php echo JText::_( 'SITE_DELETE_DOMAINS' ); ?>:</strong></label></td>
	         <td><?php echo implode( "<br/>", $this->site->domains); ?></td>
	      </tr>
<?php    if ( !empty( $this->site->host) && !empty( $this->site->db) && !empty( $this->site->dbprefix) && !empty( $this->site->user)) { ?>
	      <tr>
	         <td class="helpMenu"><label for="host"><strong><?php echo JText::_( 'SITE_DELETE_DB_CONTENT' ); ?>:</strong></label></td>
	         <td><?php echo JHTML::_('select.booleanlist', 'deleteDB', '', 'no'); ?>&nbsp;&nbsp;&nbsp;&nbsp;<font color="gray"><?php echo JText::sprintf( 'SITE_DELETE_DB_CONTENT_TTIPS', $this->site->dbprefix); ?></td>
	      </tr>
<?php    } ?>	      
	      <tr>
	         <td class="helpMenu"><label for="host"><strong><?php echo JText::_( 'SITE_DELETE_DB_HOST_NAME' ); ?>:</strong></label></td>
	         <td><?php echo $this->site->host; ?></td>
	      </tr>
	      <tr>
	         <td class="helpMenu"><label for="db"><strong><?php echo JText::_( 'SITE_DELETE_DB' ); ?>:</strong></label></td>
	         <td><?php echo $this->site->db; ?></td>
	      </tr>
	      <tr>
	         <td class="helpMenu"><label for="dbprefix"><strong><?php echo JText::_( 'SITE_DELETE_DB_PREFIX' ); ?>:</strong></label></td>
	         <td><?php echo $this->site->dbprefix; ?></td>
	      </tr>
	      <tr>
	         <td class="helpMenu"><label for="user"><strong><?php echo JText::_( 'SITE_DELETE_DB_USER' ); ?>:</strong></label></td>
	         <td><?php echo $this->site->user; ?></td>
	      </tr>
	      <tr>
	         <td class="helpMenu"><label for="password"><strong><?php echo JText::_( 'SITE_DELETE_DB_PASSWORD' ); ?>:</strong></label></td>
	         <td><?php echo $this->site->password; ?></td>
	      </tr>
	   </tbody>
	</table>
	</td></tr/></table>

	<input type="hidden" name="id" value="<?php echo $this->site->id; ?>" />
	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="task" value="" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>