<?php
/*
+--------------------------------------------------------------------------
|   WeCenter [#RELEASE_VERSION#]
|   ========================================
|   by Tatfook Network Team
|   © 2011 - 2014 WeCenter. All Rights Reserved
|   http://www.wecenter.com
|   ========================================
|   Support: WeCenter@qq.com
|
+---------------------------------------------------------------------------
*/

define('IN_AJAX', TRUE);

if (!defined('IN_ANWSION'))
{
	die;
}

class ajax extends AWS_CONTROLLER
{
	public function get_access_rule()
	{
		$rule_action['rule_type'] = 'white';

		$rule_action['actions'] = array(
			'get_article'
		);

		return $rule_action;
	}

	public function setup()
	{
		HTTP::no_cache_header();
	}
	
	public function get_article_action() {
		$where = array();
		$where['lock'] = 0;

		$order_by = 'rand()';
		$article_list = AWS_APP::model()->fetch_page('article', implode(' AND ', $where), $order_by, 1, get_setting('contents_per_page'));
		if ($article_list)
		{
			foreach ($article_list AS $key => $val)
			{
				$article_ids[] = $val['id'];

				$article_uids[$val['uid']] = $val['uid'];

				$article_list[$key]['comment_list'] = $this->model('article')->get_comments($val['id'], 1, 100);

			}

			$article_topics = $this->model('topic')->get_topics_by_item_ids($article_ids, 'article');
			$article_users_info = $this->model('account')->get_user_info_by_uids($article_uids);

			foreach ($article_list AS $key => $val)
			{
				if ($val['has_attach'])
				{
					$article_list[$key]['attachs'] = $this->model('publish')->get_attach('article', $val['id'], 'min');
				}				
				$article_list[$key]['user_info'] = $article_users_info[$val['uid']];

			}
		}

		TPL::assign('article_list', $article_list);

		TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
			'base_url' => get_js_url('/article/category_id-' . $_GET['category_id'] . '__feature_id-' . $_GET['feature_id']),
			'total_rows' => $article_list_total,
			'per_page' => get_setting('contents_per_page')
		))->create_links());

		echo TPL::output('rewen/ajax/get_article', false);
	}
}