<?php defined('_JEXEC') or die('Restricted access'); ?>
<table border="0"><tr><td>
<table class="adminform">
	<tr>
		<td class="helpMenu">
			<label for="id">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_ID' ); ?>:</strong>
			</label>
		</td>
		<td>
<?php if ($this->isnew) { ?>
			<input class="inputbox" type="text" name="id" id="id" size="30" maxlength="25" value="<?php echo $this->row->id; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_ID_TTIPS' )); ?>
<?php } else { ?>
      	<input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
         <?php echo $this->row->id;?>
<?php } ?>
		</td>
	</tr>
	<tr valign="top">
		<td class="helpMenu">
			<label for="groupName">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_GROUP' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="groupName" id="groupName" size="30" maxlength="25" value="<?php echo $this->row->groupName; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_GROUP_TTIPS')); ?>
		</td>
	</tr>
	<tr valign="top">
		<td class="helpMenu">
			<label for="validity">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_VALIDITY' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="validity" id="validity" size="7" maxlength="5" value="<?php echo $this->row->validity; ?>" />
			<?php echo MultisitesHelper::getValidityUnits( 'validity_unit', $this->row->validity_unit); ?>
			<?php echo JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_VALIDITY_TTIPS')); ?>
		</td>
	</tr>
	<tr valign="top">
		<td class="helpMenu">
			<label for="maxsite">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_TO_MAXSITE' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="maxsite" id="maxsite" size="7" maxlength="5" value="<?php echo $this->row->maxsite; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_TO_MAXSITE_TTIPS')); ?>
		</td>
	</tr>
	<tr valign="top">
		<td class="helpMenu">
			<label for="expireurl">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_EXPIRE_URL' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="expireurl" id="expireurl" size="90" maxlength="200" value="<?php echo $this->row->expireurl; ?>" />
			&nbsp;<?php echo JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_EXPIRE_URL_TTIPS')); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</td>
	</tr>
	<tr valign="top" id="show_sku" <?php echo $this->style_showDBFields; ?>>
		<td class="helpMenu">
			<label for="sku">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_SKU' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="sku" id="sku" size="30" maxlength="25" value="<?php echo $this->row->sku; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_SKU_TTIPS')); ?>
		</td>
	</tr>
	<tr valign="top" id="title" <?php echo $this->style_showDBFields; ?>>
		<td class="helpMenu">
			<label for="title">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_TITLE' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="title" id="title" size="90" maxlength="90" value="<?php echo $this->row->title; ?>" />
			&nbsp;<?php echo JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_TITLE_TTIPS')); ?>
		</td>
	</tr>
	<tr valign="top">
		<td class="helpMenu">
			<label for="description">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_DESCRIPTION' ); ?>:</strong>
			</label>
		</td>
		<td>
		  <table border="0">
   		  <tr valign="top">
      		  <td>
                  <textarea rows="3" cols="50" name="description"><?php echo $this->row->description; ?></textarea>
              </td>
              <td>
         			<?php echo JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_DESCRIPTION_TTIPS' )); ?>
         	  </td>
           </tr>
        </table>
		</td>
	</tr>
	<tr valign="top">
		<td class="helpMenu">
			<label for="toDomains">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_DOMAIN' ); ?> <font color="red">(*)</font>:</strong>
			</label>
		</td>
		<td>
		  <table border="0">
   		  <tr valign="top">
      		  <td>
                  <textarea rows="5" cols="50" name="toDomains"><?php echo implode( "\n", $this->row->toDomains); ?></textarea>
              </td>
              <td>
         			<?php echo JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_DOMAIN_TTIPS' )); ?>
         			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
         	  </td>
           </tr>
        </table>
		</td>
	</tr>
<?php if ( !empty( $this->lists['site_ids'])) { ?>
   <tr>
      <td colspan="2"><div style="clear: both;border: 1px solid #ccc;background: #f0f0f0;color: #0B55C4;font-weight: bold;text-align: center;"><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_REPLICATE_TITLE' ); ?></div></td>
   </tr>
	<tr valign="top">
		<td class="helpMenu">
			<label for="fromSiteID">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_FROM_SITE' ); ?> <font color="red">(*)</font>:</strong>
			</label>
		</td>
		<td>
			<?php echo $this->lists['site_ids']; ?>
			<?php echo JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_FROM_SITE_TTIPS')); ?>
			<span id="divMessage"></span>
		</td>
	</tr>
	<tr valign="top" id="toSiteID" <?php echo $this->style_showDBFields; ?>>
		<td class="helpMenu">
			<label for="toSiteID">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_TO_SITE' ); ?> <font color="red">(*)</font>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="toSiteID" id="toSiteID" size="50" maxlength="50" value="<?php echo $this->row->toSiteID; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_TO_SITE_TTIPS')); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</td>
	</tr>
	<tr valign="top" id="admin_user" <?php echo $this->style_showDBFields; ?>>
		<td class="helpMenu">
			<label for="adminUser">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_ADMIN_USER' ); ?> <font color="red">(*)</font>:</strong>
			</label>
		</td>
		<td>
         <div id="divAdminUser"><?php echo MultisitesHelper::getUsersList( $this->row->fromSiteID, $this->row->adminUserID); ?></div>
		</td>
	</tr>
	<tr valign="top" id="tr_shareDB">
		<td class="helpMenu">
			<label for="toDBHost">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_SHAREDB' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="checkbox" name="shareDB" id="shareDB" <?php if ($this->row->shareDB) { echo 'checked="checked"'; } ?> onclick="onSharedDB(this.checked);" />
			<?php echo JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_SHAREDB_TTIPS')); ?>
		</td>
	</tr>
	<tr valign="top" id="db_host" <?php echo $this->style_showDBFields; ?>>
		<td class="helpMenu">
			<label for="toDBHost">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_TO_DBHOST' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="toDBHost" id="toDBHost" size="50" maxlength="50" value="<?php echo $this->row->toDBHost; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_TO_DBHOST_TTIPS')); ?>
		</td>
	</tr>
	<tr valign="top" id="db_name" <?php echo $this->style_showDBFields; ?>>
		<td class="helpMenu">
			<label for="toDBName">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_TO_DBNAME' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="toDBName" id="toDBName" size="50" maxlength="50" value="<?php echo $this->row->toDBName; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_TO_DBNAME_TTIPS')); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</td>
	</tr>
	<tr valign="top" id="db_user" <?php echo $this->style_showDBFields; ?>>
		<td class="helpMenu">
			<label for="toDBUser">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_TO_DBUSER' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="toDBUser" id="toDBUser" size="50" maxlength="50" value="<?php echo $this->row->toDBUser; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_TO_DBUSER_TTIPS')); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</td>
	</tr>
	<tr valign="top" id="db_psw" <?php echo $this->style_showDBFields; ?>>
		<td class="helpMenu">
			<label for="toDBPsw">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_TO_DBPSW' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="toDBPsw" id="toDBPsw" size="20" maxlength="20" value="<?php echo $this->row->toDBPsw; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_TO_DBPSW_TTIPS')); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</td>
	</tr>
	<tr valign="top" id="table_prefix" <?php echo $this->style_showDBFields; ?>>
		<td class="helpMenu">
			<label for="toPrefix">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_TO_PREFIX' ); ?> <font color="red">(*)</font>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="toPrefix" id="toPrefix" size="50" maxlength="50" value="<?php echo $this->row->toPrefix; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_TO_PREFIX_TTIPS')); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</td>
	</tr>
<?php } ?>
   <tr>
      <td colspan="2"><div style="clear: both;border: 1px solid #ccc;background: #f0f0f0;color: #0B55C4;font-weight: bold;text-align: center;"><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_FOLDERS' ); ?></div></td>
   </tr>
	<tr valign="top">
		<td class="helpMenu">
			<label for="master_dir">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_MASTER_DIR' ); ?>:</strong>
			</label>
		</td>
		<td>
			<i><?php echo JPATH_ROOT; ?></i>
		</td>
	</tr>
<?php	if ( $this->canShowDeployDir()) { ?>
	<tr valign="top">
		<td class="helpMenu">
			<label for="deploy_dir">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_DEPLOY_DIR' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="deploy_dir" id="deploy_dir" size="80" maxlength="255" value="<?php echo $this->row->deploy_dir; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_DEPLOY_DIR_TTIPS' )); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</td>
	</tr>
	<tr valign="top">
		<td class="helpMenu">
			<label for="deploy_create">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_DEPLOY_CREATE' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="checkbox" name="deploy_create" id="deploy_create" <?php if ( !empty( $this->row->deploy_create)) { echo 'checked="checked"'; } ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_DEPLOY_CREATE_TTIPS' )); ?>
		</td>
	</tr>
	<tr valign="top" id="alias_folder" <?php echo $this->style_showDBFields; ?>>
		<td class="helpMenu">
			<label for="alias_link">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_ALIAS_LINK' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="alias_link" id="alias_link" size="80" maxlength="255" value="<?php echo $this->row->alias_link; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_ALIAS_LINK_TTIPS' )); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</td>
	</tr>
<?php } ?>
	<tr valign="top" id="media_folder" <?php echo $this->style_showDBFields; ?>>
		<td class="helpMenu">
			<label for="media_dir">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_MEDIA_FOLDER' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="media_dir" id="media_dir" size="90" maxlength="255" value="<?php echo $this->row->media_dir; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_MEDIA_FOLDER_TTIPS')); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</td>
	</tr>
	<tr valign="top" id="image_folder" <?php echo $this->style_showDBFields; ?>>
		<td class="helpMenu">
			<label for="images_dir">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_IMAGE_FOLDER' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="images_dir" id="images_dir" size="90" maxlength="255" value="<?php echo $this->row->images_dir; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_IMAGE_FOLDER_TTIPS')); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</td>
	</tr>
	<tr valign="top">
		<td class="helpMenu">
			<label for="templates_dir">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_TEMPLATES_DIR' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="templates_dir" id="templates_dir" size="90" maxlength="255" value="<?php echo $this->row->templates_dir; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_TEMPLATES_DIR_TTIPS')); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</td>
	</tr>
   <tr>
      <td colspan="2"><div style="clear: both;border: 1px solid #ccc;background: #f0f0f0;color: #0B55C4;font-weight: bold;text-align: center;"><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_FTP' ); ?></div></td>
   </tr>
	<tr valign="top">
		<td class="helpMenu">
			<label for="toFTP_enable">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_FTP_ENABLE' ); ?>:</strong>
			</label>
		</td>
		<td>
			<?php echo MultisitesHelper::getRadioYesNoDefault( 'toFTP_enable', $this->row->toFTP_enable, 'onShowFTPField(this.value);');
			      echo JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_FTP_ENABLE_TTIPS' )); ?>
		</td>
	</tr>
	<tr valign="top" id="tr_toFTP_host" <?php echo $this->style_showFTPFields; ?>>
		<td class="helpMenu">
			<label for="toFTP_host">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_FTP_HOST' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="toFTP_host" id="toFTP_host" size="80" maxlength="255" value="<?php echo $this->row->toFTP_host; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_FTP_HOST_TTIPS' )); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</td>
	</tr>
	<tr valign="top" id="tr_toFTP_port" <?php echo $this->style_showFTPFields; ?>>
		<td class="helpMenu">
			<label for="toFTP_port">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_FTP_PORT' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="toFTP_port" id="toFTP_port" size="10" maxlength="15" value="<?php echo $this->row->toFTP_port; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_FTP_PORT_TTIPS' )); ?>
		</td>
	</tr>
	<tr valign="top" id="tr_toFTP_user" <?php echo $this->style_showFTPFields; ?>>
		<td class="helpMenu">
			<label for="toFTP_user">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_FTP_USER' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="toFTP_user" id="toFTP_user" size="80" maxlength="255" value="<?php echo $this->row->toFTP_user; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_FTP_USER_TTIPS' )); ?>
		</td>
	</tr>
	<tr valign="top" id="tr_toFTP_psw" <?php echo $this->style_showFTPFields; ?>>
		<td class="helpMenu">
			<label for="toFTP_psw">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_FTP_PSW' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="password" name="toFTP_psw" id="toFTP_psw" size="80" maxlength="255" value="<?php echo $this->row->toFTP_psw; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_FTP_PSW_TTIPS' )); ?>
		</td>
	</tr>
	<tr valign="top" id="tr_toFTP_rootpath" <?php echo $this->style_showFTPFields; ?>>
		<td class="helpMenu">
			<label for="toFTP_rootpath">
				<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_CMN_FTP_ROOTPATH' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="toFTP_rootpath" id="toFTP_rootpath" size="80" maxlength="255" value="<?php echo $this->row->toFTP_rootpath; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_FTP_ROOTPATH_TTIPS' )); ?>
			<?php echo MultisitesHelper::tooltipsKeywords(); ?>
		</td>
	</tr>
</table>
</td></tr>
<tr>
   <td>
   <center><font color="red">(*)</font> <?php echo JText::_('TEMPLATE_VIEW_EDT_CMN_FIELD_REQUIRED'); ?></center>
   </td>
</tr></table>
