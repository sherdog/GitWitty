<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<div id="rok-audit">
	<ul>
		<?php foreach($rows as $row) : ?>
			<li>
				<span class="rok-audit-user">
					<?php echo $row->username;?>
					<?php echo rokAdminAuditHelper::getGravatar($row->email,20,'mm','g',true); ?>
				</span>
				<span class="rok-audit-date">
					<?php echo $row->timestamp; ?>
				</span>
				<span class="rok-audit-details">
					<?php echo rokAdminAuditHelper::getDescription($row); ?>
				</span>
			</li>
		<?php endforeach; ?>
	</ul>
	<div class="rok-more">
		<div class="mc-button">
			<span class="button"><a href="#">load more</a></span>
		</div>
	</div>
</div>
