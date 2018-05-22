<?php
return [
    'table'         => 'xxs_category',
    'primaryKey'    => 'id',
    'autoIncrement' => 'id',
    'columns' => [
		'id' => 'int',
		'category' => 'string',
		'status' => 'int',
		'created_at' => 'int',
		'updated_at' => 'int',
		'type' => 'int',
		'price' => 'float',
	]
];