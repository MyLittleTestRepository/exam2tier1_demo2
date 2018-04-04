<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
	die();

/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponent $this */
/** @var array $arParams */

/** @var array $arResult */

use Bitrix\Main\Loader;

if (!Loader::includeModule('iblock'))
{
	ShowError(GetMessage('SIMPLECOMP_EXAM2_IBLOCK_MODULE_NONE'));
	return;
}

if (!$USER->IsAuthorized())
	return;

$my_id = intval($USER->GetID());

if ($this->startResultCache(false, $my_id))
{
	//фильтруем входные параметры
	$arParams['UF_PROP_CODE'] = trim($arParams['UF_PROP_CODE']);
	$arParams['NEWS_LINK_CODE'] = trim($arParams['NEWS_LINK_CODE']);
	$arParams['NEWS_IBLOCK_ID'] = intval($arParams['NEWS_IBLOCK_ID']);

	//проверяем
	if (empty($arParams['UF_PROP_CODE']) or empty($arParams['NEWS_LINK_CODE']) or !$arParams['NEWS_IBLOCK_ID'])
	{
		$this->abortResultCache();
		return;
	}

	//получаем наш тип
	$Res = CUser::GetList($by,
	                      $order,
	                      ['ID' => $my_id],
	                      ['SELECT' => [$arParams['UF_PROP_CODE']],
	                       'FIELDS' => ['ID']]);

	if (!$Res->SelectedRowsCount()) //мы не являемся автором
	{
		$this->abortResultCache();
		return;
	}

	$user = $Res->Fetch();

	//найдем всех авторов нашего типа
	$Res = CUser::GetList($by,
	                      $order,
	                      ['UF_AUTHOR_TYPE' => $user[$arParams['UF_PROP_CODE']],
	                       'ACTIVE'         => 'Y'],
	                      ['FIELDS' => ['ID', 'LOGIN']]);

	if ($Res->SelectedRowsCount() < 2) //других авторов нет
	{
		$this->abortResultCache();
		return;
	}

	while ($user = $Res->Fetch())
		$arResult['USERS'][$user['ID']]['LOGIN'] = $user['LOGIN'];

	$uids = array_keys($arResult['USERS']);

	//найдем новости всех авторов нашего типа
	$Res = CIBlockElement::GetList(false,
	                               ['IBLOCK_ID' => $arParams['NEWS_IBLOCK_ID'],
	                                'PROPERTY_' . $arParams['NEWS_LINK_CODE'] => $uids,
	                                'ACTIVE' => 'Y'],
	                               false,
	                               false,
	                               ['NAME', 'DATE_ACTIVE_FROM', 'ID', 'PROPERTY_' . $arParams['NEWS_LINK_CODE']]);

	if (!$Res->SelectedRowsCount()) //новостей нет
	{
		$this->abortResultCache();
		return;
	}

	$my_news_ids = [];//список своих новостей

	while ($item = $Res->Fetch()) //правильно решать через GetNextElement(), но Fetch() быстрее
	{
		//автор новости
		$uid = $item['PROPERTY_' . $arParams['NEWS_LINK_CODE'] . '_VALUE'];
		//айди новости
		$id = intval($item['ID']);

		if ($uid == $my_id) //собираем айдишники своих новостей
			$my_news_ids[] = $id;

		//пропускаем свои новости
		if (in_array($id, $my_news_ids))
		{
			unset($arResult['NEWS'][$id]); //и зачищаем выдачу
			continue;
		}

		//избавляемся от дублей выдачи (GetNextElement()->GetProperties() не выдает дублей, но медленный)
		if (empty($arResult['NEWS'][$id]))
		{
			$arResult['NEWS'][$id]['NAME'] = $item['NAME'];
			$arResult['NEWS'][$id]['DATE'] = $item['DATE_ACTIVE_FROM'];

		}

		//автору добавляем ссылку на его новость
		$arResult['USERS'][$uid]['NEWS_ID'][] = $id;
	}

	//не забываем зачистить себя
	unset($arResult['USERS'][$my_id]);

	//сохраняем количество новостей
	$arResult['COUNT'] = count($arResult['NEWS']);
	$this->setResultCacheKeys('COUNT');

	//получаем тип инфоблока для контектного меню
	$arResult['IBLOCK_TYPE']=CIBlock::GetByID($arParams['NEWS_IBLOCK_ID'])->Fetch()['IBLOCK_TYPE_ID'];
	$this->setResultCacheKeys('IBLOCK_TYPE');

	$this->includeComponentTemplate();
}

//пункт в контекстном меню
if ($APPLICATION->GetShowIncludeAreas())
{
	$this->AddIncludeAreaIcons([["TITLE"          => GetMessage("IB_MENU"),
	                             "URL"            => '/bitrix/admin/iblock_element_admin.php?IBLOCK_ID='.$arParams['NEWS_IBLOCK_ID'].'&type='.$arResult['IBLOCK_TYPE'],
	                             "IN_PARAMS_MENU" => true,
	                             "IN_MENU"        => false,]]);
}

//заголовок
$APPLICATION->SetTitle(GetMessage("NEWS_COUNT") . $arResult['COUNT']);