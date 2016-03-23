<?php defined('SYSPATH') or die('No direct script access.');?>

<div class="catalog_menu">
    <h3>Категории</h3>
    <ul id="catalogMenu">
    <?foreach($categories as $_part=>$_subcategories):?>
    <li><a href="#" class="partlink"><?php echo $_part?></a>
        <ul style="display: none;"><?foreach($_subcategories as $_cat_id=>$_cat):?>
        <li<?if($active_id == $_cat_id):?> class="active"<?endif?>>
            <?php echo HTML::anchor(Model_CatalogCategory::generateUri( Model_CatalogCategory::getField('alias', $_cat_id) ), $_cat)?>
<!--            --><?php //echo  $_cat_id ?>
        </li><?endforeach?>
        </ul>
    </li><?endforeach?>
    </ul>
</div>
<?if(!Auth::instance()->logged_in('company')):?>
<br>
<div class="catalog_menu">
    <a href="/shops/register" class="pure-button center">Зарегистрировать магазин</a>
</div>
<?endif?>