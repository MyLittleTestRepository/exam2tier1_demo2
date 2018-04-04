<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if(empty($arResult))
    return?>
<p><b><?=GetMessage("SIMPLECOMP_EXAM2_CAT_TITLE")?></b></p>

<ul>
<?foreach ($arResult['USERS'] as $uid=>$item):?>
    <li>[<?=$uid?>] - <?=$item['LOGIN']?>
    <ul>
        <?foreach ($item['NEWS_ID'] as $news_id):?>
            <li><?=$arResult['NEWS'][$news_id]['DATE']?> - <?=$arResult['NEWS'][$news_id]['NAME']?></li>
        <?endforeach;?>
    </ul>
    </li>
<?endforeach;?>
</ul>