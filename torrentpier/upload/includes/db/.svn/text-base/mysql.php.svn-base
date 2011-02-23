<?php

/***************************************************************************
 *  MySQL Layer (slightly based on phpBB)
 *  (c) Meithar
 ***************************************************************************/

if (!defined('SQL_DEBUG')) die(basename(__FILE__) .": 'SQL_DEBUG' undefined");

define('SQL_LAYER',          'mysql');
define('DEFAULT_QUERY_TYPE', 'buffered');   //  buffered, unbuffered

class sql_db
{
	var $cfg            = array();
	var $link           = null;
	var $result         = null;
	var $selected_db    = null;

	var $pconnect       = false;
	var $locked         = false;
	var $locks          = array();

	var $num_queries    = 0;
	var $sql_starttime  = 0;
	var $sql_inittime   = 0;
	var $sql_timetotal  = 0;
	var $cur_query_time = 0;

	var $log_file       = 'sql_queries';
	var $log_counter    = 0;

	var $dbg            = array();
	var $dbg_id         = 0;
	var $dbg_enabled    = false;
	var $cur_query      = null;

	var $do_explain     = false;
	var $explain_hold   = '';
	var $explain_out    = '';

	var $shutdown       = array();

	/**
	* Constructor
	*/
	function sql_db ($cfg)
	{
		$this->dbg_enabled = (SQL_DEBUG && DBG_USER && (!empty($_COOKIE['sql_log']) || !empty($_COOKIE['explain'])));
		$this->do_explain  = ($this->dbg_enabled && !empty($_COOKIE['explain']));
		$this->pconnect    = $cfg['persist'];
		$this->cfg         = $cfg;
	}

	/**
	* Initialize connection
	*/
	function init ()
	{
		// Connect to server
		$this->link = $this->connect();

		// Select database
		$this->selected_db = $this->select_db();

		// Set charset
		if ($this->cfg['charset'] && !$this->sql_query("SET NAMES {$this->cfg['charset']}"))
		{
			die("Could not set charset {$this->cfg['charset']}");
		}

		$this->num_queries = 0;
		$this->sql_inittime = $this->sql_timetotal;
	}

	/**
	* Open connection
	*/
	function connect ()
	{
		$this->cur_query = ($this->pconnect ? 'p' : '') . "connect to: {$this->cfg['dbhost']}";
		$this->debug('start');

		$connect_type = ($this->pconnect) ? 'mysql_pconnect' : 'mysql_connect';

		if (!$link = @$connect_type($this->cfg['dbhost'], $this->cfg['dbuser'], $this->cfg['dbpasswd'], false, $this->cfg['flags']))
		{
			$server = (DBG_USER) ? $this->cfg['dbhost'] : '';
			header("HTTP/1.0 503 Service Unavailable");
			die("Could not connect to the server $server");
		}

		register_shutdown_function(array(&$this, 'sql_close'));

		$this->debug('stop');
		$this->cur_query = null;

		return $link;
	}

	/**
	* Select database
	*/
	function select_db ()
	{
		$this->cur_query = "select db: {$this->cfg['dbname']}";
		$this->debug('start');

		if (!@mysql_select_db($this->cfg['dbname'], $this->link))
		{
			$database = (DBG_USER) ? $this->cfg['dbhost'] : '';
			die("Could not select database $database");
		}

		$this->debug('stop');
		$this->cur_query = null;

		return $this->cfg['dbname'];
	}

	/**
	* Base query method
	*/
	function sql_query ($query, $type = DEFAULT_QUERY_TYPE)
	{
		if (!is_resource($this->link))
		{
			$this->init();
		}
		if (is_array($query))
		{
			$query = $this->build_sql($query);
		}
		$this->cur_query = $query;
		$this->debug('start');

		$query_function = ($type === 'unbuffered') ? 'mysql_unbuffered_query' : 'mysql_query';

		if (!$this->result = $query_function($query, $this->link))
		{
			$this->log_error();
		}

		$this->debug('stop');
		$this->cur_query = null;

		$this->num_queries++;

		return $this->result;
	}

	/**
	* Execute query WRAPPER (with error handling)
	*/
	function query ($query, $type = DEFAULT_QUERY_TYPE, $err_msg = '')
	{
		if (!$result = $this->sql_query($query, $type))
		{
			$this->trigger_error($err_msg);
		}

		return $result;
	}

	/**
	* Return number of rows
	*/
	function sql_numrows ($result = false)
	{
		$num_rows = false;

		if ($result OR $result = $this->result)
		{
			$num_rows = is_resource($result) ? mysql_num_rows($result) : false;
		}

		return $num_rows;
	}

	/**
	* Return number of affected rows
	*/
	function sql_affectedrows ()
	{
		return is_resource($this->link) ? mysql_affected_rows($this->link) : -1;
	}

	// Report
	/**
	* Fetch current field
	*/
	function sql_fetchfield($field, $rownum = -1, $query_id = 0)
	{
		if(!$query_id)
		{
			$query_id = $this->query_result;
		}
		if($query_id)
		{
			if($rownum > -1)
			{
				$result = @mysql_result($query_id, $rownum, $field);
			}
			else
			{
				if(empty($this->row[$query_id]) && empty($this->rowset[$query_id]))
				{
					if($this->sql_fetchrow())
					{
						$result = $this->row[$query_id][$field];
					}
				}
				else
				{
					if($this->rowset[$query_id])
					{
						$result = $this->rowset[$query_id][0][$field];
					}
					else if($this->row[$query_id])
					{
						$result = $this->row[$query_id][$field];
					}
				}
			}
			return $result;
		}
		else
		{
			return false;
		}
	}
	// Report [END]
	
	/**
	* Fetch current row
	*/
	function sql_fetchrow ($result, $result_type = MYSQL_ASSOC)
	{
		return is_resource($result) ? mysql_fetch_array($result, $result_type) : false;
	}

	/**
	* Alias of sql_fetchrow()
	*/
	function fetch_next ($result, $result_type = MYSQL_ASSOC)
	{
		return $this->sql_fetchrow($result, $result_type);
	}

	/**
	* Fetch row WRAPPER (with error handling)
	*/
	function fetch_row ($query, $type = DEFAULT_QUERY_TYPE)
	{
		if (!$result = $this->sql_query($query, $type))
		{
			$this->trigger_error();
		}

		return $this->sql_fetchrow($result);
	}

	/**
	* Fetch all rows
	*/
	function sql_fetchrowset ($result, $result_type = MYSQL_ASSOC)
	{
		$rowset = array();

		while ($row = mysql_fetch_array($result, $result_type))
		{
			$rowset[] = $row;
		}

		return $rowset;
	}

	/**
	* Fetch all rows WRAPPER (with error handling)
	*/
	function fetch_rowset ($query, $type = DEFAULT_QUERY_TYPE)
	{
		if (!$result = $this->sql_query($query, $type))
		{
			$this->trigger_error();
		}

		return $this->sql_fetchrowset($result);
	}

	/**
	* Get last inserted id after insert statement
	*/
	function sql_nextid ()
	{
		return mysql_insert_id($this->link);
	}

	/**
	* Free sql result
	*/
	function sql_freeresult ($result = false)
	{
		if ($result OR $result = $this->result)
		{
			$return_value = is_resource($result) ? mysql_free_result($result) : false;
		}

		$this->result = null;
	}

	/**
	* Escape data used in sql query
	*/
	function escape ($v, $check_type = false, $dont_escape = false)
	{
		if ($dont_escape) return $v;
		if (!$check_type) return $this->escape_string($v);

		switch (true)
		{
			case is_string ($v): return "'". $this->escape_string($v) ."'";
			case is_int    ($v): return "$v";
			case is_bool   ($v): return ($v) ? '1' : '0';
			case is_float  ($v): return "'$v'";
			case is_null   ($v): return 'NULL';
		}
		// if $v has unsuitable type
		$this->trigger_error(__FUNCTION__ .' - wrong params');
	}

	/**
	* Escape string
	*/
	function escape_string ($str)
	{
		if (!is_resource($this->link))
		{
			$this->init();
		}

		return mysql_real_escape_string($str, $this->link);
	}

	/**
	* Build SQL statement from array (based on same method from phpBB3, idea from Ikonboard)
	*
	* Possible $query_type values: INSERT, INSERT_SELECT, MULTI_INSERT, UPDATE, SELECT
	*/
	function build_array ($query_type, $input_ary, $data_already_escaped = false, $check_data_type_in_escape = true)
	{
		$fields = $values = $ary = $query = array();
		$dont_escape = $data_already_escaped;
		$check_type = $check_data_type_in_escape;

		if (empty($input_ary) || !is_array($input_ary))
		{
			$this->trigger_error(__FUNCTION__ .' - wrong params: $input_ary');
		}

		if ($query_type == 'INSERT')
		{
			foreach ($input_ary as $field => $val)
			{
				$fields[] = $field;
				$values[] = $this->escape($val, $check_type, $dont_escape);
			}
			$fields = join(', ', $fields);
			$values = join(', ', $values);
			$query = "($fields)\nVALUES\n($values)";
		}
		else if ($query_type == 'INSERT_SELECT')
		{
			foreach ($input_ary as $field => $val)
			{
				$fields[] = $field;
				$values[] = $this->escape($val, $check_type, $dont_escape);
			}
			$fields = join(', ', $fields);
			$values = join(', ', $values);
			$query = "($fields)\nSELECT\n$values";
		}
		else if ($query_type == 'MULTI_INSERT')
		{
			foreach ($input_ary as $id => $sql_ary)
			{
				foreach ($sql_ary as $field => $val)
				{
					$values[] = $this->escape($val, $check_type, $dont_escape);
				}
				$ary[] = '('. join(', ', $values) .')';
				$values = array();
			}
			$fields = join(', ', array_keys($input_ary[0]));
			$values = join(",\n", $ary);
			$query = "($fields)\nVALUES\n$values";
		}
		else if ($query_type == 'SELECT' || $query_type == 'UPDATE')
		{
			foreach ($input_ary as $field => $val)
			{
				$ary[] = "$field = ". $this->escape($val, $check_type, $dont_escape);
			}
			$glue = ($query_type == 'SELECT') ? "\nAND " : ",\n";
			$query = join($glue, $ary);
		}

		if (!$query)
		{
			bb_die('<pre><b>'. __FUNCTION__ ."</b>: Wrong params for <b>$query_type</b> query type\n\n\$input_ary:\n\n". htmlCHR(print_r($input_ary, true)) .'</pre>');
		}

		return "\n". $query ."\n";
	}

	function get_empty_sql_array ()
	{
		return array(
			'SELECT'         => array(),
			'select_options' => array(),
			'FROM'           => array(),
			'INNER JOIN'     => array(),
			'LEFT JOIN'      => array(),
			'WHERE'          => array(),
			'GROUP BY'       => array(),
			'HAVING'         => array(),
			'ORDER BY'       => array(),
			'LIMIT'          => array(),
		);
	}

	function build_sql ($sql_ary)
	{
		$sql = '';
		array_deep($sql_ary, 'array_unique', false, true);

		foreach ($sql_ary as $clause => $ary)
		{
			switch ($clause)
			{
			case 'SELECT':
				$sql .= ($ary) ? ' SELECT '. join(' ', $sql_ary['select_options']) .' '. join(', ', $ary) : '';
				break;
			case 'FROM':
				$sql .= ($ary) ? ' FROM '. join(', ', $ary) : '';
				break;
			case 'INNER JOIN':
				$sql .= ($ary) ? ' INNER JOIN '. join(' INNER JOIN ', $ary) : '';
				break;
			case 'LEFT JOIN':
				$sql .= ($ary) ? ' LEFT JOIN '. join(' LEFT JOIN ', $ary) : '';
				break;
			case 'WHERE':
				$sql .= ($ary) ? ' WHERE '. join(' AND ', $ary) : '';
				break;
			case 'GROUP BY':
				$sql .= ($ary) ? ' GROUP BY '. join(', ', $ary) : '';
				break;
			case 'HAVING':
				$sql .= ($ary) ? ' HAVING '. join(' AND ', $ary) : '';
				break;
			case 'ORDER BY':
				$sql .= ($ary) ? ' ORDER BY '. join(', ', $ary) : '';
				break;
			case 'LIMIT':
				$sql .= ($ary) ? ' LIMIT '. join(', ', $ary) : '';
				break;
			}
		}

		return trim($sql);
	}

	/**
	* Return sql error array
	*/
	function sql_error ()
	{
		$return_ary = array(
			'code'    => '',
			'message' => 'not connected',
		);

		if (is_resource($this->link))
		{
			$return_ary = array(
				'code'    => mysql_errno($this->link),
				'message' => mysql_error($this->link),
			);
		}

		return $return_ary;
	}

	/**
	* Close sql connection
	*/
	function sql_close ()
	{
		if (is_resource($this->link))
		{
			$this->unlock();

			if (!empty($this->locks))
			{
				foreach ($this->locks as $name => $void)
				{
					$this->release_lock($name);
				}
			}

			$this->exec_shutdown_queries();

			mysql_close($this->link);
		}

		$this->link = $this->selected_db = null;
	}

	/**
	* Add shutdown query
	*/
	function add_shutdown_query ($sql)
	{
		$this->shutdown['__sql'][] = $sql;
	}

	/**
	* Exec shutdown queries
	*/
	function exec_shutdown_queries ()
	{
		if (empty($this->shutdown)) return;

		// post_html
		if (!empty($this->shutdown['post_html']))
		{
			$post_html_sql = $this->build_array('MULTI_INSERT', $this->shutdown['post_html']);
			$this->query("REPLACE INTO ". POSTS_HTML_TABLE ." $post_html_sql");
		}
		// other
		if (!empty($this->shutdown['__sql']))
		{
			foreach ($this->shutdown['__sql'] as $sql)
			{
				$this->query($sql);
			}
		}
	}

	/**
	* Return the number of fields from a query
	*/
	function sql_numfields ($result)
	{
		return (is_resource($result)) ? mysql_num_fields($result) : false;
	}

	/**
	* Return the name of the field index
	*/
	function sql_fieldname ($offset, $result)
	{
		return (is_resource($result)) ? mysql_field_name($result, $offset) : false;
	}

	/**
	* Return the type of the field
	*/
	function sql_fieldtype ($offset, $result = false)
	{
		return (is_resource($result)) ? mysql_field_type($result, $offset) : false;
	}

	/**
	* Lock tables
	*/
	function lock ($tables, $lock_type = 'WRITE')
	{
		if ($this->pconnect)
		{
#			return true;
		}

		$tables_sql = array();

		foreach ((array) $tables as $table_name)
		{
			$tables_sql[] = "$table_name $lock_type";
		}
		if ($tables_sql = join(', ', $tables_sql))
		{
			$this->locked = $this->sql_query("LOCK TABLES $tables_sql");
		}

		return $this->locked;
	}

	/**
	* Unlock tables
	*/
	function unlock ()
	{
		if ($this->locked && $this->sql_query("UNLOCK TABLES"))
		{
			$this->locked = false;
		}

		return !$this->locked;
	}

	/**
	* Obtain user level lock
	*/
	function get_lock ($name, $timeout = 0)
	{
		$lock_name = $this->get_lock_name($name);
		$timeout   = (int) $timeout;
		$row = $this->fetch_row("SELECT GET_LOCK('$lock_name', $timeout) AS lock_result");

		if ($row['lock_result'])
		{
			$this->locks[$name] = true;
		}

		return $row['lock_result'];
	}

	/**
	* Obtain user level lock status
	*/
	function release_lock ($name)
	{
		$lock_name = $this->get_lock_name($name);
		$row = $this->fetch_row("SELECT RELEASE_LOCK('$lock_name') AS lock_result");

		if ($row['lock_result'])
		{
			unset($this->locks[$name]);
		}

		return $row['lock_result'];
	}

	/**
	* Release user level lock
	*/
	function is_free_lock ($name)
	{
		$lock_name = $this->get_lock_name($name);
		$row = $this->fetch_row("SELECT IS_FREE_LOCK('$lock_name') AS lock_result");
		return $row['lock_result'];
	}

	/**
	* Make per db unique lock name
	*/
	function get_lock_name ($name)
	{
		if (!$this->selected_db)
		{
			$this->init();
		}

		return "{$this->selected_db}_{$name}";
	}

	/**
	* Get info about last query
	*/
	function query_info ()
	{
		$info = array();

		if ($num = $this->sql_numrows($this->result))
		{
			$info[] = "$num rows";
		}

		if (is_resource($this->link) AND $ext = mysql_info($this->link))
		{
			$info[] = "$ext";
		}
		else if (!$num && ($aff = $this->sql_affectedrows($this->result) AND $aff != -1))
		{
			$info[] = "$aff rows";
		}

		return str_compact(join(', ', $info));
	}

	/**
	* Get server version
	*/
	function server_version ()
	{
		preg_match('#^(\d+\.\d+\.\d+).*#', mysql_get_server_info(), $m);
		return $m[1];
	}

	/**
	* Set slow query marker for xx seconds
	* This will disable counting other queries as "slow" during this time
	*/
	function expect_slow_query ($ignoring_time = 60, $new_priority = 10)
	{
		global $bb_cache;

		if ($old_priority = $bb_cache->get('dont_log_slow_query'))
		{
			if ($old_priority > $new_priority)
			{
				return;
			}
		}

		@define('IN_FIRST_SLOW_QUERY', true);
		$bb_cache->set('dont_log_slow_query', $new_priority, $ignoring_time);
	}

	/**
	* Store debug info
	*/
	function debug ($mode)
	{
		if (!SQL_DEBUG) return;

		$id  =& $this->dbg_id;
		$dbg =& $this->dbg[$id];

		if ($mode == 'start')
		{
			if (SQL_CALC_QUERY_TIME || DBG_LOG || SQL_LOG_SLOW_QUERIES)
			{
				$this->sql_starttime = utime();
			}
			if ($this->dbg_enabled)
			{
				$dbg['sql']  = $this->cur_query;
				$dbg['src']  = $this->debug_find_source();
				$dbg['file'] = $this->debug_find_source('file');
				$dbg['line'] = $this->debug_find_source('line');
				$dbg['time'] = '';
				$dbg['info'] = '';
				$dbg['mem_before'] = (MEM_USAGE) ? memory_get_usage() : null;
			}
			if ($this->do_explain)
			{
				$this->explain('start');
			}
		}
		else if ($mode == 'stop')
		{
			if (SQL_CALC_QUERY_TIME || DBG_LOG || SQL_LOG_SLOW_QUERIES)
			{
				$this->cur_query_time = utime() - $this->sql_starttime;
				$this->sql_timetotal += $this->cur_query_time;

				if (SQL_LOG_SLOW_QUERIES && $this->cur_query_time > SQL_SLOW_QUERY_TIME)
				{
					$this->log_slow_query();
				}
			}
			if ($this->dbg_enabled)
			{
				$dbg['time'] = utime() - $this->sql_starttime;
				$dbg['info'] = $this->query_info();
				$dbg['mem_after'] = (MEM_USAGE) ? memory_get_usage() : null;
				$id++;
			}
			if ($this->do_explain)
			{
				$this->explain('stop');
			}
			if ($this->log_counter)
			{
				$this->log_query($this->log_file, 4);
				$this->log_counter--;
			}
		}
	}

	/**
	* Trigger error
	*/
	function trigger_error ($msg = 'DB Error')
	{
		if (error_reporting())
		{
			if (DEBUG === true)
			{
				$err = $this->sql_error();
				$msg .= "\n". trim(sprintf('#%06d %s', $err['code'], $err['message']));
			}
			else
			{
				$msg .= " [". $this->debug_find_source() ."]";
			}

			trigger_error($msg, E_USER_ERROR);
		}
	}

	/**
	* Find caller source
	*/
	function debug_find_source ($mode = '')
	{
		foreach (debug_backtrace() as $trace)
		{
			if (!empty($trace['file']) &&  $trace['file'] !== __FILE__)
			{
				switch ($mode)
				{
					case 'file': return $trace['file'];
					case 'line': return $trace['line'];
					default: return hide_bb_path($trace['file']) .'('. $trace['line'] .')';
				}
			}
		}
		return '';
	}

	/**
	* Prepare for logging
	*/
	function log_next_query ($queries_count = 1, $log_file = 'sql_queries')
	{
		$this->log_file = $log_file;
		$this->log_counter = $queries_count;
	}

	/**
	* Log query
	*/
	function log_query ($log_file = 'sql_queries', $time_precision = 0)
	{
		$msg = array();
		$msg[] = date('m-d', $this->sql_starttime);
		$msg[] = date('H:i:s', $this->sql_starttime);
		$msg[] = sprintf('%-'.($time_precision+3).'s', sprintf("%.{$time_precision}f", $this->cur_query_time));
		$msg[] = (LOADAVG) ? sprintf('%-4s', round(get_loadavg(), 1)) : '-';
		$msg[] = sprintf('%05d', getmypid());
		$msg[] = str_compact($this->cur_query);
		$msg = join(LOG_SEPR, $msg);
		$msg .= ($info = $this->query_info()) ? ' # '. $info : '';
		$msg .= ' # '. $this->debug_find_source();
		bb_log($msg . LOG_LF, $log_file);
	}

	/**
	* Log slow query
	*/
	function log_slow_query ($log_file = 'sql_slow_bb')
	{
		if (!defined('IN_FIRST_SLOW_QUERY') && $GLOBALS['bb_cache']->get('dont_log_slow_query'))
		{
			return;
		}
		$this->log_query($log_file);
	}

	/**
	* Log error
	*/
	function log_error ()
	{
		if (!SQL_LOG_ERRORS) return;

		$msg = array();
		$err = $this->sql_error();
		$msg[] = str_compact(sprintf('#%06d %s', $err['code'], $err['message']));
		$msg[] = '';
		$msg[] = str_compact($this->cur_query);
		$msg[] = '';
		$msg[] = 'Source  : '. $this->debug_find_source();
		$msg[] = 'IP      : '. @$_SERVER['REMOTE_ADDR'];
		$msg[] = 'Date    : '. date('Y-m-d H:i:s');
		$msg[] = 'Agent   : '. @$_SERVER['HTTP_USER_AGENT'];
		$msg[] = 'Req_URI : '. @$_SERVER['REQUEST_URI'];
		$msg[] = 'Referer : '. @$_SERVER['HTTP_REFERER'];
		$msg[] = 'Method  : '. @$_SERVER['REQUEST_METHOD'];
		$msg[] = 'PID     : '. sprintf('%05d', getmypid());
		$msg[] = 'Request : '. trim(print_r($_REQUEST, true)) . str_repeat('_', 78) . LOG_LF;
		$msg[] = '';
		bb_log($msg, 'sql_error_bb');
	}

	/**
	* Explain queries (based on code from phpBB3)
	*/
	function explain ($mode, $html_table = '', $row = '')
	{
		$query = str_compact($this->cur_query);

		switch ($mode)
		{
		case 'start':
			$this->explain_hold = '';
			// TODO: добавить поддержку многотабличных запросов
			if (preg_match('#UPDATE ([a-z0-9_]+).*?WHERE(.*)/#', $query, $m))
			{
				$query = "SELECT * FROM $m[1] WHERE $m[2]";
			}
			else if (preg_match('#DELETE FROM ([a-z0-9_]+).*?WHERE(.*)#s', $query, $m))
			{
				$query = "SELECT * FROM $m[1] WHERE $m[2]";
			}

			if (preg_match('#^SELECT#', $query))
			{
				$html_table = false;

				if ($result = @mysql_query("EXPLAIN $query", $this->link))
				{
					while ($row = @mysql_fetch_assoc($result))
					{
						$html_table = $this->explain('add_explain_row', $html_table, $row);
					}
				}
				if ($html_table)
				{
					$this->explain_hold .= '</table>';
				}
			}
			break;

		case 'stop':
			if (!$this->explain_hold) break;

			$id   = $this->dbg_id-1;
			$dbg  = $this->dbg[$id];
			$file = addslashes($dbg['file']);
			$line = $dbg['line'];
			$edit = (DEBUG === true) ? "OpenInEditor('$file', $line);" : '';

			$this->explain_out .= '
				<table width="98%" cellpadding="0" cellspacing="0" class="bodyline row2 bCenter" style="border-bottom: 0px;">
				<tr>
					<th style="height: 22px; cursor: pointer;" align="left" title="Open in editor (double click)" ondblclick="'. $edit .'">&nbsp;'. $dbg['src'] .'&nbsp; ['. sprintf('%.4f', $dbg['time']) .' s]&nbsp; <i>'. $dbg['info'] .'</i></th>
					<th style="height: 22px; cursor: pointer;" align="right" title="Copy to clipboard" onclick="if (ie_copyTextToClipboard($p(\'expl_'. $id .'\'))) alert(\'SQL copied to clipboard\');">Query #'. ($this->num_queries+1) .'&nbsp;</th>
				</tr>
				<tr><td colspan="2">'. $this->explain_hold .'</td></tr>
				</table>
				<div class="sqlLog"><div id="expl_'. $id .'" class="sqlLogRow sqlExplain" style="padding: 0px;">'. sql_log_query_short($dbg['sql']) .'&nbsp;&nbsp;'. (UA_IE ? '<br /><br />' : '') .'</div></div>
				<br />';
			break;

		case 'add_explain_row':
			if (!$html_table && $row)
			{
				$html_table = true;
				$this->explain_hold .= '<table width="100%" cellpadding="3" cellspacing="1" class="bodyline" style="border-width: 0px;"><tr>';
				foreach (array_keys($row) as $val)
				{
					$this->explain_hold .= '<td class="row3 gensmall" align="center"><b>'. $val .'</b></td>';
				}
				$this->explain_hold .= '</tr>';
			}
			$this->explain_hold .= '<tr>';
			foreach (array_values($row) as $i => $val)
			{
				$class = !($i % 2) ? 'row1' : 'row2';
				$this->explain_hold .= '<td class="'. $class .' gen">'. str_replace(array("{$this->selected_db}.", ',', ';'), array('', ', ', ';<br />'), $val) .'</td>';
			}
			$this->explain_hold .= '</tr>';

			return $html_table;

			break;

		case 'display':
			echo '<a name="explain"></a><div class="genmed">'. $this->explain_out .'</div>';
			break;
		}
	}
}

function sql_log_query_short ($sql, $max_len = 6000)
{
	$max_len = max($max_len, 1000);
	$sql = str_compact($sql);

	if (empty($_COOKIE['sql_log_full']))
	{
		if (strlen($sql) > $max_len)
		{
			$sql = substr($sql, 0, $max_len-200) .' [...cut...] '. substr($sql, -180);
		}
	}

	return htmlCHR($sql);
}