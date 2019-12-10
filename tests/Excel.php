<?php
/**
 * Excel 导入导出工具类使用。
 * @author fingerQin
 * @date 2019-12-10
 */

use finger\App;
use finger\Excel;

require(__DIR__ . '/../vendor/autoload.php');

$config = [
    'app' => [
        'debug'     => true,
        'root_path' => __DIR__, // 日志输出的位置。在这个目录下的 logs 目录。
    ]
];

(new App($config));

// Excel 标题栏。
$headerTitle = ['ID', '姓名', '性别', '年龄'];

// Excel 数据。
$data = [
    ['1', '张大海', '男', '30'],
    ['2', '张二妮', '女', '24'],
    ['3', '张幺妮', '女', '20'],
    ['4', '赵大虎', '男', '28'],
    ['5', '赵东生', '男', '26']
];


// [1] 生成 Excel 到本地。
Excel::createExcel($headerTitle, $data, __DIR__, 'roster');


// [2] 导入/读取 Excel 文件。
// 默认第一行不会读取。因为当作了标题。
$excelPath = __DIR__ . '/roster.xlsx';
$excelRes  = Excel::excelImport($excelPath);
print_r($excelRes); // 会将刚刚写入 Excel 的数据全部打印出来。

// [3] 导出并从浏览器下载 Excel。
// 任何向浏览器输出下载内容之前不允许有输出。因为下载是通过 header 告知浏览器的。
Excel::excelExport($headerTitle, $data, 'roster');