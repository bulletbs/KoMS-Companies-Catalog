<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Created by JetBrains PhpStorm.
 * User: butch
 * Date: 23.05.12
 * Time: 18:35
 * To change this template use File | Settings | File Templates.
 */
class Controller_Admin_CatalogModerate extends Controller_Admin_Moderate
{
    protected $submenu = 'AdminCatalogMenu';
    protected $_crud_uri = 'admin/catalog';

    public $model_name = 'CatalogCompany';
    public $moderate_field = 'moderate';

    protected $_item_name = 'компания';
    protected $_moderate_name = 'Проверка компаний';

    public $list_fields = array(
        'nameLink',
    );


    /**
     * Check all not moderated comments as moderated
     * @return int
     */
    protected function _setAllModerated(){
        return DB::update(ORM::factory($this->model_name)->table_name())->set(array($this->moderate_field=>1))->where($this->moderate_field, '=', self::NOT_MODERATED)->execute();
    }

    /**
     * Check all selected
     * @param array $ids
     * @return object
     */
    protected function _setModerated(Array $ids){
        return DB::update(ORM::factory($this->model_name)->table_name())->set(array($this->moderate_field=>1))->where($this->moderate_field, '=', self::NOT_MODERATED)->and_where('id','IN',$ids)->execute();
    }
}
