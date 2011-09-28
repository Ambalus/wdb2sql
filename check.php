<pre><?php
error_reporting(E_ALL); 

include_once('config.php');
include_once('fmt.php');
include_once('struct.php');
include_once('dbsimple/Generic.php'); // including simple conecting for DB
include_once('wdb.class.php'); // including simple conecting for DB
define('BASE_BUILD_CLIENT',0);

$wdb = new WDBparser();

function get_type($char=null){
	switch($char){
		case FT_INT:	return 'uint32';
		case FT_IND:	return 'uint32';
		case FT_FLOAT:	return 'float';
		case FT_SORT:	return 'sorted';
		case FT_STRING:	return 'string';
		case FT_BYTE:	return 'uint8';
		case FT_LOGIC:	return 'bool';
		case FT_NA:		return 'unknown';
		case FT_NA_BYTE:return 'unknown';
		default:		return 'unknown';
	};
}

function update_xml(){
	global $WDBstruct, $WDBfmt,$wdb;
	$_str = "\t<field name=\"%s\" type=\"%s\"%s />\r\n";
	$_sfld = "\t<field type=\"%s\" count=\"%s\" />\r\n";
	$_sstr = "\t\t<element name=\"%s\" type=\"%s\" />\r\n";
	$_skip = "\t<field type=\"%s\" />\r\n";

	foreach($WDBfmt as $name => $format){
		$struct = $WDBstruct[$name];
		$wdb->_set($name);
		$wdb->getHeader();
		$fh = fopen('xml/'.$name.'.xml', 'wb');
		fwrite($fh, "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n");
		// fwrite($fh, "<!-- \$id: $name.xml ".date('Y-m-d H:i:s')."  SergiK_KilleR $ -->\r\n\r\n");
		fwrite($fh, "<WDB name=\"$name\" format=\"".$wdb->format."\" build=\"".$wdb->build."\">\r\n");
		
		$itr_skip = 0;
		$id = 0;
		for($i=0;$i< $wdb->countFields;$i++){
			$id = ($itr_skip > 0)? ($i - $itr_skip) : $i;
			if(!isset($struct[$id]))
				continue;
			switch($format[$i]){
				case FT_SIZE_ROW:
					fprintf($fh,$_skip,'size');
					$itr_skip += 1;
					break;
				case FT_SKIP_INT: // SKIP_CELL_INT
					fprintf($fh,$_skip,'uint32');
					$itr_skip += 1;
					break;
				case FT_SKIP_BYTE: // SKIP_CELL_BYTE
					fprintf($fh,$_skip,'string');
					$itr_skip += 1;
					break;
				default:
					// print_r($id);
					$data = $struct[$id];
					if(is_array($data)){
						switch($data[1]){
							case INDEX_PRIMARY_KEY:
								fprintf($fh,$_str,$data[0],get_type($format[$i])," key=\"yes\"");
								break;
							case STRUCT_DATA:
								fprintf($fh,$_sfld,STRUCT_DATA,$data[2]);
								$subdata = $data[0];
								$_id = $id;
								foreach($subdata as $sdata){
									
									if(is_array($sdata)){
										for($c=0;$c<$sdata[1];$c++){
											fprintf($fh,$_sstr,$sdata[0].$c,get_type($format[$_id+$c+1]));
										}
										$_id  += $sdata[1];
									}else{
										fprintf($fh,$_sstr,$sdata,get_type($format[$_id+1]));
										$_id++;
									}
									
								}
								fprintf($fh,"\t</field>\r\n");
								break;
							default:
								for($count=0;$count<$data[1];$count++){
									fprintf($fh,$_str,$data[0].$count,get_type($format[$i+$count]),'');
								}
								break;
						}
					}else{
						fprintf($fh,$_str,$data,get_type($format[$i]),'');
					}
					break;
			}
		}
		fwrite($fh, "</WDB>");
	}
}


update_xml();

