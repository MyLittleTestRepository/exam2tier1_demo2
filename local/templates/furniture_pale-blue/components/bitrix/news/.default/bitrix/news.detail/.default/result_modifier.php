<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

if (!empty($arParams['CANONICAL_ID']))
	$arParams['CANONICAL_ID'] = intval($arParams['CANONICAL_ID']);

if ($arParams['CANONICAL_ID'] > 0 and CModule::IncludeModule('iblock'))
{
		$Res = CIBlockElement::GetList(false,
		                               ['IBLOCK_ID' => $arParams['CANONICAL_ID'], 'PROPERTY_NEWS' => $arResult['ID']],
		                               false,
		                               false,
		                               ['NAME']);

		if ($Res->SelectedRowsCount())
			$arResult['CANONICAL'] = '<link rel="canonical" href="' . $Res->Fetch()['NAME'] . '">';

		$this->__component->setResultCacheKeys('CANONICAL');
}