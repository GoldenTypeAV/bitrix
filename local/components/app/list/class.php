<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use \Bitrix\Main;
use \Bitrix\Main\Localization\Loc as Loc;
use \Bitrix\Main\Data\Cache as Cache;
use \Bitrix\Main\Application as Application;

class CLeadList extends CBitrixComponent
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
            $params['FIELDS'] = ['TITLE', 'DATE_CREATE', 'SOURCE_ID', 'ASSIGNED_BY', 'STATUS_ID'];
        }

        //Если не задан - по умолчанию пустой массив пользовательских полей
        if (!is_array($params['LEAD_FIELDS'])) {
            $params['LEAD_FIELDS'] = [];
        }

        //Список выбираемых полей
        $this->arSelectedFields = [
            'FIELDS' => $params['FIELDS'],
            'SELECT' => $params['LEAD_FIELDS']
        ];

        return $result;
    }

    /**
     * Получить список
     *
     * @return array
     */
    private function getLeadList(){
        //Выборка списка
        $dbRes = CCrmLead::GetListEx(
                $arOrder = array(),  
                $arFilter = array(),  
                $arGroupBy = false,  
                $arNavStartParams = false,  
                $arSelectFields = array('ID', 'TITLE', 'DATE_CREATE', 'SOURCE_ID', 'ASSIGNED_BY_LOGIN', 'STATUS_ID')
);

        //Для хранения
        $arLeads = [];

        //Выборка элементов
        while ($arTemp = $dbRes->Fetch()) {
            $arLeads[] = $arTemp;
        }

        return $arLeads;
    }

    /**
     * Получить описания полей указанных в параметрах
     *
     * @param array $arLeads Массив полей, полученные из выборки
     *
     * @return array Массив символьных кодов полей и их описания
     */
    private function getSelectedFieldsNames(array $arLeads)
    {
        //Если не массив, либо в массив пуст (нет ни одного)
        if (!is_array($arLeads) && !is_array($arLeads[0])) {
            return [];
        }

        //Получаем список символьных кодов отобранных полей
        $arFieldsCodes = array_keys($arLeads[0]);
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
            $this->arResult['LEADS'] = $this->getLeadList();

            //Получить выбранные заголовки
            $this->arResult['FIELD_NAMES'] = $this->getSelectedFieldsNames($this->arResult['LEADS']);

        $this->includeComponentTemplate();
    }

}
