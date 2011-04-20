<?php defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.utilities.utility.php');
?>

<script language="javascript" type="text/javascript">
<!--
	function submitbutton(pressbutton) {
			submitform( pressbutton );
	}
//-->
</script>
<?php if ( !empty($this->ads)) { ?>
<table border="0">
   <tr><td><?php echo $this->ads; ?></td></tr>
</table>
<?php } ?>
<table border="0"><tr><td>
<table class="adminform">
   <tr>
      <td class="helpMenu"><label for="product_id"><strong><?php echo JText::_( 'Product ID' ); ?>:</strong></label></td>
      <td><?php echo $this->row->product_id; ?></td>
   </tr>
<?php if ( !JUtility::isWinOS()) { ?>
   <tr>
      <td class="helpMenu"><label for="unix_symlink"><strong><?php echo JText::_( 'SETTINGS_SYMLINK_LBL' ); ?>:</strong></label></td>
      <td>
<?php if ( MultisitesHelper::isSymbolicLinks()) {
         echo JText::_( 'SETTINGS_SYMLINK_OK');
      }
      else {
         echo JText::_( 'SETTINGS_SYMLINK_NOT_OK');
      }
?>
      </td>
   </tr>
<?php } ?>
   <tr>
      <td class="helpMenu"><label for="mysql_vers"><strong><?php echo JText::_( 'SETTINGS_MYSQL_VERS' ); ?>:</strong></label></td>
      <td>
         <?php echo Jms2WinFactory::getDBOVersion(); ?>
<?php
      $db =& JFactory::getDBO();
      if ( MultisitesDatabase::_isViewSupported($db)) {
         echo ' ' . JText::_( 'SETTINGS_MYSQL_VIEW_SUPPORTED');
      }
?>
      </td>
   </tr>
   <tr>
      <td colspan="2">
         <fieldset>
         <legend><?php echo JText::_( 'Billable websites'); ?></legend>
         <table border="0">
            <tr>
               <td class="helpMenu"><label for="website_count"><strong><?php echo JText::_( 'Website count' ); ?>:</strong></label></td>
               <td align="right"><?php echo $this->row->website_count;?></td>
               <td rowspan="2" valign="middle">
               			<form action="<?php echo $this->row->quota_url; ?>" method="post" name="buyquota" id="form-buyquota" style="clear: both;">
                        	<div class="button_holder">
                        	<div class="button1">
                        		<div class="next">
                        			<a onclick="buyquota.submit();">
                        				<?php echo JText::_( 'Buy quota'); ?></a>
                        		</div>
                        	</div>
                        	</div>
                        	<input type="hidden" name="option"     value="com_pay2win" />
                        	<input type="hidden" name="task"       value="jms.buyQuota" />
                        	<input type="hidden" name="product_id" value="<?php echo $this->row->product_id; ?>" />
                       </form>
               </td>
            </tr>
            <tr>
               <td class="helpMenu"><label for="website_quota"><strong><?php echo JText::_( 'Website quota' ); ?>:</strong></label></td>
               <td align="right"><?php echo $this->row->website_quota; ?></td>
            </tr>
         </table>
         </fieldset>
      </td>
   </tr>
</table>
</td></tr></table>

<form action="index.php" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="option"        value="<?php echo $this->option; ?>" />
	<input type="hidden" name="task"          value="manage" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>