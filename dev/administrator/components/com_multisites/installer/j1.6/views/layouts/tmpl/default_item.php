<?php // no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );?>
<tr class="<?php echo "row".$this->item->index % 2; ?>" <?php echo $this->item->style; ?>>
	<td><?php echo $this->pagination->getRowOffset( $this->item->index ); ?></td>
	<td>
		<input type="checkbox" id="cb<?php echo $this->item->index;?>" name="eid[<?php echo $this->item->id; ?>]" value="<?php echo $this->item->client_id; ?>" onclick="isChecked(this.checked);" <?php echo $this->item->cbd; ?> />
<?php $row = $this->item;
	   $img_path = ($this->item->client_id == 1 ? JURI::root().'administrator' : JURI::root() ).'components/com_multisites/templates/'.$row->directory.'/template_thumbnail.png';
?>
		<span class="editlinktip hasTip" title="<?php echo $row->name;?>::
<img border=&quot;1&quot; src=&quot;<?php echo $img_path; ?>&quot; name=&quot;imagelib&quot; alt=&quot;<?php echo JText::_( 'No preview available' ); ?>&quot; width=&quot;206&quot; height=&quot;145&quot; />">
   <?php echo $this->item->name; ?></span>
	</td>
	<td align="center">
		<?php echo $this->item->client_id == "0" ? JText::_( 'Site' ) : JText::_( 'Admin' ); ?>
	</td>
	<td align="center" <?php if(@$this->item->legacy) echo 'class="legacy-mode"'; ?>><?php echo @$this->item->version != '' ? $this->item->version : '&nbsp;'; ?></td>
	<td><?php echo @$this->item->creationdate != '' ? $this->item->creationdate : '&nbsp;'; ?></td>
	<td>
		<span class="editlinktip hasTip" title="<?php echo JText::_( 'Author Information' );?>::<?php echo $this->item->author_information; ?>">
			<?php echo @$this->item->author != '' ? $this->item->author : '&nbsp;'; ?>
		</span>
	</td>
	<td align="center">
		<span class="editlinktip hasTip" title="<?php echo JText::_('Compatible Extension');?>">
			<img src="components/com_multisites/images/tick.png"/>
		</span>
	</td>
</tr>
