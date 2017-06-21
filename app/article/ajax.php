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
			'list'
		);

		return $rule_action;
	}

	public function setup()
	{
		HTTP::no_cache_header();
	}

	public function save_comment_action()
	{
		if (!$article_info = $this->model('article')->get_article_info_by_id($_POST['article_id']))
		{
			H::ajax_json_output(AWS_APP::RSM(null, '-1', AWS_APP::lang()->_t('指定文章不存在')));
		}

		if ($article_info['lock'] AND !($this->user_info['permission']['is_administortar'] OR $this->user_info['permission']['is_moderator']))
		{
			H::ajax_json_output(AWS_APP::RSM(null, '-1', AWS_APP::lang()->_t('已经锁定的文章不能回复')));
		}

		$message = trim($_POST['message'], "\r\n\t");

		if (! $message)
		{
			H::ajax_json_output(AWS_APP::RSM(null, '-1', AWS_APP::lang()->_t('请输入回复内容')));
		}

		if (strlen($message) < get_setting('answer_length_lower'))
		{
			H::ajax_json_output(AWS_APP::RSM(null, '-1', AWS_APP::lang()->_t('回复内容字数不得少于 %s 字节', get_setting('answer_length_lower'))));
		}

		if (! $this->user_info['permission']['publish_url'] AND FORMAT::outside_url_exists($message))
		{
			H::ajax_json_output(AWS_APP::RSM(null, '-1', AWS_APP::lang()->_t('你所在的用户组不允许发布站外链接')));
		}

		if (human_valid('answer_valid_hour') and ! AWS_APP::captcha()->is_validate($_POST['seccode_verify']))
		{
			H::ajax_json_output(AWS_APP::RSM(null, '-1', AWS_APP::lang()->_t('请填写正确的验证码')));
		}

		// !注: 来路检测后面不能再放报错提示
		if (! valid_post_hash($_POST['post_hash']))
		{
			H::ajax_json_output(AWS_APP::RSM(null, '-1', AWS_APP::lang()->_t('页面停留时间过长,或内容已提交,请刷新页面')));
		}

		if ($this->publish_approval_valid($message))
		{
			$this->model('publish')->publish_approval('article_comment', array(
				'article_id' => intval($_POST['article_id']),
				'message' => $message,
				'at_uid' => intval($_POST['at_uid'])
			), $this->user_id);

			H::ajax_json_output(AWS_APP::RSM(array(
				'url' => get_js_url('/publish/wait_approval/article_id-' . intval($_POST['article_id']) . '__is_mobile-' . $_POST['_is_mobile'])
			), 1, null));
		}
		else
		{
			$comment_id = $this->model('publish')->publish_article_comment($_POST['article_id'], $message, $this->user_id, $_POST['at_uid']);

			//$url = get_js_url('/article/' . intval($_POST['article_id']) . '?item_id=' . $comment_id);

			$comment_info = $this->model('article')->get_comment_by_id($comment_id);

			$comment_info['message'] = $this->model('question')->parse_at_user($comment_info['message']);

			TPL::assign('comment_info', $comment_info);

			if (is_mobile())
			{
				H::ajax_json_output(AWS_APP::RSM(array(
					'ajax_html' => TPL::output('m/ajax/article_answer', false)
				), 1, null));
			}
			else
			{
				H::ajax_json_output(AWS_APP::RSM(array(
					'ajax_html' => TPL::output('article/ajax/comment', false)
				), 1, null));
			}
		}
	}

	public function lock_action()
	{
		if (!$this->user_info['permission']['is_moderator'] AND !$this->user_info['permission']['is_administortar'])
		{
			H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('你没有权限进行此操作')));
		}

		if (! $article_info = $this->model('article')->get_article_info_by_id($_POST['article_id']))
		{
			H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('文章不存在')));
		}

		$this->model('article')->lock_article($_POST['article_id'], !$article_info['lock']);

		H::ajax_json_output(AWS_APP::RSM(null, 1, null));
	}

	public function remove_article_action()
	{
		if (!$this->user_info['permission']['is_administortar'] AND !$this->user_info['permission']['is_moderator'])
		{
			H::ajax_json_output(AWS_APP::RSM(null, '-1', AWS_APP::lang()->_t('对不起, 你没有删除文章的权限')));
		}

		if ($article_info = $this->model('article')->get_article_info_by_id($_POST['article_id']))
		{
			if ($this->user_id != $article_info['uid'])
			{
				$this->model('account')->send_delete_message($article_info['uid'], $article_info['title'], $article_info['message']);
			}

			$this->model('article')->remove_article($article_info['id']);
		}

		H::ajax_json_output(AWS_APP::RSM(array(
			'url' => get_js_url('/')
		), 1, null));
	}

	public function remove_comment_action()
	{
		if (!$this->user_info['permission']['is_administortar'] AND !$this->user_info['permission']['is_moderator'])
		{
			H::ajax_json_output(AWS_APP::RSM(null, '-1', AWS_APP::lang()->_t('对不起, 你没有删除评论的权限')));
		}

		if ($comment_info = $this->model('article')->get_comment_by_id($_POST['comment_id']))
		{
			$this->model('article')->remove_comment($comment_info['id']);
		}

		H::ajax_json_output(AWS_APP::RSM(array(
			'url' => get_js_url('/article/' . $comment_info['article_id'])
		), 1, null));
	}

	public function article_vote_action()
	{
		switch ($_POST['type'])
		{
			case 'article':
				$item_info = $this->model('article')->get_article_info_by_id($_POST['item_id']);
			break;

			case 'comment':
				$item_info = $this->model('article')->get_comment_by_id($_POST['item_id']);
			break;

		}

		if (!$item_info)
		{
			H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('内容不存在')));
		}

		if ($item_info['uid'] == $this->user_id)
		{
			H::ajax_json_output(AWS_APP::RSM(null, -1, AWS_APP::lang()->_t('不能对自己发表的内容进行投票')));
		}

		$reputation_factor = $this->model('account')->get_user_group_by_id($this->user_info['reputation_group'], 'reputation_factor');

		$this->model('article')->article_vote($_POST['type'], $_POST['item_id'], $_POST['rating'], $this->user_id, $reputation_factor, $item_info['uid']);

		H::ajax_json_output(AWS_APP::RSM(null, 1, null));
	}

	public function set_recommend_action()
	{
		if (!$this->user_info['permission']['is_administortar'] AND !$this->user_info['permission']['is_moderator'])
		{
			H::ajax_json_output(AWS_APP::RSM(null, '-1', AWS_APP::lang()->_t('对不起, 你没有设置推荐的权限')));
		}

		switch ($_POST['action'])
		{
			case 'set':
				$this->model('article')->set_recommend($_POST['article_id']);
			break;

			case 'unset':
				$this->model('article')->unset_recommend($_POST['article_id']);
			break;
		}

		H::ajax_json_output(AWS_APP::RSM(null, 1, null));
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

		echo TPL::output('article/ajax/get_article', false);
	}
}