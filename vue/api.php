<?php
use \Bitrix\Main;
require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_before.php");
include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include.php");

header('Content-type: application/json');
global $APPLICATION;

if($_POST !== null) {

	$data = [];
	$exploded = explode(',', file_get_contents('php://input'));
	$exploded = str_replace('"','',$exploded);
	$exploded = str_replace(['{','}'],'',$exploded);
	
	foreach($exploded as $row) {
		$keyvalue = explode(':', $row);
		$data[$keyvalue[0]] = $keyvalue[1];
	
	}

	if(CModule::IncludeModule('tasks')) {
		$ID = $data['id'];
		$arFields = array(
			"PRIORITY" => $data['priority']
		);
		$obTask = new CTasks;
		$success = $obTask->Update($ID, $arFields);
        $dbRes = CTasks::GetList(
                $arOrder = array(),  
                $arFilter = array(),  
                $arSelect = array('ID', 'TITLE', 'DEADLINE', 'STATUS', 'MARK', 'PRIORITY'),
				$arParams = array()
		);

        $arTasks = [];

        while ($arTemp = $dbRes->Fetch()) {
            $arTasks[] = $arTemp;
        }

		if($success) echo json_encode($arTasks);
		else {
			if($e = $APPLICATION->GetException())
				echo "Error: ". $e->GetString();
		}
	}
	else echo 'Module not included';
}
else { return false; die; }

?>
