<?php
if ($task=='add' || $task=='view' || $task=='reply') {

	$pq = phpQuery::newDocument($buffer);


	pq('form[name=adminForm] fieldset.adminform')->parents('form[name=adminForm])')->wrapInner('<div class="mc-form-frame" />');
	pq('div.col:last')->addClass('mc-last-column');
	pq('form[name=adminForm] table.adminform table:not(".mc-filter-table")')->wrapAll('<div class="mc-form-frame mc-padding" />');
	pq('form[name=adminForm] > table.admintable,form[name=adminForm] > table.adminform')->wrapAll('<div class="mc-form-frame mc-padding" />');
	pq('table.mc-filter-table')->parent('div.mc-form-frame')->removeClass('mc-form-frame');

	$inputfield = pq('form[name=adminForm] table:not(".adminlist")')->find(':input[size]');

	foreach($inputfield->elements as $obj){
		$obj = pq($obj);
		$width = 'size';
		$size = round((int) $obj->attr($width) * 0.6);
		$obj->attr($width, $size);
	};

	$buffer = $pq->getDocument()->htmlOuter();
} else {

	$pq = phpQuery::newDocument($buffer);

	pq('form[name=adminForm] table:first')->addClass('mc-first-table mc-filter-table');
	pq('table.adminlist ')->addClass('mc-list-table');
	pq('table.adminlist')->prev('table')->addClass('mc-filter-table');
	
	pq('form[name=adminForm] table.mc-first-table')->next('table')->addClass('mc-second-table');
	pq('form[name=adminForm] table.mc-first-table tr:first td:first,form[name=adminForm] table.mc-first-table tr:first th:first')->addClass('mc-first-cell');
	pq('form[name=adminForm] table.mc-first-table tr:first td:last,form[name=adminForm] table.mc-first-table tr:first th:last')->addClass('mc-last-cell');

	
	$buffer = $pq->getDocument()->htmlOuter();
}


