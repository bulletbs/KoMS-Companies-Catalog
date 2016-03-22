<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Widgets_CatalogSearch extends Controller_System_Widgets {
    public $json = array();
    public $skip_auto_content_apply = array(
        'cities',
        'filters',
    );
    const REGION_LIST_CACHE = 'searchRegionCatalogRendered';
    const CITY_LIST_CACHE = 'searchCitiesCatalogRendered_';
    const CATEGORY_LIST_CACHE = 'searchCategoriesCatalogRendered';

    public $template = 'widgets/catalog_search_form';    // Шаблон виждета

    /**
     * Search form output
     */
    public function action_index()
    {
        $city_alias = Request::initial()->param('city_alias', FALSE);
        $cat_alias = Request::initial()->param('cat_alias', FALSE);
        if(!$cat_alias && !$city_alias){
            $form_action = Route::get('catalog')->uri();
        }
        else{
            $form_action = Route::get( $cat_alias ? 'catalog_cat' : 'catalog_city' )->uri(array(
                'city_alias' => $city_alias,
                'cat_alias' => $cat_alias,
            ));
        }

        /* CATEGORY NAME */
        $category_name = '';
        if($cat_alias){
            $category_id = Model_CatalogCategory::getCategoryIdByAlias($cat_alias);
            if($category_id)
                $category_name = Model_CatalogCategory::getField('name', $category_id);
        }

        /* REGION NAME */
        $region_name = '';
        if($city_alias){
            $region_id = Model_CatalogCity::getCityIdByAlias($city_alias);
            if($region_id)
                $region_name = Model_CatalogCity::getField('name', $region_id);
        }

        $this->template->set(array(
            'form_action' => $form_action,

            'region_name' =>  $region_name,
            'region_ailas' => $city_alias,
            'city_list' => $this->_regionListRender(),

            'category_name' => $category_name,
            'category_alias' => $cat_alias,
            'category_list' => $this->_categoryListRender(),

            'is_job_category' => isset($category_id) && in_array($category_id, Model_CatalogCategory::getJobIds()),
            'priced_category' => !(isset($category_id) && in_array($category_id, Model_CatalogCategory::getNopriceIds())),
        ));
    }

    /**
     * Get cities list
     */
    public function action_cities(){
        $region = (int) Request::current()->post('region_id');
        if(!$this->request->is_ajax() || !$region)
            return NULL;

        $region = ORM::factory('CatalogCity', $region);
        if($region->loaded() && NULL === ($this->json['content'] = Cache::instance()->get(self::CITY_LIST_CACHE . $region->id))){
            $cities = DB::select('id', 'alias', 'parent_id', 'name')->from(ORM::factory('CatalogCity')->table_name())->where('lvl','=',2)->where('parent_id','=',$region->id)->order_by('name')->as_assoc()->execute();
            $template = View::factory('widgets/_catalog_cities_search_list')->set(array(
                'cities' => $cities,
                'region' => $region,
            ));
            $this->json['content'] = $template->render();
            Cache::instance()->set(self::CITY_LIST_CACHE . $region->id, $this->json['content'], Date::DAY*365);
        }
        echo json_encode($this->json);
    }


    /**
     * Region list renderer
     * @return mixed|string
     */
    protected function _regionListRender(){
        if(NULL === ($content = Cache::instance()->get(self::REGION_LIST_CACHE))){
            $regions = DB::select('id', 'alias', 'name')->from(ORM::factory('CatalogCity')->table_name())->where('lvl','=',1)->order_by('name')->as_assoc()->execute();

            $template = View::factory('widgets/_catalog_region_search_list')->set(array(
                'regions' => $regions,
                'all_uri'  => URL::base() . Route::get('catalog_city')->uri(),
            ));
            $content = $template->render();
            Cache::instance()->set(self::REGION_LIST_CACHE, $content, Date::HOUR*24);
        }
        return $content;
    }

    /**
     * Categories lists renderer
     * @return mixed|string
     */
    protected function _categoryListRender(){
        if(NULL === ($content = Cache::instance()->get(self::CATEGORY_LIST_CACHE))){
            $result = DB::select('id', 'alias', 'name')->from(ORM::factory('CatalogCategory')->table_name())->where('lvl','=',1)->order_by('name')->as_assoc()->execute();
            $categories = array();
            foreach($result as $category){
                $category['link'] = URL::base() . Route::get('catalog_cat')->uri(array(
                    'cat_alias' => $category['alias'],
                ));
                $categories[] = $category;
            }
            $result = DB::select('id', 'alias', 'parent_id', 'name')->from(ORM::factory('CatalogCategory')->table_name())->where('lvl','=',2)->order_by('name')->as_assoc()->execute();
            $subcats = array();
            foreach($result as $res){
                $subcats[$res['parent_id']][] = $res;
            }
            $template = View::factory('widgets/_catalog_category_search_list')->set(array(
                'categories'  => $categories,
                'subcats'  => $subcats,
                'all_uri'  => Route::get('catalog_cat')->uri(),
            ));
            $content = $template->render();
            Cache::instance()->set(self::CATEGORY_LIST_CACHE, $content, Date::HOUR*24);
        }
        return $content;
    }
}