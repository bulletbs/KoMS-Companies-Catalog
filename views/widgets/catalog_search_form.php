<?php defined('SYSPATH') or die('No direct script access.');?>
<!--noindex-->
<div class="catalog_search_form">
<?php echo Form::open($form_action, array('id'=>'boardTopForm', 'method'=>'get'))?>
    <div id="queryInput" class="selector">
        <?php echo Form::input('query', Arr::get($_GET, 'query'), array('placeholder'=>'Найти магазин', 'class'=>'query', 'id'=>'serchformQuery'))?>
    </div>
    <div id="regionLabel" class="selector">
        <?php echo Form::input(NULL, $region_name, array('placeholder'=>'Область', 'readonly'=>'readonly', 'id'=>'regionTopInput'))?>
        <?php echo $city_list?>
    </div>
    <div id="categoryLabel" class="selector selector-last">
        <?php echo Form::input(NULL, $category_name, array('placeholder'=>'Категория', 'readonly'=>'readonly', 'id'=>'categoryTopInput'))?>
        <?php echo $category_list?>
    </div>
    <?php echo Form::submit(NULL, 'Найти', array('id'=>'boardTopSubmit', 'class'=>'boardSubmit'))?>
    <div class="clear"></div>
    <?php echo Form::hidden(NULL, $category_alias, array('id'=>'categoryAlias'))?>
    <?php echo Form::hidden(NULL, $region_ailas, array('id'=>'regionAlias'))?>
    <div class="clear"></div>
<?php echo Form::close()?>
</div>
<!--/noindex-->