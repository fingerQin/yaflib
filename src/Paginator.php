<?php
/**
 * 分页类。
 * @see http://document.thinkphp.cn/manual_3_2.html#data_page
 */

namespace finger;

class Paginator
{
    public  $firstRow;           // 起始行数
    public  $listRows;           // 列表每页显示行数
    public  $parameter;          // 分页跳转时要带的参数
    public  $totalRows;          // 总行数
    public  $totalPages;         // 分页总页面数
    public  $rollPage   = 11;    // 分页栏每页显示的页数
    public  $lastSuffix = true;  // 最后一页是否显示总页数
    private $p          = '';    // 分页参数名
    private $url        = '';    // 当前链接URL
    private $nowPage    = 1;

    // 分页显示定制
    private $config = [
        'header' => '<span class="rows">共 %TOTAL_ROW% 条记录</span>',
        'prev'   => '<<',
        'next'   => '>>',
        'first'  => '1...',
        'last'   => '...%TOTAL_PAGE%',
        'theme'  => '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%'
    ];

    /**
     * 构造函数
     *
     * @param  array  $totalRows  总的记录数
     * @param  array  $listRows   每页显示记录数
     * @param  array  $parameter  分页跳转的参数
     * 
     * @return void
     */
    public function __construct($totalRows, $listRows = 20, $parameter = [])
    {
        /* 基础设置 */
        $this->p         = App::getConfig('pager', 'page');
        $this->totalRows = $totalRows; // 设置总记录数
        $this->listRows  = $listRows; // 设置每页显示行数
        $this->parameter = empty($parameter) ? $_GET : $parameter;
        $this->nowPage   = empty($_GET[$this->p]) ? 1 : intval($_GET[$this->p]);
        $this->nowPage   = $this->nowPage > 0 ? $this->nowPage : 1;
        $this->firstRow  = $this->listRows * ($this->nowPage - 1);
    }

    /**
     * 定制分页链接设置
     *
     * @param  string  $name   设置名称
     * @param  string  $value  设置值
     * 
     * @return void
     */
    public function setConfig($name, $value)
    {
        if (isset($this->config[$name])) {
            $this->config[$name] = $value;
        }
    }

    /**
     * 生成链接URL
     *
     * @param  int $page 页码
     * @return string
     */
    private function url($page)
    {
        return str_replace(urlencode('[PAGE]'), $page, $this->url);
    }

    /**
     * 组装分页链接
     * 
     * -- 适配 bootstrap css 框架。
     *
     * @return string
     */
    public function pageShow()
    {
        if (0 == $this->totalRows) {
            return '';
        }
        /* 生成URL */
        $this->parameter[$this->p] = '[PAGE]';
        $this->url = Url::getCurrentTrimPageUrl($this->parameter);
        /* 计算分页信息 */
        $this->totalPages = ceil($this->totalRows / $this->listRows); // 总页数
        if (!empty($this->totalPages) && $this->nowPage > $this->totalPages) {
            $this->nowPage = $this->totalPages;
        }
        /* 计算分页临时变量 */
        $nowCoolPage     = $this->rollPage / 2;
        $nowCoolPageCeil = ceil($nowCoolPage);
        $this->lastSuffix && $this->config['last'] = $this->totalPages;
        // 上一页
        $upRow  = $this->nowPage - 1;
        $upPage = '<li class="disabled"><a aria-label="Previous" href="#"><span aria-hidden="true">' . $this->config['prev'] . '</span></a></li>';
        if ($upPage > 0) {
            $upPage = '<li><a aria-label="Previous" href="' . $this->url($upRow) . '"><span aria-hidden="true">' . $this->config['prev'] . '</span></a></li>';
        }
        // 下一页
        $downRow  = $this->nowPage + 1;
        $downPage = '<li class="disabled"><a aria-label="Next" href="#"><span aria-hidden="true">' . $this->config['next'] . '</span></a></li>';
        if ($downRow <= $this->totalPages) {
            $downPage = '<li><a aria-label="Next" href="' . $this->url($downRow) . '"><span aria-hidden="true">' . $this->config['next'] . '</span></a></li>';
        }
        // 第一页
        $theFirst = '';
        if ($this->totalPages > $this->rollPage && ($this->nowPage - $nowCoolPage) >= 1) {
            $theFirst = '<li class="firstPage"><a aria-label="Previous" href="' . $this->url(1) . '"><span aria-hidden="true">' . $this->config['first'] . '</span></a></li>';
        }
        // 最后一页
        $theEnd = '';
        if ($this->totalPages > $this->rollPage && ($this->nowPage + $nowCoolPage) < $this->totalPages) {
            $theFirst = '<li class="endPage"><a aria-label="Next" href="' . $this->url($this->totalPages) . '"><span aria-hidden="true">' . $this->config['last'] . '</span></a></li>';
        }
        // 数字链接
        $linkPage = "";
        for($i = 1; $i <= $this->rollPage; $i ++) {
            if (($this->nowPage - $nowCoolPage) <= 0) {
                $page = $i;
            } elseif (($this->nowPage + $nowCoolPage - 1) >= $this->totalPages) {
                $page = $this->totalPages - $this->rollPage + $i;
            } else {
                $page = $this->nowPage - $nowCoolPageCeil + $i;
            }
            if ($page > 0 && $page != $this->nowPage) {
                if ($page <= $this->totalPages) {
                    $linkPage .= '<li><a href="' . $this->url($page) . '">' . $page . '</a></li>';
                } else {
                    break;
                }
            } else {
                if ($page > 0 && $this->totalPages != 1) {
                    $linkPage .= '<li class="active"><a href="#"><span class="sr-only">' . $page . '</span></a></li>';
                }
            }
        }
        // 替换分页内容
        $page_str = str_replace([
            '%HEADER%',
            '%NOW_PAGE%',
            '%UP_PAGE%',
            '%DOWN_PAGE%',
            '%FIRST%',
            '%LINK_PAGE%',
            '%END%',
            '%TOTAL_ROW%',
            '%TOTAL_PAGE%'
        ], [
            $this->config['header'],
            $this->nowPage,
            $upPage,
            $downPage,
            $theFirst,
            $linkPage,
            $theEnd,
            $this->totalRows,
            $this->totalPages
        ], $this->config['theme']);
        return "<nav aria-label=\"Page navigation\"><ul class=\"pagination\">{$page_str}</ul></nav>";
    }

    /**
     * 组装分页链接
     *
     * @return string
     */
    public function backendPageShow()
    {
        if (0 == $this->totalRows) {
            return '';
        }
        /* 生成URL */
        $this->parameter[$this->p] = '[PAGE]';
        $this->url = Url::getCurrentTrimPageUrl($this->parameter);
        /* 计算分页信息 */
        $this->totalPages = ceil($this->totalRows / $this->listRows); // 总页数
        if (!empty($this->totalPages) && $this->nowPage > $this->totalPages) {
            $this->nowPage = $this->totalPages;
        }
        /* 计算分页零时变量 */
        $nowCoolPage     = $this->rollPage / 2;
        $nowCoolPageCeil = ceil($nowCoolPage);
        $this->lastSuffix && $this->config['last'] = $this->totalPages;
        // 上一页
        $upRow    = $this->nowPage - 1;
        $upPage   = $upRow > 0 ? '<a class="prev" href="' . $this->url($upRow) . '">' . $this->config['prev'] . '</a>' : "<span>{$this->config['prev']}</span>";
        // 下一页
        $downRow  = $this->nowPage + 1;
        $downPage = ($downRow <= $this->totalPages) ? '<a class="next" href="' . $this->url($downRow) . '">' . $this->config['next'] . '</a>' : "<span>{$this->config['next']}</span>";
        // 第一页
        $theFirst = '';
        if ($this->totalPages > $this->rollPage && ($this->nowPage - $nowCoolPage) >= 1) {
            $theFirst = '<a class="first" href="' . $this->url(1) . '">' . $this->config['first'] . '</a>';
        }
        // 最后一页
        $theEnd = '';
        if ($this->totalPages > $this->rollPage && ($this->nowPage + $nowCoolPage) < $this->totalPages) {
            $theEnd = '<a class="end" href="' . $this->url($this->totalPages) . '">' . $this->config['last'] . '</a>';
        }
        // 数字连接
        $linkPage = "";
        for($i = 1; $i <= $this->rollPage; $i ++) {
            if (($this->nowPage - $nowCoolPage) <= 0) {
                $page = $i;
            } elseif (($this->nowPage + $nowCoolPage - 1) >= $this->totalPages) {
                $page = $this->totalPages - $this->rollPage + $i;
            } else {
                $page = $this->nowPage - $nowCoolPageCeil + $i;
            }
            if ($page > 0 && $page != $this->nowPage) {

                if ($page <= $this->totalPages) {
                    $linkPage .= '<a class="num" href="' . $this->url($page) . '">' . $page . '</a>';
                } else {
                    break;
                }
            } else {
                if ($page > 0 && $this->totalPages != 1) {
                    $linkPage .= '<span class="current">' . $page . '</span>';
                }
            }
        }
        // 替换分页内容
        $page_str = str_replace([
            '%HEADER%',
            '%NOW_PAGE%',
            '%UP_PAGE%',
            '%DOWN_PAGE%',
            '%FIRST%',
            '%LINK_PAGE%',
            '%END%',
            '%TOTAL_ROW%',
            '%TOTAL_PAGE%'
        ], [
            $this->config['header'],
            $this->nowPage,
            $upPage,
            $downPage,
            $theFirst,
            $linkPage,
            $theEnd,
            $this->totalRows,
            $this->totalPages
        ], $this->config['theme']);
        return "<div>{$page_str}</div>";
    }
}