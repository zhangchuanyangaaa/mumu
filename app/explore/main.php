<?php
/*
+--------------------------------------------------------------------------
|   WeCenter [#RELEASE_VERSION#]
|   ========================================
|   by WeCenter Software
|   © 2011 - 2014 WeCenter. All Rights Reserved
|   http://www.wecenter.com
|   ========================================
|   Support: WeCenter@qq.com
|
+---------------------------------------------------------------------------
*/


if (!defined('IN_ANWSION'))
{
	die;
}

class main extends AWS_CONTROLLER
{
	public function get_access_rule()
	{
		$rule_action['rule_type'] = "white"; //'black'黑名单,黑名单中的检查  'white'白名单,白名单以外的检查

		if ($this->user_info['permission']['visit_explore'] AND $this->user_info['permission']['visit_site'])
		{
			$rule_action['actions'][] = 'index';
		}

		return $rule_action;
	}
	
	public function setup()
	{
		if (is_mobile() AND !$_GET['ignore_ua_check'])
		{
			switch ($_GET['app'])
			{
				default:
					HTTP::redirect('/m/');
				break;
			}
		}
	}

	public function index_action()
	{
		if (is_mobile())
		{
			HTTP::redirect('/m/explore/' . $_GET['id']);
		}

		if ($this->user_id)
		{
			$this->crumb(AWS_APP::lang()->_t('发现'), '/explore');

			if (! $this->user_info['email'])
			{
				HTTP::redirect('/account/complete_profile/');
			}
		}

		if ($_GET['category'])
		{
			if (is_digits($_GET['category']))
			{
				$category_info = $this->model('system')->get_category_info($_GET['category']);
			}
			else
			{
				$category_info = $this->model('system')->get_category_info_by_url_token($_GET['category']);
			}
		}

		if ($category_info)
		{
			TPL::assign('category_info', $category_info);

			$this->crumb($category_info['title'], '/category-' . $category_info['id']);

			$meta_description = $category_info['title'];

			if ($category_info['description'])
			{
				$meta_description .= ' - ' . $category_info['description'];
			}

			TPL::set_meta('description', $meta_description);
		}

		// 导航
		if (TPL::is_output('block/content_nav_menu.tpl.htm', 'explore/index'))
		{
			TPL::assign('content_nav_menu', $this->model('menu')->get_nav_menu_list('explore'));
		}

		// 边栏可能感兴趣的人
		if (TPL::is_output('block/sidebar_recommend_users_topics.tpl.htm', 'explore/index'))
		{
			TPL::assign('sidebar_recommend_users_topics', $this->model('module')->recommend_users_topics($this->user_id));
		}

		// 边栏热门用户
		if (TPL::is_output('block/sidebar_hot_users.tpl.htm', 'explore/index'))
		{
			TPL::assign('sidebar_hot_users', $this->model('module')->sidebar_hot_users($this->user_id, 5));
		}

		// 边栏热门话题
		if (TPL::is_output('block/sidebar_hot_topics.tpl.htm', 'explore/index'))
		{
			TPL::assign('sidebar_hot_topics', $this->model('module')->sidebar_hot_topics($category_info['id']));
		}

		// 边栏专题
		if (TPL::is_output('block/sidebar_feature.tpl.htm', 'explore/index'))
		{
			TPL::assign('feature_list', $this->model('module')->feature_list());
		}

		if (! $_GET['sort_type'] AND !$_GET['is_recommend'])
		{
			$_GET['sort_type'] = 'new';
		}

		if ($_GET['sort_type'] == 'hot')
		{
			$posts_list = $this->model('posts')->get_hot_posts(null, $category_info['id'], null, $_GET['day'], $_GET['page'], get_setting('contents_per_page'));
		}
		elseif ($_GET['sort_type'] == 'unresponsive') {
			$posts_list = $this->model('posts')->get_posts_list('question', $_GET['page'], get_setting('contents_per_page'), $_GET['sort_type'], null, $category_info['id'], $_GET['answer_count'], $_GET['day'], $_GET['is_recommend']);
		}
		else
		{
			$posts_list = $this->model('posts')->get_posts_list(null, $_GET['page'], get_setting('contents_per_page'), $_GET['sort_type'], null, $category_info['id'], $_GET['answer_count'], $_GET['day'], $_GET['is_recommend']);
		}

		if ($posts_list)
		{
			foreach ($posts_list AS $key => $val)
			{
				if ($val['answer_count'])
				{
					$posts_list[$key]['answer_users'] = $this->model('question')->get_answer_users_by_question_id($val['question_id'], 2, $val['published_uid']);
				}
				/* 拓展显示附件 */
				if($val['has_attach']) {
					$type = ($val['question_id']) ? 'question' : 'article';
					$att_id = ($val['question_id']) ? $val['question_id'] : $val['id'];
					$posts_list[$key]['attachs'] = $this->model('publish')->get_attach($type, $att_id, 'min');
				}
			}
		}
		/* 活跃榜 */
		if (TPL::is_output('block/dynamic_users_lists.tpl.htm', 'explore/index')) {
			$where = array();
			$where[] = "`uid` <> '".$this->user_id."'";
			$where[] = "`group_id` = '4'";			
			$sqlquery = "SELECT *, article_count + question_count + answer_count AS counts FROM `".AWS_APP::model()->get_table('users')."` WHERE ".implode(" AND ", $where)." ORDER BY counts DESC,last_active DESC";
			TPL::assign('dynamic_users_lists', $dynamic_users_lists = (array) AWS_APP::model()->query_all($sqlquery, 27, null, null));
			TPL::assign('dynamic_users_count', $dynamic_users_count = array(
				'users_counts' => $this->model('account')->get_user_count(),
				'users_active_counts' => $this->model('account')->get_user_count("valid_email = '1'"),
				'question_counts' => AWS_APP::model()->count('question'),
				'article_counts' => AWS_APP::model()->count('article'),
			));
		}

		/* 活跃用户 */
		if (TPL::is_output('block/sidebar_dynamic_users.tpl.htm', 'explore/index'))
		{
			$where = array();
			$where[] = "`uid` <> '".$this->user_id."'";
			$where[] = "`group_id` > '99'";			
			$sqlquery = "SELECT *, article_count + question_count + answer_count AS counts FROM `".AWS_APP::model()->get_table('users')."` WHERE ".implode(" AND ", $where)." ORDER BY counts DESC,last_active DESC";

			$sidebar_dynamic_users = (array) AWS_APP::model()->query_all($sqlquery, 5, null, null);
			foreach ($sidebar_dynamic_users as $key => $val) {
				$sidebar_dynamic_users[$key]['reputation_topics'] = $this->model('people')->get_user_reputation_topic($val['uid'], $val['reputation'], 1);
			}
			TPL::assign('sidebar_dynamic_users', $sidebar_dynamic_users);
		}
		/* 猜你喜欢 */
		if (TPL::is_output('block/sidebar_hot_article.tpl.htm', 'explore/index'))
		{
			$sidebar_hot_article = AWS_APP::model()->fetch_all('article', null, "RAND()", 8);
			TPL::assign('sidebar_hot_article', $sidebar_hot_article);
		}
		
		TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
			'base_url' => get_js_url('/sort_type-' . preg_replace("/[\(\)\.;']/", '', $_GET['sort_type']) . '__category-' . $category_info['id'] . '__day-' . intval($_GET['day']) . '__is_recommend-' . intval($_GET['is_recommend'])),
			'total_rows' => $this->model('posts')->get_posts_list_total(),
			'per_page' => get_setting('contents_per_page')
		))->create_links());

		TPL::assign('posts_list', $posts_list);
		TPL::assign('posts_list_bit', TPL::output('explore/ajax/explore_list', false));
		
		//friend links
		$links = $this->model('link')->get_link_list_all(30);
		TPL::assign('links', $links);

		TPL::output('explore/index');
	}
}