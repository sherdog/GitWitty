<?php defined('_JEXEC') or die('Restricted access'); ?>
<form action="<?php echo $this->action; ?>" method="post">
<?php if (!empty($this->message)) echo '<p>' . $this->message . '</p>'; ?>
<input type="hidden" name="option"        value="com_pay2win" />
<input type="hidden" name="task"          value="registration" />
<input type="hidden" name="product_id"    value="<?php echo $this->product_id; ?>" />
<input type="hidden" name="url"           value="<?php echo $this->redirect_url; ?>" />
<?php if ( empty( $this->product_id) || $this->product_id=='1-2WIN-V4H-1PZB-T8B-CE8') { ?>
<input type="hidden" name="clientinfo"       value="<?php echo $this->clientinfo; ?>" />
<input type="hidden" name="productname"      value="<?php echo $this->productname; ?>" />
<input type="hidden" name="productversion"   value="<?php echo $this->productversion; ?>" />
<input type="hidden" name="joomlaversion"    value="<?php echo $this->joomlaversion; ?>" />
<?php } ?>

<span class="editlinktip"><label id="registration-lbl" for="registration" class="hasTip" title="<?php echo $this->btnToolTipMsg; ?>">
<input type="image" src="<?php echo JURI::base() . 'components/' . $this->option; ?>/images/btn_registration.gif" 
       border="0" name="submit" alt="<?php echo $this->btnAltMsg; ?>" />
</label></span>
</form>
