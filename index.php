<pre><?php
error_reporting(E_ALL & ~E_WARNING); 

function getListFiles($dir="dbc/",$type='dbc') {
	$list = array();
	if(!$temp = @scandir($dir))
		return;

	foreach($temp as $f){
		if(strlen($f)>2){
			if(is_file($dir.$f)){
				$filename = explode('.',$f);
				if($filename[1]==$type){
					$list[] = $filename[0];
				}
			}
		}
	}
	return $list;
}

$_dir = 'wdb/';
$names = getListFiles('wdb/','wdb');
include_once("config.php"); // including conecting info
include_once("wdb.class.php");
include_once(FILE_FMT);
$wdb = new WDBparser();

foreach($names as $name){
	$fmt = $WDBfmt[$name];
	$wdb->_set($name);
	if(strlen($fmt) == 0)
		continue;

	$_r = array();
	$_r['name'] = $name;
	$_r = array_merge($_r,$wdb->getHeader());

	$i = 0;
	$out = array();
	$out = $wdb->getData();
	
	print_r($_r);
}


?>