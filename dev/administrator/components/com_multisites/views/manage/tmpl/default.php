<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
   $lists = $this->lists;
?>
<form action="index.php?option=com_multisites" method="post" name="adminForm" id="adminForm">
	<table>
<?php if ( !empty($this->ads)) { ?>
		<tr><td colspan="2"><?php echo $this->ads; ?></td></tr>
<?php } ?>
		<tr>
   		<td align="left" width="100%">
   			<?php echo JText::_( 'Filter' ); ?>:
   			<input type="text" name="search" id="search" value="<?php echo $this->lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
   			<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
   			<button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
   		</td>
			<td nowrap="nowrap">
				<?php
				echo $lists['dbserver'];
				echo $lists['dbname'];
				echo $lists['status'];
				echo $lists['owner_id'];
				?>
			</td>
		</tr>
	</table>

	<table class="adminlist" cellspacing="1">
	<thead>
		<tr>
			<th width="5">
				<?php echo JText::_( 'Num' ); ?>
			</th>
			<th width="5">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->sites ); ?>);" />
			</th>
			<th width="1%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort',   JText::_( 'SITE_LIST_ID'), 'id', @$lists['order_Dir'], @$lists['order'] ); ?>
			</th>
			<th width="35%">
				<?php echo JHTML::_('grid.sort',   JText::_( 'SITE_LIST_SITENAME'), 'sitename', @$lists['order_Dir'], @$lists['order'] ); ?>
			</th>
			<th width="25%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort',   JText::_( 'SITE_LIST_DOMAINS'), 'domains', @$lists['order_Dir'], @$lists['order'] ); ?>
			</th>
			<th width="10%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort',   JText::_( 'SITE_LIST_DBSERVER'), 'host', @$lists['order_Dir'], @$lists['order'] ); ?>
			</th>
			<th width="10%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort',   JText::_( 'SITE_LIST_DB'), 'db', @$lists['order_Dir'], @$lists['order'] ); ?>
			</th>
			<th width="1%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort',   JText::_( 'SITE_LIST_PREFIX'), 'prefix', @$lists['order_Dir'], @$lists['order'] ); ?>
			</th>
			<th width="8%">
				<?php echo JHTML::_('grid.sort',   JText::_( 'SITE_LIST_USER'), 'username', @$lists['order_Dir'], @$lists['order'] ); ?>
			</th>
			<?php if ( isset($this->showPassword) && $this->showPassword) { ?>
			<th width="7%">
				<?php echo JHTML::_('grid.sort',   JText::_( 'SITE_LIST_PASSWORD'), 'password', @$lists['order_Dir'], @$lists['order'] ); ?>
			</th>
			<?php } ?>
			<th width="5%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort',   JText::_( 'SITE_LIST_STATUS'), 'status', @$lists['order_Dir'], @$lists['order'] ); ?>
			</th>
			<th width="5%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort',   JText::_( 'SITE_LIST_EXPIRATION'), 'expiration', @$lists['order_Dir'], @$lists['order'] ); ?>
			</th>
			<th width="5%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort',   JText::_( 'SITE_LIST_OWNER'), 'owner_id', @$lists['order_Dir'], @$lists['order'] ); ?>
			</th>
		</tr>
	</thead>
	<tfoot>
	<tr>
		<?php if ( isset($this->showPassword) && $this->showPassword) { ?>
		<td colspan="13">
		<?php } else { ?>
		<td colspan="12">
		<?php } ?>
			<?php echo $this->pagination->getListFooter(); ?>
		</td>
	</tr>
	</tfoot>
	<tbody>
	<?php $i = 0; $k = 0; ?>
	<?php foreach ($this->sites as $site) : ?>
		<?php
			// Get the current iteration and set a few values
			$link 	= 'index.php?option=com_multisites&task=editSite&id='. $site->id;
		?>
		<tr class="<?php echo "row". $k; ?>">
			<td align="center" width="30">
				<?php echo $this->pagination->limitstart + 1 + $i; ?>
			</td>
			<td width="30" align="center">
				<input type="radio" id="cb<?php echo $i;?>" name="id" value="<?php echo $site->id; ?>" onclick="isChecked(this.checked);" />
			</td>
			<td>
				<table border="0" cellspacing="0", cellpadding="0"><tr><td><?php echo $site->id; ?></td>
<?php if ( $site->isNewExtensions()) { ?>
            <td width="100%" />
				<td><img src="components/com_multisites/images/update.png" title="<?php echo JText::_( 'SITE_LIST_REFRESH_SLAVE'); ?>"/></td>
<?php } ?>
            </tr></table>
			</td>
			<td>
			<span class="editlinktip hasTip" title="<?php echo $this->getSiteToolTips( $site); ?>">
				<a href="<?php echo $link; ?>">
					<?php echo $site->sitename; ?></a></span>
			</td>
			<td nowrap="nowrap">
<?php
            if ( empty( $site->domains)) {
               echo '&nbsp;';
            }
            else {
               $sep ='';
               $idom = 0;
               foreach( $site->domains as $domain) {
                  $urldomain = $domain;
                  if ( !empty( $site->indexDomains) && !empty( $site->indexDomains[$idom])) {
                     $urldomain = $site->indexDomains[$idom];
                  }
			         echo '<span class="editlinktip hasTip" title="Go to site::' .$urldomain. '">';
                  echo $sep . '<a href="' .$urldomain. '">' .$domain. '</a></span>';
                  $sep = '<br/>';
                  $idom++;
               }
            }
?>
			</td>
			<td>
				<?php echo !empty( $site->host) ? $site->host : '&nbsp;'; ?>
			</td>
			<td>
				<?php echo !empty( $site->db) ? $site->db : '&nbsp;'; ?>
			</td>
			<td>
				<?php echo !empty( $site->dbprefix) ? $site->dbprefix : '&nbsp;'; ?>
			</td>
			<td>
				<?php echo !empty( $site->user) ? $site->user : '&nbsp;'; ?>
			</td>
         <?php if ( isset($this->showPassword) && $this->showPassword) { ?>
			<td>
				<?php echo !empty( $site->password) ? $site->password : '&nbsp;'; ?>
			</td>
			<?php } ?>
			<td>
				<?php echo !empty( $site->status) ? $site->status : '&nbsp;'; ?>
			</td>
			<td>
				<?php echo !empty( $site->expiration) ? JHTML::_('date', $site->expiration, '%d-%b-%Y') : '&nbsp;'; ?>
			</td>
			<td>
<?php
            $owner_name = MultisitesHelper::getOwnerName( $site->owner_id);
				echo !empty( $owner_name) ? $owner_name : '&nbsp;';
?>
			</td>
		</tr>
		<?php $i++; $k = 1 - $k; ?>
	<?php endforeach; ?>
	</tbody>
	</table>

	<input type="hidden" name="option"           value="com_multisites" />
	<input type="hidden" name="task"             value="manage" />
	<input type="hidden" name="boxchecked"       value="0" />
	<input type="hidden" name="filter_order"     value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
