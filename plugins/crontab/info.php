<?php
// +----------------------------------------------------------------------
// | VhakePhp框架 [ VhakePhp ]
// +----------------------------------------------------------------------
// | 版权所有 2016~2017 Vhake [ http://www.Vhake.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://VhakePhp.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------

/**
 * 模块信息
 */
return [
  'name' => 'crontab',
  'title' => '定时任务',
  'identifier' => 'crontab.meishixiu.module',
  'icon' => 'glyphicon glyphicon-time',
  'description' => '模块依赖 composer 组件 <code>mtdowling/cron-expression</code> 和 <code>guzzlehttp/guzzle</code>',
  'author' => 'Vhake',
  'author_url' => 'http://www.VhakePhp.com/',
  'version' => '1.0.0',
  'tables' => [
    'crontab',
    'crontab_log',
  ],
  'database_prefix' => 'msx_',
];
