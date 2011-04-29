<?php
function getDirFileTree($dir, $depth = 0) {
    if (is_dir($dir)) {
        $dirStrLen = strlen($dir) - 1;
        if (strrpos($dir, '/') != $dirStrLen) {
            $dir .= '/';
        }
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false){
                $ft = filetype($dir.$file);
                if ($ft == 'dir' && $file != '.svn' && $file != '.' && $file != '..') {
                    $dirTree[] = getDirFileTree($dir.$file, $depth+1);
                } else if ($ft == 'file') {
                    $dirTree[] = $dir.$file;
                }
            }
            $dirTree['dirname'] = $dir;
            $dirTree['depth']   = $depth;
            closedir($dh);
            return $dirTree;
        }
    }
}
$f_dir      = trim($_REQUEST['dir']);
$fileurl    = trim($_REQUEST['fileurl']);
$cid        = trim($_REQUEST['cid']);
$filepath   = trim($_REQUEST['filepath']);
/*
$f_dir      = '/data/htdocs/apphistory_news_ifeng_com/figure/images';
$fileurl    = 'http://apphistory.news.ifeng.com/figure/images';
$cid        = 'res_news';
$filepath   = 'apphistory/figure/images/';
 */

// fsync_url
$fsync_base_url = 'http://fsync.ifeng.com/sync/sync2.do?fileurl=';

// 源路径
if (strrpos($f_dir, '/') != strlen($f_dir) - 1) {
    $f_dir .= '/';
}

// URL路径
if (strrpos($fileurl, '/') != strlen($fileurl) - 1) {
    $fileurl .= '/';
}

// 同步路径
if (strrpos($filepath, '/') != strlen($filepath) - 1) {
    $filepath .= '/';
}
/**
 * 同步本地目录
 */
function syncDir($dir) {
    global $fsync_base_url, $fileurl, $filepath, $cid, $f_dir;
    if (is_dir($dir)) {
        $dirStrLen = strlen($dir) - 1;
        if (strrpos($dir, '/') != $dirStrLen) {
            $dir .= '/';
        }
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false){
                $ft = filetype($dir.$file);
                if ($ft == 'dir' && $file != '.svn' && $file != '.' && $file != '..') {
                   $log[] = syncDir($dir.$file);
                } else if ($ft == 'file') {
                    $dir_path   = str_replace($f_dir, '', $dir);
                    $f_url      = $fileurl.$dir_path.$file;
                    $f_path     = $filepath.$dir_path.$file;
                    $fsync_url  = $fsync_base_url.$f_url.'&fe.cid='.$cid.'&fe.filepath='.$f_path;
                    $ret_xml    = file_get_contents($fsync_url);
                    $status     = strpos($ret_xml, "<ret>0</ret>") === false ? '失败' : '成功';
                    $log[]      = array (
                        'dir'   => $dir.$file,
                        'url'   => $f_url,
                        'status'=> $status
                    );
                }
            }
            closedir($dh);
            return $log;
        }
    }
}
echo json_encode(syncDir($f_dir));
