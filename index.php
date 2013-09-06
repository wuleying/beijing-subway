<title>北京地铁查询</title>
<?php
$getTime = explode(' ', microtime());
$beginTime = $getTime[0] + $getTime[1];
header("Content-type:text/html;charset=utf-8");
set_time_limit(0);
error_reporting(E_ALL ^ E_NOTICE);

include ('functions.php');
include ('subwaydata.php');
include ('subway_class.php');

$start = $_GET['start'];
$end = $_GET['end'];

echo "<form name=\"subway\" method=\"get\" action=\"\">";
echo "<input type=\"text\" name=\"start\" value=\"{$start}\" />";
echo "<input type=\"text\" name=\"end\" value=\"{$end}\" />";
echo "<input type=\"submit\" />";
echo "</form>";

if ($start && $end)
{
	$subwayClass = new SubwayClass($subway, $transfers, $loopLines);
	$result = $subwayClass->takeSubway($start, $end);

	if (empty($result))
	{
		echo '<hr />错误：<br />1.起始站与终点站可能相同。<br />2.起始站或终点站输入不正确。';
	}
	else
	{
		echo '经过站点数量：' . $subwayClass->getShortestRouteNumber();
		echo '<hr />';
		echo $result;
	}
}

$getTime = explode(' ', microtime());
$endTime = $getTime[0] + $getTime[1];
echo '<hr />';
echo '耗时:' . ($endTime - $beginTime) . '<br />';