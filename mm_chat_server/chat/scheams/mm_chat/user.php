<?php
return [
    'table'         => 'user',
    'primaryKey'    => 'id',
    'autoIncrement' => 'id',
    'columns' => [
		'id' => 'int',
		'user_id' => 'int',
		'nickname' => 'string',
		'sex' => 'int',
		'email' => 'string',
		'password' => 'string',
		'address' => 'string',
		'tags' => 'string',
		'remark' => 'string',
		'birthday' => 'int',
		'create_time' => 'int',
		'update_time' => 'int',
	]
];