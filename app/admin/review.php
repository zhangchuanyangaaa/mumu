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

class review extends AWS_ADMIN_CONTROLLER
{
    public function setup()
    {
        if (!$this->user_info['permission']['is_administortar'])
        {
            H::redirect_msg(AWS_APP::lang()->_t('你没有访问权限, 请重新登录'));
        }

        @set_time_limit(0);
    }

    public function index_action()
    {


        if ($_POST['action'] == 'search')
        {
            foreach ($_POST as $key => $val)
            {
                $param[] = $key . '-' . $val;
            }

            H::ajax_json_output(AWS_APP::RSM(array(
                'url' => get_js_url('/admin/review/index/' . implode('__', $param))
            ), 1, null));
        }


        $where = array();

        $review = (array) AWS_APP::cache()->get('review');
        $groups = $this->model('account')->get_user_group_list(0);



        $group_id = (isset($_GET['group_id']) && is_numeric($_GET['group_id'])) ? (int) $_GET['group_id'] : (int) $review['group_id'];
        if($group_id > 0) {
            $where[] = "`group_id` = '".$group_id."'";
        }
        $where = (!empty($where)) ? implode(" AND ", $where) : '';

        $count = (int) $this->model()->count('users', $where);
        $users = (array) $this->model()->fetch_page('users', $where, 'uid ASC', $_GET['page'], $this->per_page);

        // 上月
        $prev_month = "date_format(FROM_UNIXTIME(add_time),'%Y-%m') = date_format(DATE_SUB(curdate(), INTERVAL 1 MONTH),'%Y-%m')";

        $curr_month = "date_format(FROM_UNIXTIME(add_time),'%Y-%m')=date_format(now(),'%Y-%m')";
        // 本月

        $_where = (!empty($_where)) ? implode(" AND ", $_where) : '';



        $items = array();
        foreach ($users as $key => $user) {
            $_uid = $user['uid'];
            $_where = "`uid` = '".$_uid."'";
            $items[$_uid]['user_name'] = $user['user_name'];
            $items[$_uid]['group_id'] = $user['group_id'];
            $items[$_uid]['group_name'] = $groups[$user['group_id']]['group_name'];

            $items[$_uid]['views']['all'] = AWS_APP::model()->sum('article', 'views', $_where);
            $items[$_uid]['views']['prev'] = AWS_APP::model()->sum('article', 'views', $_where." AND ".$prev_month);
            $items[$_uid]['views']['curr'] = AWS_APP::model()->sum('article', 'views', $_where." AND ".$curr_month);
            $items[$_uid]['count']['all'] = AWS_APP::model()->count('article', $_where);
            $items[$_uid]['count']['prev'] = AWS_APP::model()->count('article', $_where." AND ".$prev_month);
            $items[$_uid]['count']['curr'] = AWS_APP::model()->count('article', $_where." AND ".$curr_month);

            if($_GET['action'] == 'search') {
                if(isset($_GET['start_date']) && !empty($_GET['start_date'])) {
                    $_where .= " AND `add_time` >= '".strtotime($_GET['start_date'].' 00:00:00')."'";
                }
                if(isset($_GET['end_date']) && !empty($_GET['end_date'])) {
                    $_where .= " AND `add_time` <= '".strtotime($_GET['end_date'].' 23:59:59')."'";
                }
                $items[$_uid]['count']['time'] = AWS_APP::model()->count('article', $_where);
                $items[$_uid]['views']['time'] = AWS_APP::model()->sum('article', 'views', $_where);
            }
        }
        TPL::assign('menu_list', $this->model('admin')->fetch_menu_list('review_index'));
        TPL::assign('groups', $groups);
        TPL::assign('items', $items);
        $url_param = array();

        foreach($_GET as $key => $val)
        {
            if (!in_array($key, array('app', 'c', 'act', 'page')))
            {
                $url_param[] = $key . '-' . $val;
            }
        }

        TPL::assign('total_rows', $count);
        TPL::assign('pagination', AWS_APP::pagination()->initialize(array(
            'base_url' => get_js_url('/admin/review/index/') . implode('__', $url_param),
            'total_rows' => $count,
            'per_page' => $this->per_page
        ))->create_links());

        TPL::output('admin/review/list');
    }


    public function setting_action()
    {
        $groups = $this->model('account')->get_user_group_list(0);
        if ($this->is_post()){
            if((int) $_POST['group_id'] > 0 && !in_array((int) $_POST['group_id'], array_keys($groups))) {
                H::redirect_msg(AWS_APP::lang()->_t('用户组不存在'));
            }
            AWS_APP::cache()->set('review', $_POST);
            H::redirect_msg(AWS_APP::lang()->_t('模块设置成功'), "admin/review/index/");
        } else {    
            TPL::assign('menu_list', $this->model('admin')->fetch_menu_list('review_index'));
            TPL::assign('groups', $groups);
            TPL::assign('review', (array) AWS_APP::cache()->get('review'));
            TPL::output('admin/review/setting');
        }
    }


    // private function _sum($where=array()) {

    // }
}