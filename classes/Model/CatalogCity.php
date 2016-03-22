<?php defined('SYSPATH') OR die('No direct script access.');

class Model_CatalogCity extends Model_BoardCity{

    public function getUri(){
        if(is_null($this->_uriToMe)){
            $this->_uriToMe = Route::get('catalog_city')->uri(array(
                'city_alias' => $this->alias,
            ));
        }
        return $this->_uriToMe;
    }

    public static function generateUri($alias, $cat_alias=NULL){
        $cat = !empty($cat_alias) ? $cat_alias : Request::initial()->param('cat_alias');
        $uri = Route::get($cat ? 'catalog_cat' : 'catalog_city')->uri(array(
            'cat_alias' => $cat,
            'city_alias' => $alias,
        ));
        return $uri;
    }
}