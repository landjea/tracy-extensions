<?php
declare(strict_types=1);

namespace flight\debug\database;

use PDO;
use PDOStatement;

class PdoQueryCapture extends PDO {

	/** @var array $query_data */
	public static array $query_data = [];

	/** @var array $prepared_query_data */
	public static array $prepared_query_data = [];

	/**
	 * Construct
	 *
	 * @param string      $dsn      dsn
	 * @param string|null $username username
	 * @param string|null $password password
	 * @param array       $options  options
	 */
	public function __construct(string $dsn, string $username = null, string $password = null, array $options = []) {
		parent::__construct($dsn, $username, $password, $options);
		$this->setAttribute(PDO::ATTR_STATEMENT_CLASS, [PdoQueryCaptureStatement::class, [$this]]);
	}

	/**
	 * Executes an SQL statement, returning a result set as a PDOStatement object
	 *
	 * @param string $query      query to run
	 * @param int    $fetchMode  how the results will be returned
	 * @param mixed  $arg3       nobody knows...
	 * @param array  $ctorargs   Arguments of custom class constructor when the mode parameter is set to PDO::FETCH_CLASS
	 * @return void
	 */
	public function query($query, $fetchMode = PDO::FETCH_ASSOC, $arg3 = null, $ctorargs = [])
	{
		$start_time = microtime(true);
		if($arg3 === null) {
			$result = parent::query($query, $fetchMode);
		} else {
			$result = parent::query($query, $fetchMode, $arg3, $ctorargs);
		}
		$end_time = microtime(true);
		$execution_time = $end_time - $start_time;
		self::$query_data[uniqid("", true)] = [
			'query' => $query,
			'execution_time' => $execution_time,
		];
		return $result;
	}

	/**
	 * Execute an SQL statement and return the number of affected rows
	 *
	 * @param string $statement SQL Statement to run
	 * @return PdoQueryCaptureStatement|false
	 */
	public function exec($statement)
	{
		$start_time = microtime(true);
		$result = parent::exec($statement);
		$end_time = microtime(true);
		$execution_time = $end_time - $start_time;
		self::$query_data[uniqid("", true)] = [
			'query' => $statement,
			'execution_time' => $execution_time,
		];
		return $result;
	}

	/**
	 * Prepares a statement for execution and returns a statement object
	 *
	 * @param string $query   query
	 * @param array  $options This array holds one or more key=>value pairs to set attribute values for the PDOStatement object that this method returns. You would most commonly use this to set the PDO::ATTR_CURSOR value to PDO::CURSOR_SCROLL to request a scrollable cursor. Some drivers have driver specific options that may be set at prepare-time.
	 * @return PdoQueryCaptureStatement|false
	 */
	public function prepare($query, $options = [])
	{
		$start_time = microtime(true);
		$statement = parent::prepare($query, $options);
		$end_time = microtime(true);
		$execution_time = $end_time - $start_time;
		self::$query_data[$statement->unique_value] = [
			'query' => $query,
			'prepare_time' => $execution_time,
			'execution_time' => 0,
			'params' => []
		];
		return $statement;
	}

}
