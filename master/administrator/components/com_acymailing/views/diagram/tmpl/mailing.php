<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php $app =& JFactory::getApplication(); $adminPath = $app->isAdmin() ? '../' : JURI::base(true)."/";
if(empty($this->mailingstats->mailid)) die('No statistics recorded yet'); ?>
<script language="JavaScript" type="text/javascript">
      function drawChartSendProcess() {
			var dataTable = new google.visualization.DataTable();
			dataTable.addColumn('string');
        	dataTable.addColumn('number');
			dataTable.addRows(3);
			dataTable.setValue(0, 0, '<?php echo intval($this->mailingstats->senthtml).' '.JText::_('SENT_HTML',true); ?>');
			dataTable.setValue(1, 0, '<?php echo intval($this->mailingstats->senttext).' '.JText::_('SENT_TEXT',true); ?>');
			dataTable.setValue(2, 0, '<?php echo intval($this->mailingstats->fail).' '.JText::_('FAILED',true); ?>');
			dataTable.setValue(0, 1, <?php echo intval($this->mailingstats->senthtml); ?>);
			dataTable.setValue(1, 1, <?php echo intval($this->mailingstats->senttext); ?>);
			dataTable.setValue(2, 1, <?php echo intval($this->mailingstats->fail); ?>);
			var vis = new google.visualization.PieChart(document.getElementById('sendprocess'));
	        var options = {
	          width: 360,
	          height: 150,
	          colors: ['#40A640','#5F78B5','#A42B37'],
	          title: '<?php echo JText::_('SEND_PROCESS',true);?>',
	          is3D:true,
	          legendTextStyle: {color:'#333333'}
	        };
	        vis.draw(dataTable, options);
      }
      function drawChartOpen(){
      		var dataTable = new google.visualization.DataTable();
			dataTable.addRows(2);
			dataTable.addColumn('string');
        	dataTable.addColumn('number');
        	dataTable.setValue(0, 0, '<?php echo intval($this->mailingstats->openunique).' '.JText::_('OPEN',true); ?>');
			dataTable.setValue(1, 0, '<?php echo intval($this->mailingstats->senthtml - $this->mailingstats->openunique).' '.JText::_('NOT_OPEN',true) ?>');
			dataTable.setValue(0, 1, <?php echo intval($this->mailingstats->openunique); ?>);
			dataTable.setValue(1, 1, <?php echo intval($this->mailingstats->senthtml - $this->mailingstats->openunique); ?>);
			var vis = new google.visualization.PieChart(document.getElementById('open'));
	        var options = {
			  width: 360,
	          height: 150,
	          colors: ['#40A640','#5F78B5'],
	          title: '<?php echo JText::_('OPEN',true);?>',
	          is3D:true,
	          legendTextStyle: {color:'#333333'}
	        };
	        vis.draw(dataTable, options);
      }
		function getDatadrawChartOpenclick(){
			var dataTable = new google.visualization.DataTable();
			dataTable.addRows(<?php echo count($this->openclick->open); ?>);
			dataTable.addColumn('string');
			dataTable.addColumn('number','<?php echo JText::_('OPEN',true); ?>');
			dataTable.addColumn('number','<?php echo JText::_('CLICKED_LINK',true);?>');
			<?php foreach($this->openclick->open as $i => $oneResult){
				if(!empty($this->openclick->legend[$i])) echo "dataTable.setValue($i, 0, '".addslashes($this->openclick->legend[$i])."');";
				echo "dataTable.setValue($i, 1, $oneResult);";
				echo "dataTable.setValue($i,2,".$this->openclick->click[$i].");";
			}
	        ?>
	        return dataTable;
		}
      function drawChartOpenclick(){
			var vis = new google.visualization.LineChart(document.getElementById('openclick'));
	        var options = {
			  width: 660,
	          height: 300,
	          colors: ['#40A640','#5F78B5'],
	          legend:'bottom',
	          title: '<?php echo JText::_('OPEN',true).' / '.JText::_('CLICKED_LINK',true); ?>',
	          legendTextStyle: {color:'#333333'}
	        };
	        vis.draw(getDatadrawChartOpenclick(), options);
      }
      function minidrawChartOpenclick(){
      	var vis = new google.visualization.LineChart(document.getElementById('miniopenclick'));
	        var options = {
			  width: 70,
	          height: 70,
	          legend:'none',
	          colors: ['#40A640','#5F78B5']
	        };
	        vis.draw(getDatadrawChartOpenclick(), options);
      }
	function getDatadrawChartOpenclickperday(){
		var dataTable = new google.visualization.DataTable();
		dataTable.addRows(<?php echo min(10,count($this->openclickday)); ?>);
		dataTable.addColumn('string');
		dataTable.addColumn('number','<?php echo JText::_('OPEN',true); ?>');
		dataTable.addColumn('number','<?php echo JText::_('CLICKED_LINK',true);?>');
		<?php $i=min(10,count($this->openclickday))-1;
		$nextDate = '';
		foreach($this->openclickday as $oneResult){
			if(!empty($nextDate) AND $nextDate != $oneResult['date']){
				echo "dataTable.setValue($i, 0, '...'); ";
				if($i-- == 0) break;
			}
			echo "dataTable.setValue($i, 0, '".addslashes($oneResult['date'])."'); ";
			echo "dataTable.setValue($i, 1, ".intval(@$oneResult['open']->totalopen)."); ";
			echo "dataTable.setValue($i,2,".intval(@$oneResult['click']->totalclick)."); ";
			$nextDate = $oneResult['nextdate'];
			if($i-- == 0) break;
		}
        ?>
        return dataTable;
	}
      function drawChartOpenclickperday(){
		var vis = new google.visualization.ColumnChart(document.getElementById('openclick'));
        var options = {
		  width: 660,
          height: 300,
          colors: ['#40A640','#5F78B5'],
          legend:'bottom',
          title: '<?php echo JText::_('OPEN',true).' / '.JText::_('CLICKED_LINK',true); ?>',
          legendTextStyle: {color:'#333333'}
        };
        vis.draw(getDatadrawChartOpenclickperday(), options);
      }
      function minidrawChartOpenclickperday(){
      	var vis = new google.visualization.ColumnChart(document.getElementById('miniopenclickperday'));
        var options = {
		  width: 70,
          height: 70,
          legend:'none',
          colors: ['#40A640','#5F78B5']
        };
        vis.draw(getDatadrawChartOpenclickperday(), options);
      }
		google.load("visualization", "1", {packages:["corechart"]});
		google.setOnLoadCallback(drawChartSendProcess);
		google.setOnLoadCallback(drawChartOpen);
		google.setOnLoadCallback(drawChartOpenclick);
		google.setOnLoadCallback(minidrawChartOpenclick);
		<?php if(!empty($this->openclickday)){ ?>
		google.setOnLoadCallback(minidrawChartOpenclickperday);
		<?php } ?>
</script>
<style>
div.miniacycharts{
	width:80px;
	float:left;
	text-align:center;
}
</style>
<div>
<div style="position:absolute;top:4px;right:5px;"><?php $app =& JFactory::getApplication(); $printimage = $app->isAdmin() ? '../images/M_images/printButton.png' : JURI::base(true).'/images/M_images/printButton.png'; ?>
	<a href="#" onclick="window.print();return false;"><img src="<?php echo $printimage; ?>" /></a></div>
<h1><?php echo $this->mailing->subject.' : '.JText::sprintf('TOTAL_EMAIL_SENT',(int) $this->mailingstats->senthtml + (int)$this->mailingstats->senttext); ?></h1>
<?php if(!empty($this->mailingstats->queue)){
	echo JText::sprintf('NB_PENDING_EMAIL',$this->mailingstats->queue,$this->mailing->subject);
} ?>
</div>
<table width="100%" border="0">
	<tr>
		<td width="50%">
			<div id="sendprocess" class="acychart"></div>
		</td>
		<td>
			<div id="open" class="acychart"></div>
		</td>
	</tr>
</table>
<br/>
<table width="100%" class="acychart" border="0">
	<tr>
		<td width="80">
			<div class="miniacycharts">
			<div id="miniopenclick"></div>
			<input type="radio" id="selectchartdrawChartOpenclick" name="selectchart" checked="checked" onclick="drawChartOpenclick()" />
			<?php if(!empty($this->openclickday)){ ?>
			<div id="miniopenclickperday" class="miniacychart" ></div>
			<input type="radio" id="selectchartdrawChartOpenclickperday" name="selectchart" onclick="drawChartOpenclickperday()" />
			<?php } ?>
			</div>
		</td>
		<td width="700">
			<div id="openclick"></div>
		</td>
	</tr>
</table>