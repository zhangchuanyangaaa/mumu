<?php

if (!defined('IN_ANWSION'))
{
	die;
}

class main extends AWS_CONTROLLER
{

	function get_access_rule()
	{
		$rule_action['rule_type'] = 'black';
		$rule_action['actions'] = array();
		return $rule_action;
	}

	public function setup()
	{
		header('Content-type: text/xml; charset=UTF-8');

		date_default_timezone_set('UTC');
	}

	public function index_action()
	{

		TPL::assign('list', $this->model('posts')->get_posts_list(null, null, 1000000, null, null, null, null, null, null));

		TPL::output('sitemap/index');
	}
}