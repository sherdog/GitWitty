<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<fieldset class="adminform">
	<legend><?php echo JText::_( 'FOLLOWUP' ); ?></legend>
	<a href="<?php echo acymailing::completeLink('followup&task=add&campaign='.$this->list->listid) ?>" title="<?php echo JText::_('FOLLOWUP_ADD')?>" ><img src="<?php echo '../images/M_images/new.png'; ?>" alt="<?php echo JText::_('FOLLOWUP_ADD',true)?>"/></a>
	<table class="adminlist" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'NUM' );?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_('DELAY'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('JOOMEXT_SUBJECT'); ?>
				</th>
				<th class="title titlesender">
					<?php echo JText::_('SENDER_INFORMATIONS'); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_('PUBLISHED'); ?>
				</th>
				<th class="title titletoggle" >
					<?php echo JText::_( 'DELETE' ); ?>
				</th>
				<th class="title titleid">
					<?php echo JText::_( 'ID' ); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$k = 0;
				for($i = 0,$a = count($this->followup);$i<$a;$i++){
					$row =& $this->followup[$i];
					$publishedid = 'published_'.$row->mailid;
					$id = 'followup'.$row->mailid;
			?>
				<tr class="<?php echo "row$k"; ?>" id="<?php echo $id; ?>">
					<td align="center" style="text-align:center">
					<?php echo $i+1; ?>
					</td>
					<td align="center" style="text-align:center">
						<?php echo $this->delay->display($row->senddate); ?>
					</td>
					<td>
					<a href="<?php echo acymailing::completeLink('followup&task=edit&mailid='.$row->mailid) ?>" title="<?php echo JText::_('EDIT')?>" >
							<?php echo $row->subject; ?>
					</a>
					</td>
					<td align="center" style="text-align:center">
						<?php
						if(empty($row->fromname)) $row->fromname = $this->config->get('from_name');
						if(empty($row->fromemail)) $row->fromemail = $this->config->get('from_email');
						if(empty($row->replyname)) $row->replyname = $this->config->get('reply_name');
						if(empty($row->replyemail)) $row->replyemail = $this->config->get('reply_email');
						if(!empty($row->fromname)){
							$text = '<b>'.JText::_('FROM_NAME').' : </b>'.$row->fromname;
							$text .= '<br/><b>'.JText::_('FROM_ADDRESS').' : </b>'.$row->fromemail;
							$text .= '<br/><br/><b>'.JText::_('REPLYTO_NAME').' : </b>'.$row->replyname;
							$text .= '<br/><b>'.JText::_('REPLYTO_ADDRESS').' : </b>'.$row->replyemail;
							echo acymailing::tooltip($text, '', '', $row->fromname);
						}
						?>
					</td>
					<td align="center" style="text-align:center">
						<span id="<?php echo $publishedid ?>" class="spanloading"><?php echo $this->toggleClass->toggle($publishedid,(int) $row->published,'mail') ?></span>
					</td>
					<td  align="center" style="text-align:center">
						<?php echo $this->toggleClass->delete($id,$this->list->listid.'_'.$row->mailid,'followup',true); ?>
					</td>
					<td width="1%" align="center" style="text-align:center">
						<?php echo $row->mailid; ?>
					</td>
				</tr>
			<?php
					$k = 1-$k;
				}
			?>
		</tbody>
	</table>
</fieldset>