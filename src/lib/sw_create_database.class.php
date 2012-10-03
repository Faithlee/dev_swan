<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */
// +---------------------------------------------------------------------------
// | SWAN [ $_SWANBR_SLOGAN_$ ]
// +---------------------------------------------------------------------------
// | Copyright $_SWANBR_COPYRIGHT_$
// +---------------------------------------------------------------------------
// | Version  $_SWANBR_VERSION_$
// +---------------------------------------------------------------------------
// | Licensed ( $_SWANBR_LICENSED_URL_$ )
// +---------------------------------------------------------------------------
// | $_SWANBR_WEB_DOMAIN_$
// +---------------------------------------------------------------------------
 
require_once 'dev_core.php';
require_once D_PATH_SWAN_SOFT . 'core.php';
require_once PATH_SWAN_LIB . 'sw_xml.class.php';
/**
+------------------------------------------------------------------------------
* 创建数据库建表语句
+------------------------------------------------------------------------------
* 
* @package 
* @version $_SWANBR_VERSION_$
* @copyright $_SWANBR_COPYRIGHT_$
* @author $_SWANBR_AUTHOR_$ 
+------------------------------------------------------------------------------
*/
class sw_create_database
{
	// {{{ const

	/**
	 * SQL语句的注释字符串  
	 */
	const DATABASE_XML = '/root/code/swansoft/docs/database/db_schema.xml';

	/**
	 * SQL语句的注释字符串  
	 */
	const SQL_COMMENT = '-- ';

	/**
	 * SQL语句的分隔符 
	 */
	 const SQL_SIGN = '`';

	/**
	 * 生成的SQL文件前缀 
	 */
	 const PREFIX = 'db_desc_';

	/**
	 * VIM折叠规则左边的标记 
	 */
	const FOLDING_SIGN_LEFT = '{{{ ';

	/**
	 * VIM折叠规则左边的标记 
	 */
	const FOLDING_SIGN_RIGHT = '}}} ';

	/**
	 * 空格 
	 */
	const SPACE_KEY = ' ';

	// }}}	
	// {{{ members

	/**
	 * 头部信息 
	 * 
	 * @var string
	 * @access protected
	 */
	protected $__header_desc = 'vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker:';

	// }}}
	// {{{ functions
	// {{{ public function run() 

	/**
	 * run 
	 * 
	 * @access public
	 * @return void
	 */
	public function run()
	{
		$xml2array = sw_xml::factory('xml2array');

		$xml2array->set_filename(self::DATABASE_XML);
		$array = $xml2array->xml2array();
		
		$tmp = isset($array['databases']['database'][0]) ? $array['databases']['database'] : $array['databases'];

		foreach ($tmp as $key => $value) {
			$output = self::PREFIX . $value['@name'] . '.sql';
			$output_str = self::SQL_COMMENT . self::SPACE_KEY . $this->__header_desc  . PHP_EOL;

			$tmp_table = isset($value['tables']['table'][0]) ? $value['tables']['table'] : $value['tables'];
			foreach ($tmp_table as $table) {
				$output_str .= $this->_parse_table($table);
			}	

			file_put_contents($output, $output_str);
		}
	}

	// }}}
	// {{{ protected function _parse_column()

	/**
	 * 解析每个表的字段
	 * 
	 * @param array $column 
	 *	  array (
	 *			 '@name' => 'gprint_id',
	 *			 'desc' => 'GPRINT id',
	 *			 'type' => 'int',
	 *			 'nullable' => 'false',
	 *			 'precision' => '11',
	 *			 'auto' => 'true',
	 *			 'unsigned' => 'true',
	 *			 'default' => '',
	 *		 ),
	 *
	 * @access protected
	 * @return array
	 */
	protected function _parse_column(array $column)
	{
		//连接描述信息
		$desc_str = self::SQL_COMMENT . $column['@name'] . PHP_EOL;
		$desc_str .= self::SQL_COMMENT . "\t" . $column['desc'] . PHP_EOL;
		// {{{ 建表语句

		$sql_str = "\t" . self::SQL_SIGN . $column['@name'] . self::SQL_SIGN . self::SPACE_KEY;

		// `column_name` type (precision) 
		$sql_str .= $column['type'] . '(' . $column['precision'] . ')' . self::SPACE_KEY;

		// charset
		if (isset($column['charset'])) {
			$sql_str .= 'CHARACTER SET' . self::SPACE_KEY . $column['charset'] . self::SPACE_KEY;
		}

		// unsigned
		if (isset($column['unsigned']) && 'true' === $column['unsigned']) {
			$sql_str .= 'UNSIGNED' . self::SPACE_KEY;
		}

		// not null
		if (isset($column['nullable']) && 'false' === $column['nullable']) {
			$sql_str .= 'NOT NULL' . self::SPACE_KEY;
		}

		// auto
		if (isset($column['auto']) && 'true' === $column['auto']) {
			$sql_str .= 'AUTO_INCREMENT' . self::SPACE_KEY;
		}

		// default
		if (isset($column['default']) && 'true' === $column['default']) {
			$sql_str .= 'DEFAULT' . $column['default'];
		}

		$sql_str .= ',' . PHP_EOL;

		// }}}
		return array('sql' => $sql_str, 'desc' => $desc_str);
	}

	// }}}
	// {{{ protected function _parse_key()

	/**
	 * 解析每个表的主键和索引
	 * 
	 * @param array $column 
	 *
	 *	 array (
	 *		 '@name' => '',
	 *		 'desc' => '',
	 *		 'type' => 'primary',
	 *		 'fields' =>
	 *			 array (
	 *				 'field' =>
	 *				 array (
	 *					'@name' => 'gprint_id',
	 *				 ),
	 *		 ),
	 *	 ),
	 *
	 * @access protected
	 * @return array
	 */
	protected function _parse_key(array $key)
	{

		if ('primary' === $key['type']) {
			$sql_str = "\t" . 'PRIMARY KEY' . self::SPACE_KEY . '(';
		}


		// `column_name` type (precision) 
		$sql_str .= $column['type'] . '(' . $column['precision'] . ')' . self::SPACE_KEY;

		// charset
		if (isset($column['charset'])) {
			$sql_str .= 'CHARACTER SET' . self::SPACE_KEY . $column['charset'] . self::SPACE_KEY;
		}

		// unsigned
		if (isset($column['unsigned']) && 'true' === $column['unsigned']) {
			$sql_str .= 'UNSIGNED' . self::SPACE_KEY;
		}

		// not null
		if (isset($column['nullable']) && 'false' === $column['nullable']) {
			$sql_str .= 'NOT NULL' . self::SPACE_KEY;
		}

		// auto
		if (isset($column['auto']) && 'true' === $column['auto']) {
			$sql_str .= 'AUTO_INCREMENT' . self::SPACE_KEY;
		}

		// default
		if (isset($column['default']) && 'true' === $column['default']) {
			$sql_str .= 'DEFAULT' . $column['default'];
		}

		$sql_str .= ',' . PHP_EOL;

		// }}}
		return array('sql' => $sql_str, 'desc' => $desc_str);
	}

	// }}}
	// {{{ protected function _parse_table()

	/**
	 * 解析每个表的字段
	 *
	 * @access protected
	 * @return string
	 */
	protected function _parse_table(array $table)
	{
		//连接描述信息
		$desc_str = PHP_EOL . self::SQL_COMMENT . self::FOLDING_SIGN_LEFT . self::SPACE_KEY;
		$desc_str .= 'table' . self::SPACE_KEY . $table['@name'] . PHP_EOL;
		$desc_str .= PHP_EOL . self::SQL_COMMENT . PHP_EOL . self::SQL_COMMENT . $table['desc'] . PHP_EOL;
		$desc_str .= self::SQL_COMMENT . PHP_EOL;


		$sql_str = PHP_EOL . 'CREATE TABLE' . self::SPACE_KEY . self::SQL_SIGN . $table['@name'] . self::SQL_SIGN;
		$sql_str .=  self::SPACE_KEY . '(' . PHP_EOL;

		$tmp_column = isset($table['columns']['column'][0]) ? $table['columns']['column'] : $table['columns'];
		foreach ($tmp_column as $key => $column) {
			$return_arr = $this->_parse_column($column);	
			$sql_str .= $return_arr['sql'];
			$desc_str .= $return_arr['desc'];
		}

		$sql_str .= ')' . self::SPACE_KEY . 'ENGINE=' . $table['engine'] . self::SPACE_KEY;
		$sql_str .= 'CHARACTER=' . $table['charset'] . ';' . PHP_EOL . PHP_EOL;
		$sql_str .= self::SQL_COMMENT . self::SPACE_KEY . self::FOLDING_SIGN_RIGHT;

		return ($desc_str . $sql_str);
	}

	// }}}
	// }}}
}

$test = new sw_create_database();
$test->run();
