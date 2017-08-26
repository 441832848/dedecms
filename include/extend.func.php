<?php
function litimgurls($imgid = 0)
{
    global $lit_imglist, $dsql;
    //获取附加表
    $row = $dsql->GetOne("SELECT c.addtable FROM #@__archives AS a LEFT JOIN #@__channeltype AS c 
                                                            ON a.channel=c.id where a.id='$imgid'");
    $addtable = trim($row['addtable']);

    //获取图片附加表imgurls字段内容进行处理
    $row = $dsql->GetOne("Select imgurls From `$addtable` where aid='$imgid'");

    //调用inc_channel_unit.php中ChannelUnit类
    $ChannelUnit = new ChannelUnit(2, $imgid);

    //调用ChannelUnit类中GetlitImgLinks方法处理缩略图
    $lit_imglist = $ChannelUnit->GetlitImgLinks($row['imgurls']);

    //返回结果
    return $lit_imglist;
}

// 获取当前父栏目的id
function getParentId($typeid)
{
    global $dsql;
    $dsql->SetQuery("SELECT * FROM #@__arctype WHERE id=$typeid");
    $dsql->Execute();
    $row = $dsql->GetArray();
    return $row['reid'];
}

// 获取当前父栏目的链接，用于list_*.htm
function getParentIdATag($typeid)
{
    global $dsql;
    $dsql->SetQuery("SELECT * FROM #@__arctype WHERE id=$typeid");
    $dsql->Execute();
    $row = $dsql->GetArray();
    $reId = $row['reid'];
    $title = $row['typename'];
    $ret = "<a href='plus/list.php?tid=" . $reId . "' title=" . $title . ">" . $title . "</a>";
    return $ret;
}

//获取文档的父栏目的父栏目的链接地址
function GetParentByDocId($docId)
{
    global $dsql;
    $row = $dsql->GetOne("SELECT * FROM #@__archives where id=$docId");
    $typeId = $row['typeid'];
    $row = $dsql->GetOne("SELECT * FROM  #@__arctype WHERE id= $typeId");
    $aTag = "<a href='" . $GLOBALS['cfg_cmspath'] . "/plus/list.php?tid=" . $row['reid'] . "' title='最新资讯文章'>最新资讯文章</a>";
    return $aTag;
}

// 获取当前文档的关键字
function getKeyWordsByDocId($docId)
{
    global $dsql;
    $dsql->SetQuery("SELECT * FROM #@__archives WHERE id=$docId");
    $dsql->Execute();
    $row = $dsql->GetArray();
    $keywords = $row['keywords'];

    $output = '';
    if (!empty($keywords)) {
        foreach (explode(",", $keywords) as $keyword) {
            $searchUrl = "search.php?channeltype=1&keyword=" . $keyword;
            $output .= "<a target='_blank' href=\"$searchUrl\">" . $keyword . "</a>";
        }
    }
    return $output;
}

// 获取文章的内容
function getContentByDocId($docId)
{
    global $dsql;
    $dsql->SetQuery("SELECT * FROM #@__addonarticle WHERE aid=$docId");
    $dsql->Execute();
    $row = $dsql->GetArray();
    $body = $row['body'];
    return $body;
}

// 首页图片分组功能
function getImageGroupByTypeId($typeid)
{
    global $dsql;
    $output = '';
    $groupNum = 6;

    $template = <<<HTML
<div class="kh02 fl">
  <ul>
    ###LI###
  </ul>
</div>
HTML;

    $dsql->SetQuery("SELECT id, litpic, title FROM #@__archives where typeid=$typeid");
    $dsql->Execute();

    $total = 0;
    $data = array();
    while ($row = $dsql->GetArray()) {
        $data[$total] = $row;
        $total++;
    }

    $groupCount = ceil($total / $groupNum);

    if ($groupCount == 1) {
        $aTag = '';
        foreach ($data as $row) {
            $id = $row['id'];
            $image = $row['litpic'];
            $title = $row['title'];
            $arcurl = GetOneArchive($id)['arcurl'];
            $temp = "<li><a href=\"$arcurl\" title=\"$title\"><img src=\"$image\" alt=\"$title\" title=\"$title\"/></a></li>";
            $aTag .= $temp;
        }
        $output .= str_replace("###LI###", $aTag, $template);
    } else {
        $aTag = '';
        $count = 0;
        $groupData = array();
        $groupIndex = 0;
        foreach ($data as $row) {
            $count++;
            $id = $row['id'];
            $image = $row['litpic'];
            $title = $row['title'];
            $arcurl = GetOneArchive($id)['arcurl'];
            $temp = "<li><a href=\"$arcurl\" title=\"$title\"><img src=\"$image\" alt=\"$title\" title=\"$title\"/></a></li>";
            $aTag .= $temp;
            if ($count == $groupNum) {
                $groupData[$groupIndex] = $aTag;
                $groupIndex++;
                $aTag = '';
                $count = 0;
            }
        }

        if ($count != 0) {
            $groupData[++$groupIndex] = $aTag;
        }

        foreach ($groupData as $item) {
            $output .= str_replace("###LI###", $item, $template);
        }
    }
    return $output;
}


// 合作伙伴的图片
function getImagesTypeId($typeid)
{
    global $dsql;
    $output = '';

    $dsql->SetQuery("SELECT id, litpic, title FROM #@__archives where typeid=$typeid");
    $dsql->Execute();

    $liTag = '';
    while ($row = $dsql->GetArray()) {
        $id = $row['id'];
        $image = $row['litpic'];
        $title = $row['title'];
        if (!empty($image)) {
            $arcurl = GetOneArchive($id)['arcurl'];
            $temp = "<li><a href=\"$arcurl\" title=\"$title\"><img src=\"$image\" alt=\"$title\" title=\"$title\"/></a></li>";
            $liTag .= $temp;
        }
    }
    return $liTag;
}