<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
if ( $this->ignoreUL) {}
else {
?>
<ul <?php echo $this->tree_id;?>>
<?php 
}
   $dbsharing = $this->row->dbsharing;
   $treeparams = $this->treeparams;
   if ( !empty( $treeparams)) {
      foreach( $treeparams->children() as $param) {
         if ( $param->name() == 'params') {
            $label = $param->attributes( 'label');
?>         
	<li id="tree_<?php echo $this->node_id++; ?>"><a href="#"><?php echo JText::_( $label); ?></a><?php echo $this->getDBSharingLevel($param); ?></li>
<?php
         }
         else if ( $param->name() == 'param') {
            $label      = $param->attributes( 'label');
            $icon       = $param->attributes( 'icon');
            $openicon   = $param->attributes( 'openicon');
            $comment = '';
            if ( !empty( $icon)) {
               $comment = 'icon:' . $icon;
            }
            if ( !empty( $openicon)) {
               if ( !empty($comment)) {
                  $comment .= '; ';
               }
               $comment .= 'openicon:' . $openicon;
            }
            
            $type    = $param->attributes( 'type');
            if ( $type == 'checkbox') {
               $name    = $param->attributes( 'name');
               
               if ( !empty($comment)) {
                  $comment .= '; ';
               }
               $checked = '';
               if ( !empty( $dbsharing[$name])) {
                  $checked = 'checked="checked"';
               }
               $comment .= "beforeText:<input type=\"$type\" name=\"params[$name]\" id=\"params$name\" $checked />";
            }
            
            $description   = $param->attributes( 'description');
            $toolTips = '';
            if ( !empty( $description)) {
               if ( !empty($comment)) {
                  $comment .= '; ';
               }
               $comment .= 'toolTips:' . JText::_($description) . ';';
            }
            
            if ( !empty( $comment)) {
               $comment = '<!-- ' . $comment . ' -->';
            }
?>
	<li id="tree_<?php echo $this->node_id++; ?>"><a href="#"><?php echo $comment . JText::_( $label); ?></a><?php echo $this->getDBSharingLevel($param); ?></li>
<?php
         }
         else if ( $param->name() == 'option') {
            $name    = $treeparams->attributes( 'name');
            $type    = $treeparams->attributes( 'type');
            if ( $type == 'list') {
               $type = 'radio';
            }
            $value   = $param->attributes( 'value');
            $label   = $param->data();
            $description   = $param->attributes( 'description');
            $toolTips = '';
            if ( !empty( $description)) {
               $toolTips = 'toolTips:' . JText::_($description) . ';';
            }
            $checked = '';
            if ( !empty( $dbsharing[$name]) && $value == $dbsharing[$name]) {
               $checked = 'checked="checked"';
            }
?>
	<li id="tree_<?php echo $this->node_id++; ?>">
	   <a href="#"><!-- beforeText:<input type="<?php echo $type; ?>" name="params[<?php echo $name; ?>]" id="params<?php echo $name.$value; ?>" value="<?php echo $value; ?>" <?php echo $checked; ?> />; icon:dbsharing.gif#7; textClass:mooTree_text2win; <?php echo $toolTips; ?> --><?php echo JText::_($label); ?></a>
<?php echo $this->getDBSharingLevel($param); ?>
	</li>
<?php
         }
         else if ( $param->name() == 'tables') {
            echo $this->getDBSharingLevel($param, true);
         }
         else if ( $param->name() == 'table' || $param->name() == 'tableexcluded') {
            $iconNbr = '5';
            if ( $param->name() == 'tableexcluded') { $iconNbr = '57'; }
            $label = $param->attributes( 'name');
            $description   = $param->attributes( 'description');
            $toolTips = '';
            if ( !empty( $description)) {
               $toolTips = 'toolTips:' . JText::_($description) . ';';
            }
?>
	<li id="tree_<?php echo $this->node_id++; ?>">
	   <a href="#"><!-- icon:dbsharing.gif#<?php echo $iconNbr; ?>; <?php echo $toolTips; ?> textClass:mooTree_text2win --><?php echo $label; ?></a>
<?php echo $this->getDBSharingLevel($param); ?>
	</li>
<?php
         }
      }
   }
if ( $this->ignoreUL) {}
else {
?>      
</ul>
<?php } ?>