<?php
/**
* 0x00 	char[4] signature 
*            0x574D4F42, // creaturecache.wdb
*            0x57474F42, // gameobjectcache.wdb
*            0x57494442, // itemcache.wdb
*            0x574E4442, // itemnamecache.wdb
*            0x57495458, // itemtextcache.wdb
*            0x574E5043, // npccache.wdb
*            0x57505458, // pagetextcache.wdb
*            0x57515354, // questcache.wdb
*            0x5752444E  // wowcache.wdb

* 0x04 	uint32 	build
* 0x08 	string 	locale
* 0x0C 	uint32 	unk1
* 0x10 	uint32 	unk2
* 0x14 	uint32 	unk3
**/

define('DEF_HEADER_SIZE',24);
if(!defined('INDEX_PRIMARY_KEY')){
	define('INDEX_PRIMARY_KEY',1);
}

// SIGNATURE
define('WDB_CREATURE','WMOB');
define('WDB_GAMEOBJECT','WGOB');
define('WDB_ITEMNAME','WNDB');
define('WDB_ITEMTEXT','WITX');
define('WDB_NPC','WNPC');
define('WDB_PAGETEXT','WPTX');
define('WDB_QUEST','WQST');
define('WDB_WOW','WRDN');

// FORMAT
define("FT_NA",'x');       // unknown, size 0x4
define("FT_NA_BYTE",'X');  // unknown, size 0x1
define("FT_STRING",'s');   // char*/string, size 0x4
define("FT_FLOAT",'f');    // float, size 0x4
define("FT_IND",'n');      // uint32, size 0x4
define("FT_INT",'i');      // uint32, size 0x4
define("FT_BYTE",'b');     // uint8, size 0x1
define("FT_SORT",'d');     // sorted, size 0x4, sorted by this field, field is not included
define("FT_LOGIC",'l');    // bool/logical, size 0x1

// SIZE / SKIP CELL
define("FT_SIZE_ROW",'!'); // uint, size 0x4, size row
define("FT_SKIP_INT",'+'); // uint, size 0x4, skip cell
define("FT_SKIP_BYTE",'-'); // null/byte, size 0x1, skip cell

define('DB_TABLE_INFO','_wdb_info_');
define('FILE_FMT','fmt.php');
define('FILE_STRUCT','struct.php');

class WDBparser 
{
	private static $_dir = 'wdb/';
	private static $_type = '.wdb';
	protected static $_signature = array(
		WDB_CREATURE,
		WDB_GAMEOBJECT,
		WDB_ITEMNAME, // 4.0.0+ -> moved to ADB
		WDB_ITEMTEXT,
		WDB_NPC,
		WDB_PAGETEXT,
		WDB_QUEST,
		WDB_WOW
	);
	protected $dom;
	var $file = null;
	var $name = null;
	var $format = null;
	var $field = null;
	var $locale = null;
	var $error = null;
	var $is_valid = false;

	var $_STR = array(
		'FILE_NOT_EXISTS' => 'Файл %s%s не найден',
		'INCORRECT_SIGNATURE' => 'Заголовок файла некорректен (%s)',
		'INCORRECT_FORMAT_FILE' => 'Ошибка в формате файла',
		'DIFF_COUNT_FIELDS' => 'fields count diff (dbc: %d, xml: %d)',
		'DIFF_SIZE_RECORDS' => 'Record size diff (dbc: %d, xml: %d)'
	);

	function __construct($filename=null){
		global $db_config;

		include_once('dbsimple/Generic.php'); // including simple conecting for DB
		$this->DB = DbSimple_Generic::connect($db_config['dbc_dns']);
		$this->DB->setErrorHandler("databaseErrorHandler");
		// $this->DB->setLogger("databaseLogHandler");
		// $this->DB->setIdentPrefix($db_config['db_prefix']);
		$this->initDB();

		if($filename == null)
			return;

		$this->_set($filename);
	}

	function _set($filename = null){
		if($filename == null)
			throw new Exception(sprintf($this->_STR['FILE_NOT_EXISTS'], $filename,self::$_type));

		$this->name = $filename;
		if(!file_exists(self::$_dir.$this->name.self::$_type)){
			throw new Exception(sprintf($this->_STR['FILE_NOT_EXISTS'], $filename,self::$_type));
		}
		$this->file = fopen(self::$_dir.$this->name.self::$_type, "rb");
		$this->format = $this->getFormat();
		$this->countFields = strlen($this->format);
	}

	public function getHeader(){
		$header = fread($this->file, DEF_HEADER_SIZE);

		$this->sign = strrev(substr($header,0,4));
		if(!in_array($this->sign, self::$_signature)){
			throw new Exception(sprintf($this->_STR['INCORRECT_SIGNATURE'],$this->sign));
			return;
		}

		$this->build = base_convert(bin2hex(strrev(substr($header,4,4))), 16, 10);
		$this->locale = strrev(substr($header,8,4));
		$this->unk1 = base_convert(bin2hex(strrev(substr($header,12,4))), 16, 10);
		$this->unk2 = base_convert(bin2hex(strrev(substr($header,16,4))), 16, 10);
		$this->unk3 = base_convert(bin2hex(strrev(substr($header,20,4))), 16, 10);

		$this->writeWDBInfo();

		return array(
			'sign' => $this->sign,
			'build' => $this->build,
			'locale' => $this->locale,
			'unk1' => $this->unk1,
			'unk2' => $this->unk3,
			'unk3' => $this->unk3
		);
	}

	private function getFormat(){
		global $WDBfmt;
		return $WDBfmt[$this->name];
	}

	public function getData(){
		$this->createTable();
		$this->DB->query("TRUNCATE ?#",'wdb_'.$this->name);
		$this->data = array();
		while(!feof($this->file)){
			$this->getRecord($out,$len);
			if($len == 0)
				return;
			$this->DB->query("INSERT INTO ?# VALUES(?a)",'wdb_'.$this->name,$out);
			unset($out);
		}
	}

	public function getRecord(&$out,&$len){
		$count = 0; $len = 0; /*$size = 0;*/ $this->endLine = false;
		$out = array();
		for ($cell = 0; $cell < $this->countFields; $cell++) {
			switch ($this->format[$cell]) {
				case FT_SIZE_ROW:
					$t = unpack("V", fread($this->file, 4));
					$len = $t[1];
					if($len == 0)
					// $size = 0;
					break;
				case FT_SKIP_INT: // SKIP_CELL_INT
					fread($this->file, 4);
					break;
				case FT_SKIP_BYTE: // SKIP_CELL_BYTE
					fread($this->file, 1);
					break;
////////////////////////////////////////////////////////////////////
				case FT_NA:
				case FT_INT:
				case FT_IND:
					$t = unpack("V", fread($this->file, 4));
					$out[] = $t[1];
					// $size += 4;
					break;
				case FT_SORT:
				case FT_FLOAT:
					$t = unpack("f", fread($this->file, 4));
					$out[] = round($t[1], 2);
					// $size += 4;
					break;
				case FT_NA_BYTE:
				case FT_BYTE:
				case FT_LOGIC:
					$t = unpack("C", fread($this->file, 1));
					$out[] = $t[1];
					// $size += 1;
					break;
				case FT_STRING:
					$s = '';
					$s =  fread($this->file, 1);
					if(!ord($s)){
						// $size += 1;
						$out[] = $s;
					}else{
						$inc = 0;
						while(ord($ch = fread($this->file, 1))){
							// $size += 1;
							$s .= $ch;
						}
						$s = str_replace("'","\'",$s);
						$s = str_replace("\"","\\\"",$s);
						$out[] = $s;
					}
					break;
				default:
					break;
			}
		}
	}

	private function initDB(){
		$result = $this->DB->selectRow("ANALYZE TABLE ?#",DB_TABLE_INFO);
		if($result['Msg_type'] == 'Error'){
			$this->DB->query("
				CREATE TABLE ?# (
				  `file` varchar(120) DEFAULT NULL,
				  `sign` varchar(80) DEFAULT NULL,
				  `build` int(10) DEFAULT NULL,
				  `locale` varchar(80) DEFAULT NULL,
				  `valid` tinyint(3) DEFAULT NULL,
				  `format` varchar(80) DEFAULT NULL,
				  PRIMARY KEY (`file`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8
			",DB_TABLE_INFO);
			$this->_initDB_();
		}
		$result = $this->DB->selectRow("ANALYZE TABLE `_wdb_fields_`");
		if($result['Msg_type'] == 'Error'){
			$this->DB->query("
				CREATE TABLE `_wdb_fields_` (
				  `filename` varchar(120) DEFAULT NULL,
				  `id` int(12) DEFAULT NULL,
				  `field` varchar(120) DEFAULT NULL,
				  `isKey` tinyint(11) DEFAULT NULL,
				  `count` int(11) DEFAULT NULL,
				  `type` varchar(120) DEFAULT NULL,
				  `type_string` varchar(120) DEFAULT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8
			");
		}
	}
	private function writeWDBInfo(){
		$this->initDB();

		$res = $this->DB->selectRow("SELECT * FROM ?# WHERE `file`=?",DB_TABLE_INFO,$this->name);
		if(isset($res['file'])){
			$this->DB->query("
				UPDATE ?# SET 
				`sign`=?,
				`build`=?d,
				`locale`=?,
				`valid`=?d
				WHERE `file`=?
			",DB_TABLE_INFO,$this->sign,$this->build,$this->locale,$this->is_valid?1:0,$this->name);
		}else{
			$this->DB->query("
				REPLACE INTO ?# VALUES (?,?,?d,?,?d,?s)
			",DB_TABLE_INFO,$this->name,$this->sign,$this->build,$this->locale,$this->is_valid?1:0,$this->format);
		}
		return;
	}

	private function _initDB_(){
		global $WDBfmt;
		if(!isset($WDBfmt)){
			include_once(FILE_FMT);
		}
		foreach($WDBfmt as $k => $v){
			$this->DB->query("INSERT IGNORE INTO ?#(`file`,`format`) VALUES(?,?)",DB_TABLE_INFO,$k,$v);
		}
	}

	private function createTable(){
		if(!$this->countFields)
			return false;

		$sql = "CREATE TABLE ?# (\n";
		$collums = 0;
		$pkey = '';
		$field = 'unk';
		for($i=0; $i < $this->countFields; $i++) {
			$skip = false;
			switch($this->format[$i]){
				case FT_SIZE_ROW:
				case FT_SKIP_INT: // SKIP_CELL_INT
				case FT_SKIP_BYTE: // SKIP_CELL_BYTE
					$skip = true;
					break;
				case FT_FLOAT:
					$sql .= "`$field$i` FLOAT DEFAULT '0'";
					break;
				case FT_IND:
					$sql .= "`$field$i` INT UNSIGNED DEFAULT '0'";
					break;
				case FT_NA:
				case FT_INT:
					$sql .= "`$field$i` INT DEFAULT '0'";
					break;
				case FT_SORT:
					$sql .= "`$field$i` DOUBLE DEFAULT '0'";
					break;
				case FT_NA_BYTE:
				case FT_LOGIC:
					$sql .= "`$field$i` TINYINT UNSIGNED DEFAULT '0'";
					break;
				case FT_BYTE:
					$sql .= "`$field$i` SMALLINT UNSIGNED DEFAULT '0'";
					break;
				case FT_STRING:
					$sql .= "`$field$i` VARCHAR(255) DEFAULT NULL";
					break;
				default:
					$this->error = $this->_STR['INCORRECT_FORMAT_FILE'];
					return false;
					break;
			}
			if(!$skip)
				$sql .= ($i+1==$this->countFields)? "\n":",\n";
		}
		$sql .= sprintf(") ENGINE=InnoDB DEFAULT CHARSET=utf8  COMMENT='Export of %s';",$this->name);
		
		$this->DB->query("DROP TABLE IF EXISTS ?#",'wdb_'.$this->name);
		$this->DB->query($sql,'wdb_'.$this->name);
	}
}

?>