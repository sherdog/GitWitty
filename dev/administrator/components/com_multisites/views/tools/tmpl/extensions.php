<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
jimport('joomla.html.pane');
$pane =& JPane::getInstance(); 
echo $pane->startPane( 'pane' );

echo $pane->startPanel( JText::_( 'Detail'), 'paneldetail' );
echo $this->loadTemplate('detail');
echo $pane->endPanel();

echo $pane->startPanel( JText::_( 'Components'), 'panelcomponents' );
echo $this->loadTemplate('components');
echo $pane->endPanel();

echo $pane->startPanel( JText::_( 'Modules'), 'panelmodules' );
echo $this->loadTemplate('modules');
echo $pane->endPanel();

echo $pane->startPanel( JText::_( 'Plugins'), 'panelplugins' );
echo $this->loadTemplate('plugins');
echo $pane->endPanel();

echo $pane->startPanel( JText::_( 'Tables'), 'paneltables' );
echo $this->loadTemplate('tables');
echo $pane->endPanel();

echo $pane->endPane();
?>
