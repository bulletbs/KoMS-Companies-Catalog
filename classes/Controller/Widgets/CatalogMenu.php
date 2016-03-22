<?php defined('SYSPATH') or die('No direct script access.');
/*
 * Виджет "Меню админа"
 */
class Controller_Widgets_CatalogMenu extends Controller_System_Widgets {

    public $template = 'widgets/catalog_menu';    // Шаблон виждета

    public function action_index()
    {
        $categories = Model_CatalogCategory::getTwoLevelArray();
        $active_id = Model_CatalogCategory::getAliases((string) Request::initial()->param('cat_alias'));
        $this->template->set(array(
            'categories' => $categories,
            'active_id' => $active_id,
        ));
    }

}