<?php
// +----------------------------------------------------------------------
// | VhakePHP [QiqiStudio]
// +----------------------------------------------------------------------
// | 版权所有 2015~2018 Vhake Shenzhen
// +----------------------------------------------------------------------
// | 官方网站: http://www.vhake.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------

/**
 * 插件配置信息
 */
return [
    ['radio', 'status', '单选', '', ['1' => '开启', '0' => '关闭'], 1],
    ['text', 'text', '单行文本', '提示', 'x'],
    ['textarea', 'textarea', '多行文本', '提示'],
    ['checkbox', 'checkbox', '多选', '提示', ['1' => '是', '0' => '否'], 0],
    ['group',
        [
            '分组1' => [
                ['radio', 'status1', '单选', '', ['1' => '开启', '0' => '关闭'], 1],
                ['text', 'text1', '单行文本', '提示', 'x'],
                ['array', 'textarea1', '多行文本2', '提示'],
                ['checkbox', 'checkbox1', '多选', '提示', ['1' => '是', '0' => '否'], 0],
            ],
            '分组2' => [
                ['textarea', 'textarea2', '多行文本', '提示'],
                ['checkbox', 'checkbox2', '多选', '提示', ['1' => '是', '0' => '否'], 0],
            ]
        ]
    ]
];
