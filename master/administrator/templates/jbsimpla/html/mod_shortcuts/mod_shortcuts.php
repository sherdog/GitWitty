<?php 

$iconsize = $this->params->get('icon_size', '48');
$shortcutheight = $this->params->get('shortcut_height' ,'');
$shortcutwidth = $this->params->get('shortcut_width', '90');
$shortcuttext = $this->params->get('shortcut_text', '1');
$user = &JFactory::getUser();
?>

<ul class="shortcut-buttons-set">
	<?php for ($i = 1; $i < 21; $i++) : ?>
	<?php if ($this->params->get('link'.$i.'_enable') == 'enabled' && $user->get('gid') >= $this->params->get('link'.$i.'_access')) : ?>
	<li><a class="shortcut-button" href="<?php echo $this->params->get('link'.$i.'_link'); ?>" style="width: <?php echo $shortcutwidth; ?>px;<?php if ($shortcutheight !== '') echo " height: ".$shortcutheight."px;"; ?>"><span>
		<?php if ($iconsize !== 'disabled') : ?>
		<img src="templates/<?php echo  $this->template; ?>/images/icons/shortcuts/<?php echo $iconsize; ?>/<?php echo $this->params->get('link'.$i.'_icon'); ?>" alt="icon" title="<?php echo $this->params->get('link'.$i.'_text'); ?>" class="toolTipImg" /><br />
		<?php endif; ?>
		<?php if ($this->params->get('link'.$i.'_text') !== '' && $shortcuttext == '1') echo $this->params->get('link'.$i.'_text'); ?>
		</span></a></li>
	<?php endif; ?>
	<?php endfor; ?>
</ul>
<div class="clear"></div>
