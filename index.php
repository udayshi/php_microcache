<?php

/**
 * File based micro php caching system.
 * 
 * This script will help you quickly enable 
 * filebased caching system on the project without any dependencies.
 * This is useful for that application which do not use big server and need to use caching system.
 * I have used this script for my amazon micro instance and it boosts up my speed by 400% so I plan to share it on 
 * github.
 * 
 * This caching system can be used on any framework.
 * 
 * Example:
 *  1.  Rename your project index file from index.php to index_project.php
 *  2.  Provide $project_index_file='index_project.php';
 *  3.  Put micro cache file with name index.php where there is project index.php 
 *  4.  Create cache dir where your project index file and give 777 rights.
 * 
 *  Cheers n Enjoy Caching micro System
 * 
 * @author Uday Shiwakoti (6th March 2014)
 * 
 */

/** Caching TTL **/
$cache_time=60*60*24; //1day

/** Prefix of Caching file **/
$cache_file_prefix='fc_';

/**If you want to cache on different directory change the value **/
$cache_dir=dirname(__FILE__).'/cache/';
$use_cache=true;    

/**Index file of project which is renamed **/
$project_index_file='index_zf.php';

/**If you want to destroy cache use ?no_cache=1 on particular REQUEST URI **/
$cache_file_map=$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
if(isset($_GET['no_cache']) && ($_GET['no_cache']==1 || $_GET['no_cache']=='true')){
    $chop_params=array('?no_cache=1','?no_cache=true','&no_cache=1','&no_cache=true');
    foreach($chop_params as $param){
        $cache_file_map=  str_replace($param,'', $cache_file_map);
    }
    $use_cache=false;
}

/**Store it on DEFINE in order to avoid overriding variable **/
define('FC_USE_CACHE',$use_cache);
define('FC_CACHE_TTL',$cache_time);
define('FC_CACHE_FILE_PREFIX',$cache_file_prefix);
define('FC_CACHE_DIR',$cache_dir);
define('FC_CACHE_FILE',FC_CACHE_DIR.FC_CACHE_FILE_PREFIX.md5($cache_file_map));


/**Magic happened here where it cache all **/
function cache($buffer){
    if(FC_USE_CACHE){
        file_put_contents(FC_CACHE_FILE,$buffer);
    }
  return $buffer;
}


/**Cache checking and deliver from cache if found **/
if(file_exists(FC_CACHE_FILE)){
    if(FC_USE_CACHE){
    header("Pragma: public");
    header("Cache-Control: maxage=".time()+FC_CACHE_TTL);
    header('Expires: ' . gmdate('D, d M Y H:i:s', time()+FC_CACHE_TTL). ' GMT');

       $finfo = new finfo(FILEINFO_MIME);
       $ct  = $finfo->file(FC_CACHE_FILE); 
       header("Content-Type: ".$ct);      
        $current_time=mktime();
        $file_a_time=  fileatime(FC_CACHE_FILE);
        $diff_time=$current_time-$file_a_time;

        if($diff_time<=$cache_time){
            //cache found memory saving way of reading.
            $fp = fopen(FC_CACHE_FILE, 'r');
            while (false !== ($char = fgetc($fp))) {
                echo $char;
            }
            die(0);
        }
    }else{
        /**Remove of cache File**/
        unlink(FC_CACHE_FILE);
    }  
  }

 /**If no cache folder found,it will give message which helps to debug;) **/
if(FC_USE_CACHE){
    if(!file_exists(FC_CACHE_DIR) || !is_writeable(FC_CACHE_DIR)){
        die('<pre>Please create '.FC_CACHE_DIR.' and give writeable permission.<br/> <b>ie:</b> mkdir -p '.FC_CACHE_DIR.' && chmod 777 '.FC_CACHE_DIR.'</pre>');
    }
}    
    
ob_start("cache");


require_once $project_index_file;
