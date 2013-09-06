<?php
class SubwayClass
{
	// 地铁数据
	private $_subwayData = array();
	// 换乘站数据
	private $_transfersData = array();
	// 环线数据
	private $_loopLinesData = array();
	// 邻接矩阵
	private $_graphMatrix = array();
	// 最短路线
	private $_shortestRoute = '';
	// 最短路线站数
	private $_shortestRouteNumber = 0;

	/**
	 * 构造函数
	 *
	 * @param array $subwayData 地铁数据
	 * @param array $transfersData 换乘站数据
	 * @param array $loopLinesData 环线数据
	 *
	 */
	public function __construct($subwayData, $transfersData, $loopLinesData)
	{
		$this->_subwayData = $subwayData;
		$this->_transfersData = $transfersData;
		$this->_loopLinesData = $loopLinesData;

		// 初始化邻接矩阵
		$this->_initGraphMatrix();
	}

	/**
	 * 开始乘车
	 *
	 * @param string $start	起点站
	 * @param string $end	终点站
	 * @return string
	 *
	 */
	public function takeSubway($start, $end)
	{
		// 起点与终点一样
		if ($start == $end)
		{
			return;
		}

		// 起点不存在
		if (!isset($this->_graphMatrix[$start]))
		{
			return;
		}

		// 终点不存在
		if (!isset($this->_graphMatrix[$end]))
		{
			return;
		}

		// 当前位置
		$currentStation = $start;
		// 树，存放每一站最近的相邻站点
		$tree = array();
		// 路径
		$paths = array();
		// 已经过的站点
		$passed = array();

		while (isset($currentStation))
		{
			// 标记当前站已经过
			$passed[$currentStation] = 1;

			// 循环邻接矩阵中相邻的站点，得到所有可能相关的站点
			foreach ($this->_graphMatrix[$currentStation] as $station => $distance)
			{
				// 如果这一站已经经过，则跳过
				if (isset($passed[$station]))
				{
					continue;
				}

				// 获取站点之间的距离
				if (isset($paths[$currentStation]['distance']))
				{
					$stationDistance = $paths[$currentStation]['distance'] + $distance;
				}
				else
				{
					$stationDistance = $distance;
				}

				// 将当前站点存入树中
				$tree[$station]['station'] = $currentStation;

				if (!isset($paths[$station]) || ($stationDistance < $paths[$station]['distance']))
				{
					if (isset($paths[$currentStation]))
					{
						$paths[$station] = $paths[$currentStation];
					}

					$paths[$station]['station'] = $currentStation;
					$paths[$station]['distance'] = $stationDistance;
				}
			}
			unset($currentStation);

			foreach ($paths as $station => $path)
			{
				// 如果这一站已经经过，则跳过
				if (isset($passed[$station]))
				{
					continue;
				}

				// 距离
				$distance = $path['distance'];

				// 获取距离最短的站点
				if (!isset($min) || $distance < $min || !isset($currentStation))
				{
					$min = $distance;
					$currentStation = $station;
				}
			}
		}

		// 标记，跳出循环的条件
		$flag = TRUE;
		// 终点
		$position = $end;

        // 由终点开始，倒着循环出最近路线
		while ($flag)
		{
			if (isset($tree[$position]['station']))
			{
				$position = $tree[$position]['station'];

				// 如果是起点站
				if ($position == $start)
				{
					$this->_shortestRoute = $position . '' . $this->_shortestRoute;
				}
				// 非起点站
				else
				{
					$this->_shortestRoute = '->' . $position . $this->_shortestRoute;
				}
				// 经过的站点数量加1
				$this->_shortestRouteNumber++;
			}
			// 中止循环
			else
			{
				$flag = FALSE;
			}
		}
		// 终点站
		$this->_shortestRoute .= '->' . $end;

		return $this->_shortestRoute;
	}

	/**
	 * 获取最短线路的站点数量
	 *
	 * @return integer
	 *
	 */
	public function getShortestRouteNumber()
	{
		return $this->_shortestRouteNumber;
	}

	/**
	 * 初始化邻接矩阵
	 *
	 * @return array
	 *
	 */
	private function _initGraphMatrix()
	{
		if (file_exists('graphmatrix.data'))
		{
			$data = json_decode(file_get_contents('graphmatrix.data'), TRUE);

			// 重新获取邻接矩阵并缓存
			if (empty($data))
			{
				$this->_createGraphMatrix();
			}
			// 使用缓存
			else
			{
				$this->_graphMatrix = $data;
				unset($data);
			}
		}
		// 获取邻接矩阵并缓存
		else
		{
			$this->_createGraphMatrix();
		}
	}

	/**
	 * 生成邻接矩阵
	 *
	 */
	private function _createGraphMatrix()
	{
		foreach ($this->_subwayData as $line => $stations)
		{
			foreach ($stations as $station)
			{
				// 换乘站
				if (in_array($station, array_keys($this->_transfersData)))
				{
					foreach ($this->_transfersData[$station] as $changeStation)
					{
						$this->_pushGraphMatrix($station, $line);
					}
				}
				// 普通站
				else
				{
					$this->_pushGraphMatrix($station, $line);
				}
			}
		}

		// 生成缓存
		$this->_createGraphMatrixCache();
	}

	/**
	 * 填充邻接矩阵
	 *
	 * @param string $station	站点
	 * @param integer $line		线路
	 *
	 */
	private function _pushGraphMatrix($station, $line)
	{
		// 当前站在这条线路中的位置
		$position = array_search($station, $this->_subwayData[$line]);

		// 上一站
		if ($position - 1 >= 0)
		{
			$this->_graphMatrix[$station][$this->_subwayData[$line][$position - 1]] = 1;
		}

		// 下一站
		if ($position + 1 < count($this->_subwayData[$line]))
		{
			$this->_graphMatrix[$station][$this->_subwayData[$line][$position + 1]] = 1;
		}
	}

	/**
	 * 生成邻接矩阵缓存
	 *
	 */
	private function _createGraphMatrixCache()
	{
		file_put_contents('graphmatrix.data', json_encode($this->_graphMatrix));
	}
}