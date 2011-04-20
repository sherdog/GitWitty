<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php

	$document = & JFactory::getDocument();
	$document->addStyleSheet( 'components/com_multisites/templates/' .basename( dirname( __FILE__)). '/css/list.css');
	
   $lists = $this->lists;
   $filters = $this->filters;
?>
<script language="javascript" type="text/javascript">
<!--
	function submitbutton(pressbutton) {
		var form = document.adminForm;

		if (pressbutton == 'saveslave') {
			if ( form.id.value == '' ) {
				alert( '<?php echo JText::_( 'Please enter a site id', true ); ?>' );
				form.menutype.focus();
				return;
			}
			var r = new RegExp("[\']", "i");
			if ( r.exec(form.id.value) ) {
				alert( '<?php echo JText::_( 'The site id cannot contain a \'', true ); ?>' );
				form.menutype.focus();
				return;
			}
			submitform( 'saveslave' );
		} else {
			submitform( pressbutton );
		}
	}

//-->
</script>
<div id="jmslist">
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
<?php
   // Compute a <form action and convert it with SEF routing to force Joomla use the converted SEF link 
   // instead of the native non SEF one that might be mis-understood when SEF is enabled.
   $action = 'index.php?option=com_multisites';
   if ( isset( $this->Itemid) && $this->Itemid != 0 ) {
      $action .= '&Itemid=' . $this->Itemid;
   }
?>   
   <form action="<?php echo JRoute::_( $action); ?>" method="post" name="adminForm" id="adminForm">
   	<div id="toolbar-box">
   	<table border="0">
   		<tr>
   			<td>
   			<td align="right" width="100%">
   				<?php
         		if ( $this->isSuperAdmin) {
      				echo $lists['owner_id'];
         		}
   				echo $lists['status'];
   				?>
   			</td>
   		</tr>
   	</table>
   	</div>
   	<table cellspacing="1" class="multisiteslist">
   	<thead>
   		<tr>
   			<th>
   				<?php echo JText::_( 'Num' ); ?>
   			</th>
   			<th>&nbsp;
   				
   			</th>
   			<th>
   				<?php echo JHTML::_('grid.sort',   JText::_( 'SLAVE_LIST_PREFIX'), 'site_prefix', @$lists['order_Dir'], @$lists['order'] ); ?>
   			</th>
   			<th>
   				<?php echo JHTML::_('grid.sort',   JText::_( 'SLAVE_LIST_ALIAS'), 'site_alias', @$lists['order_Dir'], @$lists['order'] ); ?>
   			</th>
   <?php if ( $this->isSuperAdmin) { ?>
   			<th>
   				<?php echo JHTML::_('grid.sort',   JText::_( 'SLAVE_LIST_OWNER_ID'), 'owner_id', @$lists['order_Dir'], @$lists['order'] ); ?>
   			</th>
   <?php } ?>
   			<th>
   				<?php echo JHTML::_('grid.sort',   JText::_( 'SLAVE_LIST_SITE_TITLE'), 'toSiteName', @$lists['order_Dir'], @$lists['order'] ); ?>
   			</th>
   			<th>
   				<?php echo JHTML::_('grid.sort',   JText::_( 'SLAVE_LIST_FROM_TEMPLATE_ID'), 'fromTemplateID', @$lists['order_Dir'], @$lists['order'] ); ?>
   			</th>
   			<th>
   				<?php echo JHTML::_('grid.sort',   JText::_( 'SLAVE_LIST_DOMAIN'), 'domains', @$lists['order_Dir'], @$lists['order'] ); ?>
   			</th>
   			<th>
   				<?php echo JHTML::_('grid.sort',   JText::_( 'SLAVE_LIST_STATUS'), 'status', @$lists['order_Dir'], @$lists['order'] ); ?>
   			</th>
   			<th>
   				<?php echo JHTML::_('grid.sort',   JText::_( 'SLAVE_LIST_EXPIRATION'), 'expiration', @$lists['order_Dir'], @$lists['order'] ); ?>
   			</th>
   		</tr>
   	</thead>
   	<tfoot>
      	<tr>
      		<td colspan="10">
      			<?php 
      			echo $this->pagination->getResultsCounter();
      			echo $this->pagination->getListFooter();
      			?>
      		</td>
      	</tr>
   <?php if ( !$this->eshop_events) { ?>
      	<tr class="powerby">
      	   <td colspan="10">Powered by <a href="http://www.jms2win.com">JMS Multisite for joomla!</a><br/>
      	   Copyright &copy; 2008-2010 - Edwin2Win sprlu - all right reserved.
      	   </td>
      	</tr>
   <?php } ?>
   	</tfoot>
   	<tbody>
   <?php
      $i = 0; $k = 0;
   	if ( !empty( $this->rows)) {
   	   foreach ($this->rows as $row) {
   			// Get the current iteration and set a few values
            if ( $this->isSuperAdmin) {
   			   $link 	= 'index.php?option=com_multisites&task=editSlave&id='. $row->id;
   			}
   			else {
   			   $link 	= 'index.php?option=com_multisites&task=editSlave&id='. $row->id;
   			}
   ?>
   		<tr class="<?php echo "row". $k; ?>">
   			<td align="center">
   				<?php echo $this->pagination->limitstart + 1 + $i; ?>
   			</td>
   			<td align="center">
   				<input type="radio" id="cb<?php echo $i;?>" name="id" value="<?php echo $row->id; ?>" onclick="isChecked(this.checked);" />
   			</td>
   			<td>
   			 <span class="editlinktip hasTip" title="<?php echo $this->getLinkToolTipsTitle( $row); ?>">
   				<a href="<?php echo $link; ?>">
   					<?php echo $row->site_prefix; ?></a></span>
   			</td>
   			<td>
   				<?php echo $row->site_alias; ?>
   			</td>
   <?php if ( $this->isSuperAdmin) { ?>
   			<td>
   <?php
               $owner_name = MultisitesHelper::getOwnerName( $row->owner_id);
   				echo !empty( $owner_name) ? $owner_name : '&nbsp;';
   ?>
   			</td>
   <?php } ?>
   			<td>
   				<?php echo $row->toSiteName; ?>
   			</td>
   			<td>
   				<?php echo $this->getTemplateTitle( $row->fromTemplateID); ?>
   			</td>
   			<td>
<?php if ( !empty( $row->domains)) {
         echo '<ul><li>';
         echo implode( "</li>\n<li>", $row->domains);
         echo '</li><ul>';
   	}
   	else {
   	   echo '&nbsp;';
   	}
?>
   			</td>
   			<td>
   				<?php echo !empty( $row->status) ? JText::_( $row->status) : '&nbsp;'; ?>
   			</td>
   			<td>
   				<?php echo !empty( $row->expiration) ? JHTML::_('date', $row->expiration, '%d-%b-%Y') : '&nbsp;'; ?>
   			</td>
   		</tr>
   <?php
         $i++; $k = 1 - $k; 
         } 
      } 
   ?>
   	</tbody>
   	</table>
   
   	<input type="hidden" name="task"                value="list" />
   	<input type="hidden" name="boxchecked"          value="0" />
   	<input type="hidden" name="filter_groupName"    value="<?php echo $this->filters['groupName']; ?>" />
   	<input type="hidden" name="eshop_events"        value="<?php echo $this->eshop_events; ?>" />
   	<input type="hidden" name="payment_code"        value="<?php echo $this->payment_code; ?>" />
   	<input type="hidden" name="onDeploy_OK_code"    value="<?php echo $this->onDeploy_OK_code; ?>" />
   	<input type="hidden" name="onDeploy_Err_code"   value="<?php echo $this->onDeploy_Err_code; ?>" />
   	<input type="hidden" name="show_template"       value="<?php echo $this->show_template; ?>" />
   	<input type="hidden" name="show_templatedescr"  value="<?php echo $this->show_templatedescr; ?>" />
   	<input type="hidden" name="show_prefix"         value="<?php echo $this->show_prefix; ?>" />
   	<input type="hidden" name="show_alias"          value="<?php echo $this->show_alias; ?>" />
   	<input type="hidden" name="show_toSiteName"     value="<?php echo $this->show_toSiteName; ?>" />
   	<input type="hidden" name="show_AdminName"      value="<?php echo $this->show_AdminName; ?>" />
   	<input type="hidden" name="show_newAdminEmail"  value="<?php echo $this->show_newAdminEmail; ?>" />
   	<input type="hidden" name="show_newAdminPsw"    value="<?php echo $this->show_newAdminPsw; ?>" />
   	<input type="hidden" name="show_siteComment"    value="<?php echo $this->show_siteComment; ?>" />
   	<input type="hidden" name="filter_order"        value="<?php echo $this->lists['order']; ?>" />
   	<input type="hidden" name="filter_order_Dir"    value="<?php echo $this->lists['order_Dir']; ?>" />
   	<?php echo JHTML::_( 'form.token' ); ?>
   </form>
</div>
<!-- Jms Multisites extends joomla with multisites facilities -->
<!-- &copy; 2010 Edwin2Win sprlu -->
<!-- More information at http://www.jms2win.com -->
