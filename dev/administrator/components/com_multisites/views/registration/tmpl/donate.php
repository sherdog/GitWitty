<?php defined('_JEXEC') or die('Restricted access'); ?>
<form action="<?php echo $this->action; ?>" method="post" target="_blank">
<?php if (!empty($this->message)) echo '<p>' . $this->message . '</p>'; ?>
<input type="hidden" name="option"        value="com_pay2win" />
<input type="hidden" name="task"          value="donations" />
<input type="hidden" name="item_code"     value="<?php echo $this->option; ?>" />
<input type="hidden" name="client_info"   value="<?php echo $this->clientInfo; ?>" />
<span class="editlinktip"><label id="donation-lbl" for="donation" class="hasTip" title="<?php echo $this->btnToolTipMsg; ?>">
<input type="image" src="<?php echo JURI::base() . 'components/' . $this->option; ?>/images/btn_donate.gif" 
       border="0" name="submit" alt="<?php echo $this->btnAltMsg; ?>" />
</label></span>
</form>

