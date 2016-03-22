<?php defined('SYSPATH') or die('No direct script access');
return array(
    /* User settings */
    'user_submodels' => array(
        'user_company' => array(
            'model' => 'CatalogCompany',
            'foreign' => 'user_id',
        ),
    ),
);
