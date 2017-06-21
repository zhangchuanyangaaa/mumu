<?php
if (!defined('IN_ANWSION'))
{
	die;
}

class main extends AWS_CONTROLLER //继承基类
{
	
//系统默认权限判断，目前只需关系$rule_action这个数组元素
public function get_access_rule()
{
  $rule_action['rule_type'] = 'white';
  if ($this->user_info['permission']['visit_site'])
  {
      $rule_action['actions'][] = 'apply'; //非登录提交数据，这个apply行为被允许操作
  }
  return $rule_action;
}


//默认执行为空
function index_action() 
{             

}

public function apply_action() 
{
  TPL::output('link/ajax_apply');
} 


}




?>