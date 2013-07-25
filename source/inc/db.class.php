<?php
include_once("config.inc.php");
//数据库封装类 萨瓦迪卡~
class DB extends PDO{	//此类扩展自PHP的PDO数据库操作类
	var $sth;	//用于储存PDOStatement对象
	
	function execute($sql, $values = array()) {	//执行SQL语句
		$this->sth = $this->prepare($sql);		//预执行SQL语句，可防止SQL注入
		return $this->sth->execute($values);	//执行SQL语句
	}
	
	function get_all($sql, $values = array()) {	//得到所有SELECT语句执行后的数据集
		
		$this->execute($sql, $values);			//执行本类的execute方法
		return $this->sth->fetchAll();				//取得所有结果集
	}
	
	function get_one($sql, $values = array()) {	//得到一条SELECT语句执行后的数据集
		$this->execute($sql, $values);			//执行本类的execute方法
		return $this->sth->fetch();				//取得一条结果集
	}
	
	function get_col($sql, $values = array(), $column_number = 0) {	//取得记录中的列值
		$this->execute($sql, $values);								//执行本类的execute方法
		return $this->sth->fetchColumn($column_number);				//取得结果中的某一列值
	}
	
	function insert($table, $data) {								//向数据表中插入数据
		$fields = array_keys($data);								//提取数据中的key值
		$marks = array_fill(0, count($fields), '?');				//组成数据

		//重新组合成插入的SQL语句
		$sql = "INSERT INTO $table (`" . implode('`,`',$fields) . "`) VALUES (" . implode(",",$marks).")";
		return $this->execute($sql, array_values($data));			//执行本类的execute方法，并返回结果
	}
	
	function update($table, $data, $where) {						//更新数据表中的数据
		$values = $bits = $wheres = array();						//建立数据
		foreach ( $data as $k=>$v ) {								//循环构建需要的数据参数
			$bits[] = "`$k` = ?";
			$values[] = $v;
		}
		if ( is_array( $where ) )
			foreach ( $where as $c => $v ) {						//循环构建需要的条件参数
				$wheres[] = "$c = ?";
				$values[] = $v;
			}
		else
			return false;
		//重新组合更新的SQL语句
		$sql = "UPDATE $table SET " . implode( ',', $bits ) . ' WHERE ' . implode( ' AND ',$wheres );
		return $this->execute($sql, $values);						//执行本类的execute方法，并返回结果
	}
	
	function delete($table, $field, $where) {						//从数据表中删除结果集
		if( empty($where) ) {										//条件是否为空
			return false;											//返回FALSE
		}
		//预执行删除语句
		$this->sth = $this->prepare("DELETE FROM $table WHERE $field = ?");
		
		if( is_array($where) ) {									//如果是数组
			foreach($where as $key=>$val) {							//循环需要删除的值
				$this->sth->execute(array($val));					//执行本类的execute方法
			}
		} else {													//只有一个数值
			$this->sth->execute(array($where));						//执行本类的execute方法
		}
	}
	
	function table2sql($table) {									//将数据表导出成SQL语句
		$sql = array();												//创建临时数组
		
		$sql[] = "DROP TABLE IF EXISTS `{$table}`;\n";				//如果存在则删除该数据表
		$create_table = $this->get_one('SHOW CREATE TABLE '.$table);//返回表结构的SQL语句
		$sql[] = $create_table[1].";\n\n";							//将如上语句存入临时数组中
		return implode('', $sql);									//以SQL形式返回所有的表信息
	}
	
	function data2sql($table) {										//将数据表数据导出成SQL语句
		$sql = array();												//创建临时数组
		$sql[] = "DROP TABLE IF EXIST `{$table}`;\n";				//如果存在则删除该数据表
		$create_table = $this->get_one('SHOW CREATE TABLE '.$table);//返回表结构的SQL语句
		$sql[] = $create_table[1].";\n\n";							//将如上语句存入临时数组中
		
		$rows = $this->get_all("SELECT * FROM $table");				//取得表中所有数据
		$col_count = $this->sth->columnCount();						//取得记录的个数
		
		foreach($rows as $row) {									//循环取得的数据
			$sql[] = "INSERT INTO $table VALUES(";					//创建insert语句
			$comma = '';
			
			for($i=0; $i< $col_count; $i++) {						//循环记录中的所有列
				if (!isset($row[$i])) {								//如果没有值
					$sql[] = $comma."NULL";							//设置为NULL
				} else {											//否则
					$sql[] = $comma."'".$row[$i]."'";				//设置为当前值
				}
				$comma = ',';										//更改连接符为“,”
			}
			$sql[] = ");\n";
		}
		$sql[] = "\n";
		return implode('',$sql);									//以SQL形式返回所有的数据信息
	}
	
	function dump_sql() {											//将数据库中的所有数据表导出成SQL形式字符串
		$sql = array();												//创建临时数组
		foreach ($this->query('SHOW TABLES') as $row) {				//循环所有表
			$sql[] = $this->data2sql($row[0]);						//调用data2sql导出数据表的SQL
		}
		return implode('',$sql);									//返回集合后的SQL数据
	}
}

?>