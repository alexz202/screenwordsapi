<?php

return array(
	'A1000' =>
	array(
		'name' => 'checkscreenword',
		'params' => array(
			array(
				'field' => 'sUsername',
				'name' => 'sUsername',
				'required' => true,
				'datatype' => VALIDATE_DATATYPE_STRING
			),
		),
		'desc' => '检查屏蔽词',
		'actionType' => 'GET',
	),
	// response
	// succeed:
	// { iRet:0, sRetMsg: "ok",  list:[] }
	// failed:
	// { iRet:1, sRetMsg: "error",  list:[] }
	// jszhang
	'A1111' =>
		array(
			'name' => 'addscreenword',
			'params' => array(
				array(
					'field' => 'word',
					'name' => 'word',
					'required' => true,
					'datatype' => VALIDATE_DATATYPE_STRING
				),
			),
			'desc' => '添加屏蔽词',
			'actionType' => 'GET',
		),
);
?>
