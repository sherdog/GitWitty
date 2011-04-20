<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
$app =& JFactory::getApplication();
$doc =& JFactory::getDocument();
$doc->addStyleSheet( ACYMAILING_CSS.'frontendedition.css' );
$pageInfo = null;
$paramBase = ACYMAILING_COMPONENT.'.'.$this->getName().$this->getLayout();
$pageInfo->filter->order->value = $app->getUserStateFromRequest( $paramBase.".filter_order", 'filter_order',	'','cmd' );
$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
$pageInfo->search = $app->getUserStateFromRequest( $paramBase.".search", 'search', '', 'string' );
$pageInfo->search = JString::strtolower( $pageInfo->search );
$selectedMail = $app->getUserStateFromRequest( $paramBase."filter_mail",'filter_mail',0,'int');
$selectedUrl = $app->getUserStateFromRequest( $paramBase."filter_url",'filter_url',0,'int');
$pageInfo->limit->value = $app->getUserStateFromRequest( $paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
$pageInfo->limit->start = $app->getUserStateFromRequest( $paramBase.'.limitstart', 'limitstart', 0, 'int' );
$database	=& JFactory::getDBO();
$filters = array();
if(!empty($pageInfo->search)){
	$searchVal = '\'%'.$database->getEscaped($pageInfo->search,true).'%\'';
	$filters[] = implode(" LIKE $searchVal OR ",$this->detailSearchFields)." LIKE $searchVal";
}
if(!empty($selectedMail)) $filters[] = 'a.mailid = '.$selectedMail;
if(!empty($selectedUrl)) $filters[] = 'a.urlid = '.$selectedUrl;
$query = 'SELECT '.implode(' , ',$this->detailSelectFields);
$query .= ' FROM '.acymailing::table('urlclick').' as a';
$query .= ' LEFT JOIN '.acymailing::table('mail').' as b on a.mailid = b.mailid';
$query .= ' LEFT JOIN '.acymailing::table('url').' as c on a.urlid = c.urlid';
$query .= ' LEFT JOIN '.acymailing::table('subscriber').' as d on a.subid = d.subid';
if(!empty($filters)) $query .= ' WHERE ('.implode(') AND (',$filters).')';
if(!empty($pageInfo->filter->order->value)){
	$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
}
$database->setQuery($query,$pageInfo->limit->start,$pageInfo->limit->value);
$rows = $database->loadObjectList();
$countQuery = 'SELECT COUNT(a.subid) FROM #__acymailing_urlclick as a';
if(!empty($pageInfo->search)){
	$countQuery .= ' LEFT JOIN '.acymailing::table('mail').' as b on a.mailid = b.mailid';
	$countQuery .= ' LEFT JOIN '.acymailing::table('url').' as c on a.urlid = c.urlid';
	$countQuery .= ' LEFT JOIN '.acymailing::table('subscriber').' as d on a.subid = d.subid';
}
if(!empty($filters)) $countQuery .= ' WHERE ('.implode(') AND (',$filters).')';
$database->setQuery($countQuery);
$pageInfo->elements->total = $database->loadResult();
if(!empty($pageInfo->search)){
	$rows = acymailing::search($pageInfo->search,$rows);
}
$pageInfo->elements->page = count($rows);
jimport('joomla.html.pagination');
$pagination = new JPagination( $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );
$filtersType = null;
$mailType = acymailing::get('type.urlmail');
$urlType = acymailing::get('type.url');
$filtersType->mail = $mailType->display('filter_mail',$selectedMail);
$filtersType->url = $urlType->display('filter_url',$selectedUrl);
$this->assignRef('filters',$filtersType);
$this->assignRef('rows',$rows);
$this->assignRef('pageInfo',$pageInfo);
$this->assignRef('pagination',$pagination);