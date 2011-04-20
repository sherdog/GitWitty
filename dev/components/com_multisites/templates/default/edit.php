<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php

	$document = & JFactory::getDocument();
	$document->addStyleSheet( 'components/com_multisites/templates/' .basename( dirname( __FILE__)). '/css/edit.css');
	

   $readonly = '';
   if ( $this->isReadOnly) {
      $readonly = 'readonly="1"';
   }
   
   // Compute the parameters that must be passed to the <form action to allow get the parameters correctly
   $action = '';
   $sep = '?';
   if ( !empty( $option)) {
      $action .= $sep . 'option=' . $this->option;
      $sep = '&';
   }
   if ( isset( $this->Itemid) && $this->Itemid != 0 ) {
      $action .= $sep . 'Itemid=' . $this->Itemid;
      $sep = '&';
   }
?>
<script language="javascript" type="text/javascript">
<!--
	function submitbutton(pressbutton) {
		var form = document.adminForm;

		if (pressbutton == 'saveSlave') {
			if ( form.site_prefix.value == '' ) {
				alert( '<?php echo JText::_( 'Please enter a prefix', true ); ?>' );
				form.site_prefix.focus();
				return;
			}
			var r = new RegExp("[\']", "i");
			if ( r.exec(form.site_prefix.value) ) {
				alert( '<?php echo JText::_( 'The prefix cannot contain a \'', true ); ?>' );
				form.site_prefix.focus();
				return;
			}
			document.getElementById("divMsg").innerHTML = "<?php echo JText::_('SITE_PLEASE_WAIT'); ?>";
         document.getElementById("tr_divMsg").style.display = '';
			submitform( 'saveSlave' );
		}
		else {
			submitform( pressbutton );
		}
	}

   function refreshTemplateDir( template_id)
   {
      var ajax;
      document.getElementById("divtemplateDescr").innerHTML     = 'Refreshing ...';
      try {  ajax = new ActiveXObject('Msxml2.XMLHTTP');   }
      catch (e)
      {
        try {   ajax = new ActiveXObject('Microsoft.XMLHTTP');    }
        catch (e2)
        {
          try {  ajax = new XMLHttpRequest();     }
          catch (e3) {  ajax = false;   }
        }
      }

      ajax.onreadystatechange  = function()
      {
         if(ajax.readyState  == 4)
         {
            if(ajax.status  == 200) {
               var replyStr = ajax.responseText;
               if ( replyStr.indexOf( '|') > 0) {
                  var strEntryArray = replyStr.split('|');
                  
                  // strEntryArray[0]  //<** reply type
                  // strEntryArray[1]  //<** ID
                  // strEntryArray[2]  //<** title
                  var tplDescr = strEntryArray[3];
                  document.getElementById("divtemplateDescr").innerHTML = tplDescr.replace(/\n/g,"<br/>");
                  // strEntryArray[4]  //<** validity
                  // strEntryArray[5]  //<** validity_unit
               }
               else {
                  document.getElementById("divtemplateDescr").innerHTML = "Unexpected server response: " + replyStr;
               }
            }
            else {
               document.getElementById("divtemplateDescr").innerHTML = "Error code " + ajax.status;
            }
         }
      };

      ajax.open( "GET", "index.php?option=com_multisites&task=ajaxGetTemplateDescr&<?php echo JUtility::getToken() . '=1'; ?>&id="+template_id,  true);
      ajax.send(null);
   }
//-->
</script>
<div id="jmsedit">
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
   <form action="index.php<?php echo $action; ?>" method="post" name="adminForm">
   <table border="0">
   <tr id="tr_divMsg" style="display:none;"><td>
   <dl id="system-message">
   <dt class="message">Message</dt>
   <dd class="message message fade">
   	<ul id="divMsg"></ul>
   </dd>
   </dl>
   </td/><tr>
   <tr><td>
   <table class="paramlist">
   	<tr valign="top">
   		<td class="helpMenu" align="right">
   			<label for="Template">
   				<strong><?php echo JText::_( 'SLAVE_EDIT_TEMPLATE' ); ?>:</strong>
   			</label>
   		</td>
   		<td nowrap>
   <?php 
            // If Edit or Show website
            if ( $this->isReadOnly || !$this->isnew) {
               echo $this->template->title;
   ?>            
   	         <input type="hidden" name="fromTemplateID" value="<?php echo $this->template->id; ?>" />
   <?php
            }
            // If Add new website
            else {
               echo $this->lists['templates'];
               echo JHTML::_('tooltip', JText::_( 'SLAVE_EDIT_TEMPLATE_TTIPS' ));
            }
   ?>
   		</td>
   		<td rowspan="3">
   		   <table border="0">
      		   <tr>
         			<td width="100%" />
         		   <td>
            		   <table border="0">
            		      <tr>
                     		<td class="helpMenu" align="right">
                     			<label for="status">
                     				<font color="gray"><strong><?php echo JText::_( 'SLAVE_EDIT_STATUS' ); ?>:</strong></font>
                     			</label>
                     		</td>
                     		<td>
                     		   <font color="green"><i><?php echo JText::_( $this->row->status); ?></i></font>
                     		</td>
            		      </tr>
   <?php if ( !empty( $this->row->expiration)) { ?>
            		      <tr>
                     		<td class="helpMenu" align="right" nowrap>
                     			<label for="expiration">
                     				<font color="gray"><strong><?php echo JText::_( 'SLAVE_EDIT_EXPIRATION' ); ?>:</strong></font>
                     			</label>
                     		</td>
                     		<td>
                  				<font color="green"><i><?php echo JHTML::_('date', $this->row->expiration, '%d-%b-%Y'); ?></i></font>
                     		</td>
            		      </tr>
   <?php } ?>         		      
            		   </table>
         		   </td>
      		   </tr>
   		   </table>
   		</td>
   	</tr>
   	<tr valign="top">
   		<td class="helpMenu" align="right">
   			<label for="adminUser">
   				<font color="gray"><strong><?php echo JText::_( 'SLAVE_EDIT_TEMPLATE_DESC' ); ?>:</strong></font>
   			</label>
   		</td>
   		<td>
            <font color="green"><i><div id="divtemplateDescr"><?php echo str_replace( "\n", '<br/>', $this->template->description); ?></div></i></font>
   		</td>
   	</tr>
   	<tr>
   		<td class="helpMenu" align="right">
   			<label for="id">
   				<strong><?php echo JText::_( 'SLAVE_EDIT_SITE_PREFIX' ); ?>:</strong>
   			</label>
   		</td>
   		<td nowrap>
   <?php if ($this->isnew) { ?>
   			<input class="inputbox" type="text" name="site_prefix" id="site_prefix" size="10" maxlength="8" value="<?php echo $this->row->site_prefix; ?>" <?php echo $readonly; ?>/>
   			<?php echo JHTML::_('tooltip', JText::_( 'SLAVE_EDIT_SITE_PREFIX_TTIPS' )); ?>
   <?php } else { ?>
         	<input type="hidden" name="site_prefix" value="<?php echo $this->row->site_prefix; ?>" <?php echo $readonly; ?>/>
            <?php echo $this->row->site_prefix;?>
   <?php } ?>
   		</td>
   	</tr>
   <?php if ( !isset( $this->show_alias) || $this->show_alias) { ?>
   	<tr valign="top">
   		<td class="helpMenu" align="right">
   			<label for="domains">
   				<strong><?php echo JText::_( 'SLAVE_EDIT_SITE_ALIAS' ); ?>:</strong>
   			</label>
   		</td>
   		<td nowrap colspan="2">
   			<input class="inputbox" type="text" name="site_alias" id="site_alias" size="50" maxlength="50" value="<?php echo $this->row->site_alias; ?>" <?php echo $readonly; ?>/>
   			<?php echo JHTML::_('tooltip', JText::_( 'SLAVE_EDIT_SITE_ALIAS_TTIPS' )); ?>
   		</td>
   	</tr>
   <?php } ?>
   	<tr valign="top">
   		<td class="helpMenu" align="right">
   			<label for="toSiteName">
   				<strong><?php echo JText::_( 'SLAVE_EDIT_SITE_TITLE' ); ?>:</strong>
   			</label>
   		</td>
   		<td colspan="2">
   			<input class="inputbox" type="text" name="toSiteName" id="toSiteName" size="90" maxlength="100" value="<?php echo $this->row->toSiteName; ?>" <?php echo $readonly; ?>/>
   			<?php echo JHTML::_('tooltip', JText::_( 'SLAVE_EDIT_SITE_TITLE_TTIPS')); ?>
   		</td>
   	</tr>
   <?php if ($this->isnew) { ?>
   <?php if ( !isset( $this->show_AdminName) || $this->show_AdminName) { ?>
   	<tr valign="top">
   		<td class="helpMenu" align="right">
   			<label for="newAdminEmail">
   				<font color="gray"><strong><?php echo JText::_( 'SLAVE_EDIT_ADMIN_USER' ); ?>:</strong></font>
   			</label>
   		</td>
   		<td colspan="2">
   			<?php echo MultisitesHelper::getTemplateAdminName( $this->template); ?>
   		</td>
   	</tr>
   <?php } ?>
   <?php if ( !isset( $this->show_newAdminEmail) || $this->show_newAdminEmail) { ?>
   	<tr valign="top">
   		<td class="helpMenu" align="right">
   			<label for="newAdminEmail">
   				<strong><?php echo JText::_( 'SLAVE_EDIT_ADMIN_EMAIL' ); ?>:</strong>
   			</label>
   		</td>
   		<td colspan="2">
   			<input class="inputbox" type="text" name="newAdminEmail" id="newAdminEmail" size="90" maxlength="90" value="<?php echo $this->row->newAdminEmail; ?>" <?php echo $readonly; ?>/>
   			<?php echo JHTML::_('tooltip', JText::_( 'SLAVE_EDIT_ADMIN_EMAIL_TTIPS')); ?>
   		</td>
   	</tr>
   <?php } ?>
   <?php if ( !isset( $this->show_newAdminPsw) || $this->show_newAdminPsw) { ?>
   	<tr valign="top">
   		<td class="helpMenu" align="right">
   			<label for="newAdminPsw">
   				<strong><?php echo JText::_( 'SLAVE_EDIT_ADMIN_PASSWORD' ); ?>:</strong>
   			</label>
   		</td>
   		<td colspan="2">
   			<input class="inputbox" type="password" name="newAdminPsw" id="newAdminPsw" size="20" maxlength="20" value="<?php echo $this->row->newAdminPsw; ?>" <?php echo $readonly; ?> />
   			<?php echo JHTML::_('tooltip', JText::_( 'SLAVE_EDIT_ADMIN_PASSWORD_TTIPS')); ?>
   		</td>
   	</tr>
   <?php } ?>
   <?php } ?>
   <?php if ( !isset( $this->show_siteComment) || $this->show_siteComment) { ?>
   	<tr valign="top">
   		<td class="helpMenu" align="right">
   			<label for="siteComment">
   				<strong><?php echo JText::_( 'SLAVE_EDIT_ADMIN_COMMENT' ); ?>:</strong>
   			</label>
   		</td>
   		<td colspan="2">
   		  <table>
      		  <tr valign="top">
         		  <td>
                     <textarea rows="3" cols="67" name="siteComment" <?php echo $readonly; ?>><?php echo $this->row->siteComment; ?></textarea>
                 </td>
                 <td>
            			<?php echo JHTML::_('tooltip', JText::_( 'SLAVE_EDIT_ADMIN_COMMENT_TTIPS' )); ?>
            	  </td>
              </tr>
           </table>
   		</td>
   	</tr>
   <?php } ?>
   </table>
   </td></tr></table>
   
   	<input type="hidden" name="task"                value="saveSlave" />
   	<input type="hidden" name="site_id"             value="<?php echo $this->row->id; ?>" />
   	<input type="hidden" name="status"              value="<?php echo $this->row->status; ?>" />
   	<input type="hidden" name="eshop_events"        value="<?php echo $this->eshop_events; ?>" />
   	<input type="hidden" name="payment_code"        value="<?php echo $this->payment_code; ?>" />
   	<input type="hidden" name="onDeploy_OK_code"    value="<?php echo $this->onDeploy_OK_code; ?>" />
   	<input type="hidden" name="onDeploy_Err_code"   value="<?php echo $this->onDeploy_Err_code; ?>" />
   	<input type="hidden" name="isnew"               value="<?php if ($this->isnew) { echo '1';} else { echo'0';} ?>" />
   	<?php echo JHTML::_( 'form.token' ); ?>
   </form>
</div>
