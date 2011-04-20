<?php 
/*
// JoomlaWorks "Simple RSS Feed Reader" Module for Joomla! 1.5.x - Version 2.2
// Copyright (c) 2006 - 2010 JoomlaWorks, a business unit of Nuevvo Webware Ltd.
// Released under the GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
// More info at http://www.joomlaworks.gr
// Designed and developed by the JoomlaWorks team
// ***Last update: September 22nd, 2010***
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/*
Here we call the stylesheet style.css from a folder called 'css' and located at the same directory with this template file. If Joomla!'s cache is turned on, we print out the CSS include within a script tag so we're valid and the styling is included properly (it's how Joomla! works unfortunately).
*/
$filePath = substr(JURI::base(), 0, -1).str_replace(JPATH_SITE,'',dirname(__FILE__));

?>

<?php if($mainframe->getCfg('caching')): ?>
<script type="text/javascript">
	//<![CDATA[
	document.write('\
	<style type="text/css" media="all">\
		@import "<?php echo $filePath; ?>/css/style.css";\
	</style>\
	');
	//]]>
</script>
<?php else: ?>
<?php $document->addStyleSheet($filePath.'/css/style.css'); ?>
<?php endif; ?>

<div class="srfrContainer <?php echo $moduleclass_sfx; ?>">
	
	<?php if($feedsBlockPreText): ?>
	<p class="srfrPreText"><?php echo $feedsBlockPreText; ?></p>
	<?php endif; ?>
	
	<ul class="srfrList">
		<?php foreach($output as $key=>$feed): ?>
		<li class="srfrRow<?php echo $key%2; ?>">
			<?php if($feedItemTitle): ?>
			<h3><a target="_blank" href="<?php echo $feed->itemLink; ?>"><?php echo $feed->itemTitle; ?></a></h3>
			<?php endif; ?>
			
			<?php if($feedTitle): ?>
			<span class="srfrFeedSource"<?php if($feedFavicon && $feed->feedFavicon) echo ' style="display:block;padding:2px 0 2px 20px;background:url('.$feed->feedFavicon.') no-repeat 0 50%;"'; ?>>
				<a target="_blank" href="<?php echo $feed->siteURL; ?>"><?php echo $feed->feedTitle; ?></a>
			</span>
			<?php endif; ?>

			<?php if($feedItemDate): ?>
			<span class="srfrFeedItemDate"><?php echo $feed->itemDate; ?></span>
			<?php endif; ?>			

			<?php if($feedItemDescription || $feed->feedImageSrc): ?>
			<p>
				<?php if($feed->feedImageSrc): ?>
				<a target="_blank" href="<?php echo $feed->itemLink; ?>">
					<img class="srfrImage" src="<?php echo $feed->feedImageSrc; ?>" alt="<?php echo $feed->itemTitle; ?>" />
				</a>
				<?php endif; ?>
				
				<?php if($feedItemDescription): ?>
				<?php echo $feed->itemDescription; ?>
				<?php endif; ?>
			</p>
			<?php endif; ?>
			
			<?php if($feedItemReadMore): ?>
			<span class="srfrReadMore">
				<a target="_blank" href="<?php echo $feed->itemLink; ?>"><?php echo JText::_('Read more...'); ?></a>
			</span>
			<?php endif; ?>
			
			<span class="clr"></span>
		</li>
		<?php endforeach; ?>	
	</ul>
	
	<?php if($feedsBlockPostText): ?>
	<p class="srfrPostText"><?php echo $feedsBlockPostText; ?></p>
	<?php endif; ?>
	
	<?php if($feedsBlockPostLink): ?>
	<p class="srfrPostTextLink"><a href="<?php echo $feedsBlockPostLinkURL; ?>"><?php echo $feedsBlockPostLinkTitle; ?></a></p>
	<?php endif; ?>
</div>

<div class="clr"></div>
