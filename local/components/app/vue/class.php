<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use \Bitrix\Main;
use \Bitrix\Main\Localization\Loc as Loc;
use \Bitrix\Main\Data\Cache as Cache;
use \Bitrix\Main\Application as Application;

class CTaskList extends CBitrixComponent
{

    private $arSelectedFields;

    /**
     * Подключение языкового файла
     */
    public function onIncludeComponentLang()
    {
        Loc::loadMessages(__FILE__);
    }

    /**
     * Подготовка параметров компонента.
     *
     * Подготавливаем параметры компонента, устаналиваем значения по умолчнию, если не заданы.
     *
     * @param array $params Массив параметров компонента
     *
     * @return array
     */
    public function onPrepareComponentParams(array $params)
    {

        //Если не задан устанавливаем отбираемые по умолчанию поля
        if (!is_array($params['FIELDS'])) {
            $params['FIELDS'] = ['ID', 'TITLE', 'DATE_CREATE', 'DEADLINE', 'STATUS', 'MARK', 'CREATED_BY', 'RESPONSIBLE_ID', 'GROUP_ID', 'PRIORITY'];
        }

        //Если не задан - по умолчанию пустой массив пользовательских полей
        if (!is_array($params['TASK_FIELDS'])) {
            $params['LEAD_FIELDS'] = [];
        }

        //Список выбираемых полей
        $this->arSelectedFields = [
            'FIELDS' => $params['FIELDS'],
            'SELECT' => $params['TASK_FIELDS']
        ];

        return $result;
    }

    /**
     * Получить список
     *
     * @return array
     */
    private function getTaskList(){
        //Выборка списка
        $dbRes = CTasks::GetList(
                $arOrder = array(),  
                $arFilter = array(),  
                $arSelect = array('ID', 'TITLE', 'DEADLINE', 'STATUS', 'MARK', 'PRIORITY'),
				$arParams = array()
);

        //Для хранения
        $arTasks = [];

        //Выборка элементов
        while ($arTemp = $dbRes->Fetch()) {
            $arTasks[] = $arTemp;
        }

        return $arTasks;
    }

    /**
     * Получить описания полей указанных в параметрах
     *
     * @param array $arTasks Массив полей, полученные из выборки
     *
     * @return array Массив символьных кодов полей и их описания
     */
    private function getSelectedFieldsNames(array $arTasks)
    {
        //Если не массив, либо в массив пуст (нет ни одного)
        if (!is_array($arTasks) && !is_array($arTasks[0])) {
            return [];
        }

        //Получаем список символьных кодов отобранных полей
        $arFieldsCodes = array_keys($arTasks[0]);
        $arFieldsNames = [];

        //Получаем все пользовательские поля (UF_*) пользователей для соотвествующего языка публичной части сайта
        global $USER_FIELD_MANAGER;
        $arUserFields = $USER_FIELD_MANAGER->GetUserFields('main', 0, LANGUAGE_ID);

        //Ищем соответствующее полю описание
        foreach ($arFieldsCodes as $field) {
            //Для типовых полей описание поля находится в языковом файле. Состав типовых полей не изменяется
            $arFieldsNames[$field] = Loc::getMessage($field);

            //Если поле не найдено в типовых, то оно задано пользователем при создании поля
            if (null === $arFieldsNames[$field]) {
                $arFieldsNames[$field] = $arUserFields[$field]['EDIT_FORM_LABEL'];
            }
        }

        return $arFieldsNames;
    }

    /**
     * Выполнить логику компонента
     */
    public function executeComponent()
    {

            //Получить список
            $this->arResult['DATA'] = $this->getTaskList();

            //Получить выбранные заголовки
            $this->arResult['HEADERS'] = $this->getSelectedFieldsNames($this->arResult['DATA']);

        $this->includeComponentTemplate();
    }

}
