<?
AddEventHandler('main', 'OnBuildGlobalMenu', Array("EventHandler", "OnBuildGlobalMenu"));


class EventHandler
{

	function OnBuildGlobalMenu(&$aGlobalMenu, &$aModuleMenu)
	{
		global $USER;
		if ($USER->IsAdmin() or !substr_count($USER->GetGroups(), CONTENT_ED_GID))
			return;

		foreach ($aGlobalMenu as $key => $item)
			if ($key != 'global_menu_content')
				unset($aGlobalMenu[$key]);

		foreach ($aModuleMenu as $key => $item)
			if ($item['parent_menu'] != 'global_menu_content')
				unset($aModuleMenu[$key]);

		foreach ($aModuleMenu as $key => $item)
			if ($item['items_id'] != 'menu_iblock_/news')
				unset($aModuleMenu[$key]);

	}
}