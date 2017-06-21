<?php
if (!defined('IN_ANWSION'))
{
	die;
}


/**
 * http://fensi.shangc.net/?/article/api/js/&limit=12
 	支持参数：
 		limit : 调用条数
 		category_id ： 分类ID
 		is_recommend : 是否推荐（0：不推荐；1：推荐，默认全部）
 		time : 多少时间内发布的文章，单位为天，默认不限制
 		keyword ： 关键字搜索（模糊搜索标题）
 		orderby ： 排序字段（article里的所有至此排序的字段）
 		orderway ： 排序方式（支持DESC或ASC）
 */
class api extends AWS_CONTROLLER
{
	public function js_action() {
		$limit = (isset($_GET['limit'])) ? (int) $_GET['limit'] : 12;
		$orderby = (isset($_GET['orderby'])) ? $_GET['orderby'] : 'add_time';
		$orderway = (isset($_GET['orderway'])) ? $_GET['orderway'] : 'DESC';
		$where = array();
		if(isset($_GET['category_id']) && (int) $_GET['category_id'] > 0) {
			$where[] = "`category_id` = '".(int) $_GET['category_id']."'";
		}

		if(isset($_GET['keyword']) && !empty($_GET['keyword'])) {
			$where[] = "`title` LIKE '%".$_GET['keyword']."%'";
		}

		if(isset($_GET['is_recommend'])) {
			$where[] = "`is_recommend` = '".(int) $_GET['is_recommend']."'";
		}

		if(isset($_GET['time']) && (int) $_GET['time'] > 0) {
			$where[] = "`add_time` >= '".(time() - (86400 * (int) $_GET['time']))."'";
		}

		$order = $orderby." ".$orderway;
		$result = $this->model()->fetch_all('article', implode(" AND ", $where), $order, $limit);

		$html = '';
		foreach ($result as $key => $value) {
			$html .= '<li><a href="'.base_url().G_INDEX_SCRIPT.get_js_url('article/'.$value['id']).'" title="'.$value['title_fulltext'].'" target="_blank">'.$value['title'].'</a></li>';
		}
		echo "document.write('".$html."');";
	}
}