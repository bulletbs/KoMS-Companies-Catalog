<?php defined('SYSPATH') or die('No direct script access.');?>

<h1>Объявления магазина <?php echo $company->name?></h1>

<?if(count($categories)):?>
<ul class="search_sub_col"><?$_steps = ceil(count($categories)/5); $_step = 0;?>
    <?foreach($categories as $_cat_id=>$_cnt):?><?if($_step>0 && $_step%$_steps==0):?></ul><ul class="search_sub_col"><?endif?>
    <li><?php echo HTML::anchor($company->adsUri($_cat_id), Model_CatalogCategory::getField('name', $_cat_id) . ' <span>' .$_cnt. '</span>') ?></li>
    <?$_step++?><?endforeach?>
</ul>
<div class="line"></div>
<?endif?>

<div class="col_main">
<?if(count($ads)):?>
    <table class="tableccat" id="adList">
        <?foreach($ads as $ad):?>
            <tr><td class="dashed">
                    <table>
                        <tr>
                            <td class="list_img"><?= HTML::anchor( $ad->getUri(), HTML::image(isset($photos[$ad->id]) ? $photos[$ad->id]->getThumbUri() : "/assets/board/css/images/noimage.png", array('alt'=>htmlspecialchars($ad->getTitle()), 'title'=>htmlspecialchars($ad->getTitle()))) . ($ad->photo_count ? '<span title="Всего фотографий: '.$ad->photo_count.'">'.$ad->photo_count.'</span>' : ''))?></td>
                            <td class="list_fav"><a href="#" class="ico_favorite" data-item="<?=$ad->id?>" title="Добавить в избранное"></a></td>
                            <td class="list_title">
                                <h3><?php echo HTML::anchor($ad->getUri(), $ad->getTitle(), array('title'=> $ad->getTitle()))?></h3>
                                <div class="list_price"><?= $ad->getPrice( BoardConfig::instance()->priceTemplate($ad->price_unit) ) ?></div><br>
                                <span class="quiet"><?php echo Model_BoardCategory::getField('name', $ad->category_id)?><br><b><?php echo Model_BoardCity::getField('name', $ad->city_id)?></b><br><?= Date::smart_date($ad->addtime)?> <?= date('G:i', $ad->addtime) ?> </span>
                            </td>
                        </tr>
                    </table>
                </td></tr>
        <?endforeach;?>
    </table>
    <div class="clear"></div>
    <?php echo $pagination->render()?>
<?else:?>
    <b>К сожалению не удалось найти объявления по указаным критериям</b><br /><br />
    <?php echo Widget::factory('Banner728x90')?>
<?endif?>
<?= $pagination->render()?>
</div>
<div class="col_tools"><?php echo Widget::factory('catalogMenu')?></div>