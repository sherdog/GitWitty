<?php defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.utilities.utility.php');
?>

<script language="javascript" type="text/javascript">
<!--
	function submitbutton(pressbutton) {
		var form = document.adminForm;

		if (pressbutton == 'savesite') {
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
			submitform( 'savesite' );
		} else {
			submitform( pressbutton );
		}
	}


   function refreshShareDBStyle( style_display)
   {
      document.getElementById("tr_divdbhost").style.display = style_display;
      document.getElementById("tr_toDBHost").style.display  = style_display;
      document.getElementById("tr_divdbname").style.display = style_display;
      document.getElementById("tr_toDBName").style.display  = style_display;
      document.getElementById("tr_divdbuser").style.display = style_display;
      document.getElementById("tr_toDBUser").style.display  = style_display;
      document.getElementById("tr_divdbpsw").style.display  = style_display;
      document.getElementById("tr_toDBPsw").style.display   = style_display;
      document.getElementById("tr_toPrefix").style.display  = style_display;
   }

   function refreshShowFoldersStyle( style_display)
   {
      document.getElementById("tr_toSiteName").style.display      = style_display;
      document.getElementById("tr_newAdminEmail").style.display   = style_display;
      document.getElementById("tr_newAdminPsw").style.display     = style_display;
      document.getElementById("tr_divmedia_dir").style.display    = style_display;
      document.getElementById("tr_media_dir").style.display       = style_display;
      document.getElementById("tr_divimages_dir").style.display   = style_display;
      document.getElementById("tr_images_dir").style.display      = style_display;

      document.getElementById("tr_gray_message").style.display    = style_display;
   }

   function refreshShowFolders()
   {
      var showFolders   = false;
      var tpl_toPrefix  = document.getElementById("tpl_toPrefix").value;
      var toPrefix      = document.getElementById("toPrefix").value;
      if ( tpl_toPrefix.length>0 || toPrefix.length>0) {
         showFolders = true;
      }
      var style_display = '';
      if ( !showFolders) {
         style_display = 'none';
      }
      refreshShowFoldersStyle( style_display);
      if ( style_display.length<=0) {
         var elt =document.getElementById("toSiteName");
         elt.select();
         elt.focus();
      }
   }

   function refreshShowFTPStyle( style_display)
   {
      document.getElementById("tr_divtoFTP_host").style.display      = style_display;
      document.getElementById("tr_toFTP_host").style.display         = style_display;
      document.getElementById("tr_divtoFTP_port").style.display      = style_display;
      document.getElementById("tr_toFTP_port").style.display         = style_display;
      document.getElementById("tr_divtoFTP_user").style.display      = style_display;
      document.getElementById("tr_toFTP_user").style.display         = style_display;
      document.getElementById("tr_divtoFTP_psw").style.display       = style_display;
      document.getElementById("tr_toFTP_psw").style.display          = style_display;
      document.getElementById("tr_divtoFTP_rootpath").style.display  = style_display;
      document.getElementById("tr_toFTP_rootpath").style.display     = style_display;
   }

   function refreshShowFTPFields()
   {
      var showFields   = false;
      var tpl_toFTP_enable  = document.getElementById("tpl_toFTP_enable").value;
      var toFTP_enable0      = document.getElementById("toFTP_enable0");
      var toFTP_enable1      = document.getElementById("toFTP_enable1");
      if ( toFTP_enable0.checked || toFTP_enable1.checked) {
         showFields = true;
      }
      else if ( tpl_toFTP_enable=='0' || tpl_toFTP_enable=='1') {
         showFields = true;
      }
      var style_display = '';
      if ( !showFields) {
         style_display = 'none';
      }
      refreshShowFTPStyle( style_display);
   }
   
   function refreshTemplateDir( template_id)
   {
      if( template_id == '[unselected]') {
         document.getElementById("tr_shareDB").style.display = 'none';
         refreshShareDBStyle( 'none');
         refreshShowFoldersStyle( 'none');
         return;
      }
      document.getElementById("tr_shareDB").style.display = '';
      refreshShareDBStyle( '');
      
      var ajax = null;
<?php if ( MultisitesHelper::isSymbolicLinks()) { ?>
      document.getElementById("divdeploy_dir").innerHTML    = 'Refreshing ...';
      document.getElementById("divdeploy_create").innerHTML = 'Refreshing ...';
      document.getElementById("divalias_link").innerHTML    = 'Refreshing ...';
<?php } ?>
      document.getElementById("divmedia_dir").innerHTML     = 'Refreshing ...';
      document.getElementById("divimages_dir").innerHTML    = 'Refreshing ...';
      document.getElementById("divtemplates_dir").innerHTML = 'Refreshing ...';

      document.getElementById("divdbhost").innerHTML        = 'Refreshing ...';
      document.getElementById("divtoDBName").innerHTML      = 'Refreshing ...';
      document.getElementById("divtoDBUser").innerHTML      = 'Refreshing ...';
      document.getElementById("divtoDBPsw").innerHTML       = 'Refreshing ...';

      document.getElementById("divtoFTP_enable").innerHTML  = 'Refreshing ...';
      document.getElementById("divtoFTP_host").innerHTML    = 'Refreshing ...';
      document.getElementById("divtoFTP_port").innerHTML    = 'Refreshing ...';
      document.getElementById("divtoFTP_user").innerHTML    = 'Refreshing ...';
      document.getElementById("divtoFTP_psw").innerHTML     = 'Refreshing ...';
      document.getElementById("divtoFTP_rootpath").innerHTML= 'Refreshing ...';


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
                  document.getElementById("tpl_toPrefix").value         = strEntryArray[2];
<?php if ( MultisitesHelper::isSymbolicLinks()) { ?>
                  document.getElementById("divdeploy_dir").innerHTML    = strEntryArray[3];
                  document.getElementById("divdeploy_create").innerHTML = strEntryArray[11];
                  document.getElementById("divalias_link").innerHTML    = strEntryArray[12];
<?php } ?>
                  document.getElementById("divmedia_dir").innerHTML     = strEntryArray[4];
                  document.getElementById("divimages_dir").innerHTML    = strEntryArray[5];
                  document.getElementById("divtemplates_dir").innerHTML = strEntryArray[6];
                  document.getElementById("divdbhost").innerHTML        = strEntryArray[7];
                  document.getElementById("divtoDBName").innerHTML      = strEntryArray[8];
                  document.getElementById("divtoDBUser").innerHTML      = strEntryArray[9];
                  document.getElementById("divtoDBPsw").innerHTML       = strEntryArray[10];

                  var tpl_toFTP_enable = document.getElementById("tpl_toFTP_enable");
                  tpl_toFTP_enable.value                                = strEntryArray[13];
                  if( tpl_toFTP_enable.value == '0') {
                     document.getElementById("divtoFTP_enable").innerHTML  = '<?php echo JText::_( 'No', true ); ?>';
                  }
                  else if( tpl_toFTP_enable.value == '1') {
                     document.getElementById("divtoFTP_enable").innerHTML  = '<?php echo JText::_( 'Yes', true ); ?>';
                  }
                  else {
                     document.getElementById("divtoFTP_enable").innerHTML  = '<?php echo JText::_( 'Default', true ); ?>';
                  }
                  
                  document.getElementById("divtoFTP_host").innerHTML    = strEntryArray[14];
                  document.getElementById("divtoFTP_port").innerHTML    = strEntryArray[15];
                  document.getElementById("divtoFTP_user").innerHTML    = strEntryArray[16];
                  document.getElementById("divtoFTP_psw").innerHTML     = strEntryArray[17];
                  document.getElementById("divtoFTP_rootpath").innerHTML= strEntryArray[18];
               }
               else {
                  document.getElementById("divmedia_dir").innerHTML = "Unexpected server response: " + replyStr;
               }
            }
            else {
               document.getElementById("divmedia_dir").innerHTML = "Error code " + ajax.status;
               document.getElementById("divimages_dir").innerHTML = '';
               document.getElementById("divtemplates_dir").innerHTML = '';
            }
            
            refreshShowFolders();
            refreshShowFTPFields();
         }
      };

      ajax.open( "GET", "index.php?option=com_multisites&task=ajaxGetTemplate&<?php echo JUtility::getToken() . '=1'; ?>&id="+template_id,  true);
      ajax.send(null);
   }

   function clearExpiration()
   {
      document.getElementById("expiration").value = "";
   }
 
   function onSharedDB( checked)
   {
      var db_display = '';
      if ( checked) {
         db_display = 'none';
      }
      document.getElementById("tr_divdbhost").style.display = db_display;
      document.getElementById("tr_toDBHost").style.display  = db_display;
      document.getElementById("tr_divdbname").style.display = db_display;
      document.getElementById("tr_toDBName").style.display  = db_display;
      document.getElementById("tr_divdbuser").style.display = db_display;
      document.getElementById("tr_toDBUser").style.display  = db_display;
      document.getElementById("tr_divdbpsw").style.display  = db_display;
      document.getElementById("tr_toDBPsw").style.display   = db_display;
      document.getElementById("tr_toPrefix").style.display  = db_display;
      
      document.getElementById("tr_newAdminEmail").style.display   = db_display;
      document.getElementById("tr_newAdminPsw").style.display     = db_display;

      document.getElementById("tr_divmedia_dir").style.display    = db_display;
      document.getElementById("tr_media_dir").style.display       = db_display;
      document.getElementById("tr_divimages_dir").style.display   = db_display;
      document.getElementById("tr_images_dir").style.display      = db_display;
      
   }

   function onShowFTPField( radiovalue)
   {
      var ftp_display = 'none';
      if ( radiovalue=='0' || radiovalue=='1') {
         ftp_display = '';
      }

      document.getElementById("tr_toFTP_host").style.display      = ftp_display;
      document.getElementById("tr_toFTP_port").style.display      = ftp_display;
      document.getElementById("tr_toFTP_user").style.display      = ftp_display;
      document.getElementById("tr_toFTP_psw").style.display       = ftp_display;
      document.getElementById("tr_toFTP_rootpath").style.display  = ftp_display;
   }

//-->
</script>
<?php if ( !empty($this->ads)) { ?>
<table border="0">
   <tr><td><?php echo $this->ads; ?></td></tr>
</table>
<?php } ?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<table border="0"><tr><td>
<table class="adminform">
	<tr>
		<td class="helpMenu">
			<label for="id">
				<strong><?php echo JText::_( 'SITE_EDIT_SITE_ID' ); ?> <font color="red">(*)</font>:</strong>
			</label>
		</td>
		<td>
<?php if ($this->isnew) { ?>
			<input class="inputbox" type="text" name="id" id="id" size="30" maxlength="25" value="<?php echo $this->row->id; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'SITE_EDIT_SITE_ID_TTIPS' )); ?>
<?php } else { ?>
      	<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
         <?php echo $this->row->id;?>
<?php } ?>
		</td>
	</tr>
	<tr valign="top">
		<td class="helpMenu">
			<label for="status">
				<strong><?php echo JText::_( 'SITE_EDIT_STATUS' ); ?> <font color="red">(*)</font>:</strong>
			</label>
		</td>
		<td>
			<?php echo MultisitesHelper::getAllStatusList( 'status', $this->row->status); ?>
			<?php echo JHTML::_('tooltip', JText::_( 'SITE_EDIT_STATUS_TTIPS')); ?>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<?php echo JText::_( 'SITE_EDIT_PAYMENT_REFERENCE' ); ?>:
      	<input type="hidden" name="payment_ref" value="<?php echo $this->row->payment_ref; ?>" />
		   <?php echo $this->row->payment_ref; ?>
		</td>
	</tr>
	<tr valign="top">
		<td class="helpMenu">
			<label for="owner_id">
				<strong><?php echo JText::_( 'SITE_EDIT_OWNER' ); ?>:</strong>
			</label>
		</td>
		<td>
			<?php echo MultisitesHelper::getOwnerList( $this->row->owner_id); ?>
			<?php echo JHTML::_('tooltip', JText::_( 'SITE_EDIT_OWNER_TTIPS')); ?>
		</td>
	</tr>
	<tr valign="top">
		<td class="helpMenu">
			<label for="domains">
				<strong><?php echo JText::_( 'SITE_EDIT_DOMAINS' ); ?> <font color="red">(*)</font>:</strong>
			</label>
		</td>
		<td>
		  <table border="0">
   		  <tr valign="top">
      		  <td>
                  <textarea rows="5" cols="50" name="domains"><?php echo implode( "\n", $this->row->domains); ?></textarea>
              </td>
              <td>
         			<?php echo JHTML::_('tooltip', JText::_( 'SITE_EDIT_DOMAINS_TTIPS' )); ?>
         			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
         	  </td>
           </tr>
        </table>
		</td>
	</tr>
	<tr valign="top">
		<td class="helpMenu">
			<label for="expiration">
				<strong><?php echo JText::_( 'SITE_EDIT_EXPIRATION_DATE' ); ?>:</strong>
			</label>
		</td>
		<td>
         <?php JHTML::_( 'behavior.calendar' ); ?>
         <input class="inputbox" type="text" name="expiration" id="expiration" size="15" maxlength="12" readonly="true" value="<?php echo !empty( $this->row->expiration) ? JHTML::_('date', $this->row->expiration, '%Y-%m-%d') : ''; ?>" />
         <a href="#" onclick="return showCalendar( 'expiration', '%Y-%m-%d');"><img class="calendar" src="images/blank.png" alt="calendar" /></a>
         <a href="#" onclick="return clearExpiration();"><img style="width: 16px;height: 16px;margin-left: 3px;background: url(components/com_multisites/images/cancel.png) no-repeat;cursor: pointer;vertical-align: middle;" src="images/blank.png" alt="Clear date" /></a>
			<?php echo JHTML::_('tooltip', JText::_( 'SITE_EDIT_EXPIRATION_DATE_TTIPS')); ?>
			<?php if ( $this->row->isExpired()) { echo '&nbsp;&nbsp;&nbsp;&nbsp;<font color="red"><b>' . JText::_( 'SITE_EDIT_EXPIRED') . '</b></font>'; } ?>
		</td>
	</tr>
	<tr>
		<td valign="top" colspan="2">
		   <span class="note">
   	      <center><font color="red">(*)</font> <?php echo JText::_('SITE_EDIT_FIELDS_MANDATORY'); ?></center>
		   </span>
		</td>
	</tr>
<?php if ( empty( $this->lists['templates'])) { ?>
   <tr>
      <td colspan="2"><div style="clear: both;border: 1px solid #ccc;background: #f0f0f0;color: #0B55C4;font-weight: bold;text-align: center;"><?php echo JText::_( 'SITE_EDIT_CUSTOM_TITLE' ); ?></div></td>
   </tr>
<?php } else { ?>
   <tr>
      <td colspan="2"><div style="clear: both;border: 1px solid #ccc;background: #f0f0f0;color: #0B55C4;font-weight: bold;text-align: center;"><?php echo JText::_( 'SITE_EDIT_REPLICATE_TITLE' ); ?></div></td>
   </tr>
	<tr valign="top">
		<td class="helpMenu">
			<label for="fromTemplateID">
				<strong><?php echo JText::_( 'SITE_EDIT_TEMPLATES' ); ?>:</strong>
			</label>
		</td>
		<td>
			<?php echo $this->lists['templates']; ?>
			<?php echo JHTML::_('tooltip', JText::_( 'SITE_EDIT_TEMPLATES_TTIPS')); ?>
		</td>
	</tr>
	<tr valign="top" id="tr_shareDB" <?php echo $this->style_shareCheckBox; ?>>
		<td class="helpMenu">
			<label for="toDBHost">
				<strong><?php echo JText::_( 'SITE_EDIT_SHAREDB' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="checkbox" name="shareDB" id="shareDB" <?php if ($this->row->shareDB) { echo 'checked="checked"'; } ?> onclick="onSharedDB(this.checked);" />
			<?php echo JHTML::_('tooltip', JText::_( 'SITE_EDIT_SHAREDB_TTIPS')); ?>
		</td>
	</tr>
	<tr valign="top" id="tr_divdbhost" <?php echo $this->style_shareDB; ?>>
		<td class="helpMenu">
			<label for="dbhost">
				<strong><?php echo JText::_( 'SITE_EDIT_TEMPLATE_TO_DBHOST' ); ?>:</strong>
			</label>
		</td>
		<td>
         <font color="green"><i><div id="divdbhost"><?php echo $this->template->toDBHost; ?></div></i></font>
		</td>
	</tr>
	<tr valign="top" id="tr_toDBHost" <?php echo $this->style_shareDB; ?>>
		<td class="helpMenu">
			<label for="toDBHost">
				<strong><?php echo JText::_( 'SITE_EDIT_TO_DBHOST' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="toDBHost" id="toDBHost" size="90" maxlength="255" value="<?php echo $this->row->toDBHost; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'SITE_EDIT_TO_DBHOST_TTIPS' )); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</td>
	</tr>
	<tr valign="top" id="tr_divdbname" <?php echo $this->style_shareDB; ?>>
		<td class="helpMenu">
			<label for="divtoDBName">
				<strong><?php echo JText::_( 'SITE_EDIT_TEMPLATE_TO_DBNAME' ); ?>:</strong>
			</label>
		</td>
		<td>
         <font color="green"><i><div id="divtoDBName"><?php echo $this->template->toDBName; ?></div></i></font>
		</td>
	</tr>
	<tr valign="top" id="tr_toDBName" <?php echo $this->style_shareDB; ?>>
		<td class="helpMenu">
			<label for="toDBName">
				<strong><?php echo JText::_( 'SITE_EDIT_TO_DBNAME' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="toDBName" id="toDBName" size="90" maxlength="255" value="<?php echo $this->row->toDBName; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'SITE_EDIT_TO_DBNAME_TTIPS' )); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</td>
	</tr>
	<tr valign="top" id="tr_divdbuser" <?php echo $this->style_shareDB; ?>>
		<td class="helpMenu">
			<label for="divtoDBUser">
				<strong><?php echo JText::_( 'SITE_EDIT_TEMPLATE_TO_DBUSER' ); ?>:</strong>
			</label>
		</td>
		<td>
         <font color="green"><i><div id="divtoDBUser"><?php echo $this->template->toDBUser; ?></div></i></font>
		</td>
	</tr>
	<tr valign="top" id="tr_toDBUser" <?php echo $this->style_shareDB; ?>>
		<td class="helpMenu">
			<label for="toDBUser">
				<strong><?php echo JText::_( 'SITE_EDIT_TO_DBUSER' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="toDBUser" id="toDBUser" size="90" maxlength="255" value="<?php echo $this->row->toDBUser; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'SITE_EDIT_TO_DBUSER_TTIPS' )); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</td>
	</tr>
	<tr valign="top" id="tr_divdbpsw" <?php echo $this->style_shareDB; ?>>
		<td class="helpMenu">
			<label for="divtoDBPsw">
				<strong><?php echo JText::_( 'SITE_EDIT_TEMPLATE_TO_DBPSW' ); ?>:</strong>
			</label>
		</td>
		<td>
         <font color="green"><i><div id="divtoDBPsw"><?php echo $this->template->toDBPsw; ?></div></i></font>
		</td>
	</tr>
	<tr valign="top" id="tr_toDBPsw" <?php echo $this->style_shareDB; ?>>
		<td class="helpMenu">
			<label for="toDBPsw">
				<strong><?php echo JText::_( 'SITE_EDIT_TO_DBPSW' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="toDBPsw" id="toDBPsw" size="20" maxlength="20" value="<?php echo $this->row->toDBPsw; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'SITE_EDIT_TO_DBPSW_TTIPS' )); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</td>
	</tr>
	<tr valign="top" id="tr_toPrefix" <?php echo $this->style_shareDB; ?>>
		<td class="helpMenu">
			<label for="toPrefix">
				<strong><?php echo JText::_( 'SITE_EDIT_NEW_DB_PREFIX' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input type="hidden" name="tpl_toPrefix" id="tpl_toPrefix" value="<?php echo $this->template->toPrefix; ?>" />
			<input class="inputbox" type="text" name="toPrefix" id="toPrefix" size="20" maxlength="20" value="<?php echo $this->row->toPrefix; ?>" onchange="refreshShowFolders();"/>
			<?php echo JHTML::_('tooltip', JText::_( 'SITE_EDIT_NEW_DB_PREFIX_TTIPS')); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</td>
	</tr>
	<tr valign="top" id="tr_toSiteName" <?php echo $this->style_showDBFields; ?>>
		<td class="helpMenu">
			<label for="toSiteName">
				<font color="gray"><strong><?php echo JText::_( 'SITE_EDIT_NEW_SITE_TITLE' ); ?>:</strong></font>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="toSiteName" id="toSiteName" size="90" maxlength="100" value="<?php echo $this->row->toSiteName; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'SITE_EDIT_NEW_SITE_TITLE_TTIPS')); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</td>
	</tr>
	<tr valign="top" id="tr_newAdminEmail" <?php echo $this->style_showDBFields; ?>>
		<td class="helpMenu">
			<label for="newAdminEmail">
				<font color="gray"><strong><?php echo JText::_( 'SITE_EDIT_NEW_ADMIN_EMAIL' ); ?>:</strong></font>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="newAdminEmail" id="newAdminEmail" size="90" maxlength="90" value="<?php echo $this->row->newAdminEmail; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'SITE_EDIT_NEW_ADMIN_EMAIL_TTIPS')); ?>
		</td>
	</tr>
	<tr valign="top" id="tr_newAdminPsw" <?php echo $this->style_showDBFields; ?>>
		<td class="helpMenu">
			<label for="newAdminPsw">
				<font color="gray"><strong><?php echo JText::_( 'SITE_EDIT_NEW_ADMIN_PASSWORD' ); ?>:</strong></font>
			</label>
		</td>
		<td>
			<input class="inputbox" type="password" name="newAdminPsw" id="newAdminPsw" size="20" maxlength="20" value="<?php echo $this->row->newAdminPsw; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'SITE_EDIT_NEW_ADMIN_PASSWORD_TTIPS')); ?>
		</td>
	</tr>
<?php } ?>	
	<tr valign="top">
		<td class="helpMenu">
			<label for="master_dir">
				<strong><?php echo JText::_( 'SITE_EDIT_MASTER_SITE_DIRECTORY' ); ?>:</strong>
			</label>
		</td>
		<td>
			<i><?php echo JPATH_ROOT; ?></i>
		</td>
	</tr>
<?php	
// On Windows platform, it is not possible to create symbolic links.
// Therefore, it is not possible to propose a slave Deploy directory
if ( MultisitesHelper::isSymbolicLinks()) {
?>   
	<tr valign="top" id="tr_divdeploy_dir">
		<td class="helpMenu">
			<label for="adminUser">
				<strong><?php echo JText::_( 'SITE_EDIT_TEMPLATE_DEPLOY_DIR' ); ?>:</strong>
			</label>
		</td>
		<td>
         <font color="green"><i><div id="divdeploy_dir"><?php echo $this->template->deploy_dir; ?></div></i></font>
		</td>
	</tr>
	<tr valign="top" id="tr_deploy_dir">
		<td class="helpMenu">
			<label for="deploy_dir">
				<strong><?php echo JText::_( 'SITE_EDIT_DEPLOY_DIR' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="deploy_dir" id="deploy_dir" size="90" maxlength="255" value="<?php echo $this->row->deploy_dir; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'SITE_EDIT_DEPLOY_DIR_TTIPS' )); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</td>
	</tr>
	<tr valign="top" id="tr_divdeploy_create">
		<td class="helpMenu">
			<label for="deployCreate">
				<strong><?php echo JText::_( 'SITE_EDIT_TEMPLATE_DEPLOY_CREATE' ); ?>:</strong>
			</label>
		</td>
		<td>
         <font color="green"><i><div id="divdeploy_create"><?php echo $this->template->deploy_create; ?></div></i></font>
		</td>
	</tr>
	<tr valign="top" id="tr_deploy_create">
		<td class="helpMenu">
			<label for="deploy_create">
				<strong><?php echo JText::_( 'SITE_EDIT_DEPLOY_CREATE' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="checkbox" name="deploy_create" id="deploy_create" <?php if (!empty($this->row->deploy_create)) { echo 'checked="checked"'; } ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'SITE_EDIT_DEPLOY_CREATE_TTIPS' )); ?>
		</td>
	</tr>
	<tr valign="top" id="tr_divalias_link">
		<td class="helpMenu">
			<label for="divalias_link">
				<strong><?php echo JText::_( 'SITE_EDIT_TEMPLATE_ALIAS_LINK' ); ?>:</strong>
			</label>
		</td>
		<td>
         <font color="green"><i><div id="divalias_link"><?php echo $this->template->alias_link; ?></div></i></font>
		</td>
	</tr>
	<tr valign="top" id="tr_alias_link">
		<td class="helpMenu">
			<label for="alias_link">
				<strong><?php echo JText::_( 'SITE_EDIT_ALIAS_LINK' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="alias_link" id="alias_link" size="90" maxlength="255" value="<?php echo $this->row->alias_link; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'SITE_EDIT_ALIAS_LINK_TTIPS' )); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</td>
	</tr>
<?php } else { ?>	
   <tr style="display:none;">
      <td>
         <input type="hidden" name="deploy_dir"      value="" />
         <input type="hidden" name="deploy_create"   value="" />
         <input type="hidden" name="alias_link"      value="" />
      </td>
   </tr>
<?php } ?>	
	<tr valign="top" id="tr_divmedia_dir" <?php echo $this->style_showDBFields; ?>>
		<td class="helpMenu">
			<label for="adminUser">
				<font color="gray"><strong><?php echo JText::_( 'SITE_EDIT_TEMPLATE_MEDIA_DIR' ); ?>:</strong></font>
			</label>
		</td>
		<td>
         <font color="green"><i><div id="divmedia_dir"><?php echo $this->template->media_dir; ?></div></i></font>
		</td>
	</tr>
	<tr valign="top" id="tr_media_dir" <?php echo $this->style_showDBFields; ?>>
		<td class="helpMenu">
			<label for="media_dir">
				<font color="gray"><strong><?php echo JText::_( 'SITE_EDIT_MEDIA_DIR' ); ?>:</strong></font>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="media_dir" id="media_dir" size="90" maxlength="255" value="<?php echo $this->row->media_dir; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'SITE_EDIT_MEDIA_DIR_TTIPS')); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</td>
	</tr>
	<tr valign="top" id="tr_divimages_dir" <?php echo $this->style_showDBFields; ?>>
		<td class="helpMenu">
			<label for="adminUser">
				<font color="gray"><strong><?php echo JText::_( 'SITE_EDIT_TEMPLATE_IMAGES_DIR' ); ?>:</strong></font>
			</label>
		</td>
		<td>
         <font color="green"><i><div id="divimages_dir"><?php echo $this->template->images_dir; ?></div></i></font>
		</td>
	</tr>
	<tr valign="top" id="tr_images_dir" <?php echo $this->style_showDBFields; ?>>
		<td class="helpMenu">
			<label for="images_dir">
				<font color="gray"><strong><?php echo JText::_( 'SITE_EDIT_IMAGES_DIR' ); ?>:</strong></font>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="images_dir" id="images_dir" size="90" maxlength="255" value="<?php echo $this->row->images_dir; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'SITE_EDIT_IMAGES_DIR_TTIPS')); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</td>
	</tr>
	<tr valign="top">
		<td class="helpMenu">
			<label for="adminUser">
				<strong><?php echo JText::_( 'SITE_EDIT_TEMPLATE_THEMES_DIR' ); ?>:</strong>
			</label>
		</td>
		<td>
         <font color="green"><i><div id="divtemplates_dir"><?php echo $this->template->templates_dir; ?></div></i></font>
		</td>
	</tr>
	<tr valign="top">
		<td class="helpMenu">
			<label for="templates_dir">
				<strong><?php echo JText::_( 'SITE_EDIT_THEMES_DIR' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="templates_dir" id="templates_dir" size="90" maxlength="255" value="<?php echo $this->row->templates_dir; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'SITE_EDIT_THEMES_DIR_TTIPS')); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</td>
	</tr>
<?php if ( false) { ?>	
	<tr valign="top">
		<td class="helpMenu">
			<label for="tmp_dir">
				<strong><?php echo JText::_( 'SITE_EDIT_TEMP_DIR' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="tmp_dir" id="tmp_dir" size="90" maxlength="255" value="<?php echo $this->row->tmp_dir; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'SITE_EDIT_TEMP_DIR_TTIPS')); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</td>
	</tr>
<?php } ?>	
	<tr id="tr_gray_message" <?php echo $this->style_showDBFields; ?>>
		<td valign="top" colspan="2">
		   <span class="note">
				<center><strong><?php echo JText::_( 'SITE_EDIT_GRAY_FIELDS' ); ?></strong></center>
		   </span>
		</td>
	</tr>
   <tr>
      <td colspan="2"><div style="clear: both;border: 1px solid #ccc;background: #f0f0f0;color: #0B55C4;font-weight: bold;text-align: center;"><?php echo JText::_( 'SITE_EDIT_FTP' ); ?></div></td>
   </tr>
	<tr valign="top">
		<td class="helpMenu">
			<label for="adminUser">
				<strong><?php echo JText::_( 'SITE_EDIT_TEMPLATE_FTP_ENABLE' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input type="hidden" name="tpl_toFTP_enable" id="tpl_toFTP_enable" value="<?php echo $this->template->toFTP_enable; ?>" />
         <font color="green"><i><div id="divtoFTP_enable"><?php 
            if ($this->template->toFTP_enable == '0') { echo JText::_( 'No' ); }
            else if ($this->template->toFTP_enable == '1') { echo JText::_( 'Yes' ); }
            else {
               echo JText::_( 'Default' );
            } ?></div></i></font>
		</td>
	</tr>
	<tr valign="top" id="tr_toFTP_enable">
		<td class="helpMenu">
			<label for="toFTP_enable">
				<strong><?php echo JText::_( 'SITE_EDIT_FTP_ENABLE' ); ?>:</strong>
			</label>
		</td>
		<td>
			<?php echo MultisitesHelper::getRadioYesNoDefault( 'toFTP_enable', $this->row->toFTP_enable, 'refreshShowFTPFields();');
			      echo JHTML::_('tooltip', JText::_( 'SITE_EDIT_FTP_ENABLE_TTIPS' )); ?>
		</td>
	</tr>

	<tr valign="top" id="tr_divtoFTP_host" <?php echo $this->style_showFTPFields; ?>>
		<td class="helpMenu">
			<label for="adminUser">
				<strong><?php echo JText::_( 'SITE_EDIT_TEMPLATE_FTP_HOST' ); ?>:</strong>
			</label>
		</td>
		<td>
         <font color="green"><i><div id="divtoFTP_host"><?php echo $this->template->toFTP_host; ?></div></i></font>
		</td>
	</tr>
	<tr valign="top" id="tr_toFTP_host" <?php echo $this->style_showFTPFields; ?>>
		<td class="helpMenu">
			<label for="toFTP_host">
				<strong><?php echo JText::_( 'SITE_EDIT_FTP_HOST' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="toFTP_host" id="toFTP_host" size="90" maxlength="255" value="<?php echo $this->row->toFTP_host; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'SITE_EDIT_FTP_HOST_TTIPS')); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</td>
	</tr>

	<tr valign="top" id="tr_divtoFTP_port" <?php echo $this->style_showFTPFields; ?>>
		<td class="helpMenu">
			<label for="adminUser">
				<strong><?php echo JText::_( 'SITE_EDIT_TEMPLATE_FTP_PORT' ); ?>:</strong>
			</label>
		</td>
		<td>
         <font color="green"><i><div id="divtoFTP_port"><?php echo $this->template->toFTP_port; ?></div></i></font>
		</td>
	</tr>
	<tr valign="top" id="tr_toFTP_port" <?php echo $this->style_showFTPFields; ?>>
		<td class="helpMenu">
			<label for="toFTP_port">
				<strong><?php echo JText::_( 'SITE_EDIT_FTP_PORT' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="toFTP_port" id="toFTP_port" size="90" maxlength="255" value="<?php echo $this->row->toFTP_port; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'SITE_EDIT_FTP_PORT_TTIPS')); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</td>
	</tr>

	<tr valign="top" id="tr_divtoFTP_user" <?php echo $this->style_showFTPFields; ?>>
		<td class="helpMenu">
			<label for="adminUser">
				<strong><?php echo JText::_( 'SITE_EDIT_TEMPLATE_FTP_USER' ); ?>:</strong>
			</label>
		</td>
		<td>
         <font color="green"><i><div id="divtoFTP_user"><?php echo $this->template->toFTP_user; ?></div></i></font>
		</td>
	</tr>
	<tr valign="top" id="tr_toFTP_user" <?php echo $this->style_showFTPFields; ?>>
		<td class="helpMenu">
			<label for="toFTP_user">
				<strong><?php echo JText::_( 'SITE_EDIT_FTP_USER' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="toFTP_user" id="toFTP_user" size="90" maxlength="255" value="<?php echo $this->row->toFTP_user; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'SITE_EDIT_FTP_USER_TTIPS')); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</td>
	</tr>

	<tr valign="top" id="tr_divtoFTP_psw" <?php echo $this->style_showFTPFields; ?>>
		<td class="helpMenu">
			<label for="adminUser">
				<strong><?php echo JText::_( 'SITE_EDIT_TEMPLATE_FTP_PSW' ); ?>:</strong>
			</label>
		</td>
		<td>
         <font color="green"><i><div id="divtoFTP_psw"><?php echo str_repeat("*", strlen( $this->template->toFTP_psw)); ?></div></i></font>
		</td>
	</tr>
	<tr valign="top" id="tr_toFTP_psw" <?php echo $this->style_showFTPFields; ?>>
		<td class="helpMenu">
			<label for="toFTP_psw">
				<strong><?php echo JText::_( 'SITE_EDIT_FTP_PSW' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="password" name="toFTP_psw" id="toFTP_psw" size="90" maxlength="255" value="<?php echo $this->row->toFTP_psw; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'SITE_EDIT_FTP_PSW_TTIPS')); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</td>
	</tr>

	<tr valign="top" id="tr_divtoFTP_rootpath" <?php echo $this->style_showFTPFields; ?>>
		<td class="helpMenu">
			<label for="adminUser">
				<strong><?php echo JText::_( 'SITE_EDIT_TEMPLATE_FTP_ROOTPATH' ); ?>:</strong>
			</label>
		</td>
		<td>
         <font color="green"><i><div id="divtoFTP_rootpath"><?php echo $this->template->toFTP_rootpath; ?></div></i></font>
		</td>
	</tr>
	<tr valign="top" id="tr_toFTP_rootpath" <?php echo $this->style_showFTPFields; ?>>
		<td class="helpMenu">
			<label for="toFTP_rootpath">
				<strong><?php echo JText::_( 'SITE_EDIT_FTP_ROOTPATH' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="toFTP_rootpath" id="toFTP_rootpath" size="90" maxlength="255" value="<?php echo $this->row->toFTP_rootpath; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'SITE_EDIT_FTP_ROOTPATH_TTIPS')); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</td>
	</tr>


   <tr>
      <td colspan="2"><div style="clear: both;border: 1px solid #ccc;background: #f0f0f0;color: #0B55C4;font-weight: bold;text-align: center;"><?php echo JText::_( 'SITE_EDIT_FROM_CONFIG' ); ?></div></td>
   </tr>
   <tr>
      <td class="helpMenu"><label for="host"><strong><?php echo JText::_( 'SITE_EDIT_DB_HOST_NAME' ); ?>:</strong></label></td>
      <td><?php echo $this->row->host; ?></td>
   </tr>
   <tr>
      <td class="helpMenu"><label for="db"><strong><?php echo JText::_( 'SITE_EDIT_DB' ); ?>:</strong></label></td>
      <td><?php echo $this->row->db; ?></td>
   </tr>
   <tr>
      <td class="helpMenu"><label for="dbprefix"><strong><?php echo JText::_( 'SITE_EDIT_DB_PREFIX' ); ?>:</strong></label></td>
      <td><?php echo $this->row->dbprefix; ?></td>
   </tr>
   <tr>
      <td class="helpMenu"><label for="user"><strong><?php echo JText::_( 'SITE_EDIT_DB_USER' ); ?>:</strong></label></td>
      <td><?php echo $this->row->user; ?></td>
   </tr>
   <tr>
      <td class="helpMenu"><label for="password"><strong><?php echo JText::_( 'SITE_EDIT_DB_PASSWORD' ); ?>:</strong></label></td>
      <td><?php echo $this->row->password; ?></td>
   </tr>
	<?php if ($this->isnew) : ?>
	<tr>
		<td valign="top" colspan="2">
		   <span class="note">
				<center><strong><?php echo JText::_( 'SITE_EDIT_REMARK' ); ?></strong></center>
		   </span>
		</td>
	</tr>
	<?php endif; ?>
</table>
</td></tr></table>

	<input type="hidden" name="option"        value="<?php echo $this->option; ?>" />
	<input type="hidden" name="task"          value="saveSite" />
	<input type="hidden" name="site_prefix"   value="<?php echo !empty( $this->row->site_prefix) ? $this->row->site_prefix  : ''; ?>" />
	<input type="hidden" name="site_alias"    value="<?php echo !empty( $this->row->site_alias)  ? $this->row->site_alias   : ''; ?>" />
	<input type="hidden" name="siteComment"   value="<?php echo !empty( $this->row->siteComment) ? $this->row->siteComment  : ''; ?>" />
	<input type="hidden" name="isnew"         value="<?php if ($this->isnew) { echo '1';} else { echo'0';} ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>