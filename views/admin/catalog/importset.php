<?php defined('SYSPATH') or die('No direct script access.');?>

<?php echo Form::hidden('id', $id) ?>
<div class="well well-small">
    <label><?php echo  Form::checkbox('import', 1, $import>0) ?> импорт разрешен</label>
    <div class="form-group">
        <?php echo  Form::label('maxads', "Лимит объявлений", array('class'=>'control-label')) ?>
        <?php echo  Form::input('maxads', $maxads, array('class'=>'form-control input_short')) ?>
    </div>
</div>