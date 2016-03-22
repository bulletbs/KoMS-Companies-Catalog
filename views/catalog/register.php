<?php defined('SYSPATH') or die('No direct script access.');?>
<?if(!Auth::instance()->logged_in('login')):?>
<ul class="uk-tab uk-margin-top  uk-text-small">
    <li><a href="/register"><?php echo __('Register as user')?></a></li>
    <li class="uk-active"><a href="<?php echo Route::get('catalog_user')->uri(array('action'=>'register')) ?>"><?php echo __('Register as company')?></a></li>
</ul>
<?else:?>
<h1><?php echo __('Register as company')?></h1>
<?endif?>

<?if(count($errors)):?><?= View::factory('error/validation', array('errors' => $errors))->render()?><?endif;?>
<?=Form::open(Route::get('catalog_user')->uri(array('action'=>'register')), array('class' => 'pure-form pure-form-stacked'))?>
<?if(!Auth::instance()->logged_in('login')):?>
<fieldset>
    <legend><?php echo __('User information')?></legend>
    <?=Form::label('name', __('Enter your name'));  ?>
    <?=Form::input('name', $data['name'], array('class' => ''));  ?>
    <?=Form::label('username', __('Enter your email'));  ?>
    <?=Form::input('email', $data['email'], array('class' => ''));  ?>
    <?=Form::label('username', __('Password'));  ?>
    <?=Form::password('password', null, array('class' => ''));  ?>
    <?=Form::label('username', __('Password confirm'));  ?>
    <?=Form::password('password_confirm', null, array('class' => ''));  ?>
</fieldset>
<?endif?>
<fieldset>
    <legend><?php echo __('Company information')?></legend>

    <?=Form::label('company[name]', __('Company Name'));  ?>
    <?=Form::input('company[name]', $company->name, array('class' => ''));  ?>
    <?=Form::label('company[desc]', __('Company description'));  ?>
    <?=Form::textarea('company[desc]', $company->desc, array('class' => ''));  ?>
    <legend><?php echo  __('Company contacts')?></legend>
    <div class="pure-g">
        <div class="pure-u-1-2">
            <?=Form::label('company[city]', __('City'));  ?>
            <?=Form::hidden('company[city_id]', $company->city_id, array('id'=>'city_id'));  ?>
            <?= Form::select('region', $regions, Arr::get($_POST,'region', $region), array('class'=>isset($errors['city_id']) ? 'error-input': '', 'id'=>'region'))?>
            <span id="subRegion"><?php echo !empty($cities) ? '&nbsp;&nbsp;<b>&raquo;</b>&nbsp;&nbsp;'.$cities : '' ?></span>

            <?=Form::label('company[address]', __('Address'));  ?>
            <?=Form::input('company[address]', $company->address, array('class' => ''));  ?>
            <?=Form::label('company[postcode]', __('Postcode'));  ?>
            <?=Form::input('company[postcode]', $company->postcode, array('class' => ''));  ?>
            <?=Form::label('company[email]', __('Email'));  ?>
            <?=Form::input('company[email]', $company->email, array('class' => ''));  ?>
        </div>
        <div class="pure-u-1-2">
            <?=Form::label('company[telephone]', __('Phone'));  ?>
            <?=Form::input('company[telephone]', $company->telephone, array('class' => ''));  ?>
            <?=Form::label('company[fax]', __('Fax'));  ?>
            <?=Form::input('company[fax]', $company->fax, array('class' => ''));  ?>
            <?=Form::label('company[website]', __('Website'));  ?>
            <?=Form::input('company[website]', $company->website, array('class' => ''));  ?>
        </div>
    </div>

    <legend><?php echo  __('Company images')?></legend>
    <?php echo Form::file('logo', array('class'=>'form-control')) ?>
</fieldset>
<?if(!Auth::instance()->logged_in('login')):?>
    <Br><?php echo Captcha::instance() ?>
    <?= Form::label('captcha', __('Enter captcha code')) ?>
    <?php echo Form::input('captcha', NULL, array('id' => 'captcha-key'))?>
    <br>
<?endif?>
    <?=Form::submit('register', __('Register'), array('type'=>'submit', 'class' => 'pure-button pure-button-primary'));  ?>
<?=Form::close()?>
