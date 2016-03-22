<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Created by JetBrains PhpStorm.
 * User: butch
 * Date: 09.02.14
 * Time: 19:19
 */

class Model_CatalogCategory extends Model_BoardCategory{


    /**
     * Get category uri
     * @param null $city_alias
     * @return string
     * @throws Kohana_Exception
     */
    public function getUri($city_alias = NULL){
        if(is_null($this->_uriToMe)){
            $this->_uriToMe = Route::get('catalog_cat')->uri(array(
                'cat_alias' => $this->alias,
                'city_alias' => $city_alias,
            ));
        }
        return $this->_uriToMe;
    }

    /**
     * Generate runtime URI
     * @param $alias
     * @return string
     * @throws Kohana_Exception
     */
    public static function generateUri($alias){
        $uri = Route::get('catalog_cat')->uri(array(
            'cat_alias' => $alias,
        ));
        return $uri;
    }
}