<?php
if (!defined('IN_ANWSION'))
{
	die;
}

class ajax extends AWS_CONTROLLER //继承基类
{
	
public function get_access_rule()
{
    $rule_action['rule_type'] = 'white';
    if ($this->user_info['permission']['visit_site'])
    {
        $rule_action['actions'][] = 'apply';
    }
    return $rule_action;
}

//友链提交处理
public function apply_action()
{
    $site_name = $_POST['site_name'];
    $site_url = $_POST['site_url'];
	
    //用于判断该网站地址是否已经存在
    if ($this->model('link')->is_exist_url($site_url))
    {
        H::ajax_json_output(AWS_APP::RSM(null, '-1', AWS_APP::lang()->_t('该网站已经提交过，请勿重复提交！')));
    }
    //对提交的参数进行简单的判断
    if (trim($site_name) == '')
    {
        H::ajax_json_output(AWS_APP::RSM(null, '-1', AWS_APP::lang()->_t('请输入网站名称！')));
    }
    if (trim($site_url) == '')
    {
        H::ajax_json_output(AWS_APP::RSM(null, '-1', AWS_APP::lang()->_t('请输入网站地址！')));
    }
    //调用models\link.php中的apply()，将申请数据插入数据库中
    $this->model('link')->apply($site_name,$site_url) ;
    //H::ajax_json_output(AWS_APP::RSM(null, 1, AWS_APP::lang()->_t('申请成功，等待管理员审核！')));
	H::ajax_json_output(AWS_APP::RSM(array(
                'url' => get_js_url('/publish/link_wait_approval/')
            ), 1, null));
}   



}




?>