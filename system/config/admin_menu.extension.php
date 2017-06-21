<?php
$config[] = array(
    'title' => AWS_APP::lang()->_t('绩效'),
    'cname' => 'count',
    'url' => 'admin/',
    'children' => array(
    	array(
    		'id' => 'review_index',
            'title' => AWS_APP::lang()->_t('文章统计'),
            'url' => 'admin/review/index'
    	),
		array(
    		'id' => 'wenda_index',
            'title' => AWS_APP::lang()->_t('问答统计'),
            'url' => 'admin/wenda/index'
    	),    	
    )
);
