<?php defined('SYSPATH') or die('No direct script access.');

if(!Route::cache()){
    Route::set('catalog_main', 'shops(/p<page>.html)', array('page' => '[0-9]+'))
        ->defaults(array(
            'controller' => 'catalog',
            'action' => 'search',
        ));
    Route::set('catalog', 'shops(/<action>)(/<id>)(/p<page>.html)', array('action' => '(goto|send_message)', 'id' => '[0-9]+', 'page' => '[0-9]+'))
        ->defaults(array(
            'controller' => 'catalog',
            'action' => 'search',
        ));
    Route::set('catalog_user', 'shops(/<action>)', array('action' => '(register|registration_done|company_created)'))
        ->defaults(array(
            'controller' => 'userCompany',
        ));

    Route::set('catalog_company', 'shops/<id>-<alias>.html', array('id' => '[0-9]+', 'alias' => '[\d\w\-_]+'))
        ->defaults(array(
            'controller' => 'catalog',
            'action' => 'company',
        ));

    Route::set('catalog_city', 'shops/<city_alias>(/p<page>).html', array('city_alias' => '[\w\-_]+', 'cat_alias' => '[\d\w\-_]+', 'page' => '[0-9]+'))
        ->defaults(array(
            'controller' => 'catalog',
            'action' => 'search',
            'city_alias' => 'all',
        ));

    Route::set('catalog_cat', 'shops/<city_alias>(/<cat_alias>)(/p<page>).html', array('city_alias' => '[\w\-_]+', 'cat_alias' => '[\d\w\-_]+', 'page' => '[0-9]+'))
        ->defaults(array(
            'controller' => 'catalog',
            'action' => 'search',
            'city_alias' => 'all',
        ));

    // hidden profile actions: |enable|remove
    Route::set('catalog_mycompany', 'profile/shop(/<action>(/<id>)(/p<page>.html))', array('action' => '(edit)', 'id' => '[0-9]+', 'page' => '[0-9]+'))
        ->defaults(array(
            'controller' => 'userCompany',
            'action' => 'edit',
        ));

    Route::set('catalog_ads', 'products-<id>-<alias>(/<cat_alias>)(/p<page>).html', array('id' => '[0-9]+', 'page' => '[0-9]+'))
        ->defaults(array(
            'controller' => 'catalog',
            'action' => 'ads',
        ));
    Route::set('catalog_search_widget', 'shops/catalogSearch/<action>(/<id>)')
        ->defaults(array(
            'directory' => 'widgets',
            'controller' => 'CatalogSearch',
            'action' => 'cities',
        ));
}