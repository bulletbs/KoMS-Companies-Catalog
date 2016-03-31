<?php defined('SYSPATH') or die('No direct script access.');
/**
 *
 */

class Controller_UserCompany extends Controller_User
{
    public $auth_required = 'login';
    public $secured_actions = array(
        'register' => NULL,
        'registration_done' => NULL,
        'edit' => 'company',
    );

    public $cfg;

    public $skip_auto_content_apply = array(
        'register',
        'registration_done',
        'company_created',
        'enable',
        'remove',
    );

    public function before(){
        /* Путь к шаблону */
        $this->uri = 'catalog/cabinet/'. $this->request->action();
        $this->cfg = Kohana::$config->load('catalog')->as_array();
        parent::before();
    }


    /**
     * Companies list action
     */
//    public function action_list(){
//        $companies = ORM::factory('CatalogCompany')->where('user_id', '=', $this->current_user->id)->find_all();
//        $this->user_content->set(array(
//            'companies' => $companies,
//        ));
//    }

    /**
     * Company add & edit action
     */
    public function action_edit(){
        if(!Auth::instance()->logged_in('company')){
            $this->redirect(Route::get('catalog_user')->uri(array('action' => 'register')));
        }

        $errors = array();
        $id = $this->request->param('id');

        $this->breadcrumbs->add(__('My companies'), URL::site().Route::get('catalog_mycompany')->uri());

//        $model = ORM::factory('CatalogCompany')->where('id', '=', $id)->and_where('user_id', '=', $this->current_user->id)->find();
        $model = ORM::factory('CatalogCompany')->where('user_id', '=', $this->current_user->id)->find();
        if(!$model->loaded())
            $this->redirect( URL::site() . Route::get('catalog_user')->uri(array('action'=>'register')) );
        $photos = $model->photos->find_all();

        if(HTTP_Request::POST == $this->request->method()){
            if(Arr::get($_POST, 'cancel'))
                $this->redirect(URL::site().Route::get('catalog_mycompany')->uri());
            $model->values($_POST);
            $model->user_id = $this->current_user->id;
            try{
                $model->save();

                /* Delete photos */
                $dellogo = Arr::get($_POST, 'del_logo', false);
                if($dellogo){
                    $photos = $model->photos->find_all();
                    foreach($photos as $_photo)
                        $_photo->delete();
                }

                /* Save photos */
                $logo = Arr::get($_FILES, 'logo', false);
                if($logo && is_file($logo['tmp_name']) && Image::isImage($logo['tmp_name'])){
                    if(!$dellogo){
                        $photos = $model->photos->find_all();
                        foreach($photos as $_photo)
                            $_photo->delete();
                    }
                    $model->addPhoto($logo['tmp_name']);
                }
//                $files = Arr::get($_FILES, 'photos', array('tmp_name' => array()));
//                if(count($files['tmp_name'])){
//                    foreach ($files['tmp_name'] as $k => $file) {
//                        $model->addPhoto($file);
//                    }
//                }
//
//                /* Deleting photos */
//                $files = Arr::get($_POST, 'delphotos', array());
//                foreach ($files as $file_id)
//                    $model->deletePhoto($file_id);
//
//                /* Setting up main photo */
//                $setmain = Arr::get($_POST, 'setmain');
//                $model->setMainPhoto($setmain);

                Flash::success(__('Your company successfully saved'));
                $this->redirect(URL::site().Route::get('catalog_mycompany')->uri());
            }
            catch(ORM_Validation_Exception $e){
                $errors = $e->errors('validation');
            }
        }


        /* Регионы и города */
        $regions = array(''=>"Выберите регион");
        $regions += ORM::factory('BoardCity')->where('parent_id', '=', 0)->cached(Model_BoardCity::CITIES_CACHE_TIME)->find_all()->as_array('id','name');
        $cities = '';
        $region = NULL;
        if($model->city_id > 0){
            $city = ORM::factory('BoardCity', $model->city_id);
            $region = $city->parent_id;
            $cities = $this->_render_city_list($city->parent(), $model->city_id);
        }
        else{
            if(NULL !== $region = Arr::get($_POST, 'region')){
                $cities = $this->_render_city_list(ORM::factory('BoardCity', $region));
            }
        }

        $this->scripts[] = "media/libs/jquery-form-styler/jquery.formstyler.min.js";
        $this->styles[] = "media/libs/jquery-form-styler/jquery.formstyler.css";
        $this->scripts[] = "assets/user/js/profile.js";
        $this->user_content->set(array(
            'model' => $model,
            'photos' => $photos,
            'errors' => $errors,

            'regions' => $regions,
            'region' => $region,
            'cities' => $cities,
        ));
    }

    /**
     * Company enable/disable action
     */
    public function action_enable(){
        $id = $this->request->param('id');
        $model = ORM::factory('CatalogCompany')->where('id', '=', $id)->and_where('user_id', '=', $this->current_user->id)->find();
        if($id > 0 && !$model->loaded()){
            $this->redirect(URL::site().Route::get('catalog_mycompany')->uri());
            Flash::warning(__('Company not found'));
        }
        else{
            Flash::success(__('Your company successfully turned '. ($model->enable ? 'off' : 'on')));
            $model->flipStatus();
            $this->redirect(URL::site().Route::get('catalog_mycompany')->uri());

        }
    }

    /**
     * Company remove action
     */
    public function action_remove(){
        $id = $this->request->param('id');
        $model = ORM::factory('CatalogCompany')->where('id', '=', $id)->and_where('user_id', '=', $this->current_user->id)->find();
        if($id > 0 && !$model->loaded()){
            $this->redirect(URL::site().Route::get('catalog_mycompany')->uri());
            Flash::warning(__('Company not found'));
        }
        else{
            $model->delete();
            Flash::success(__('Your company successfully removed'));
            $this->redirect(URL::site().Route::get('catalog_mycompany')->uri());

        }

    }

    /**
     * User registration action
     */
    public function action_register(){
        if(Auth::instance()->logged_in('company'))
            $this->redirect(Route::get('catalog_mycompany')->uri(array()));

        $company = ORM::factory('CatalogCompany');
        if(Request::initial()->method() == Request::POST){
            if(!Auth::instance()->logged_in('login')){
                $data = Arr::extract($_POST, array('name', 'username', 'password', 'password_confirm', 'email', 'captcha'));
                $errors = $this->_validateUserData($data);
            }
            else{
                $errors = array();
                $exists = ORM::factory('CatalogCompany')->where('user_id', '=', $this->current_user->id)->find_all();
                if(count($exists))
                    $errors[] = __('Company already registered and wait for approvement');
            }
            $company_data = Arr::get($_POST, 'company');
            $company->values($company_data);
            try{
                $company->check();
            }
            catch(ORM_Validation_Exception $e){
                $errors = Arr::merge($errors, $e->errors('validation'));
            }
            if(!count($errors)){
                $role = ORM::factory('Role')->where('name', '=', 'company')->find();
                if(!Auth::instance()->logged_in('login')){
                    $user = $this->_saveUserData($data);
                    $user->add('roles', $role);
                }
                else{
                    $user = $this->current_user;
                    $user->load_roles();
                    if(!$user->has_role($role->name))
                        $user->add('roles', $role);
                }
                $company->user_id = $user->id;
                $company->save();

                $logo = Arr::get($_FILES, 'logo', false);
                if($logo && is_file($logo['tmp_name']) && Image::isImage($logo['tmp_name'])){
                    $company->addPhoto($logo['tmp_name']);
                }

                $this->go(Route::get('catalog_user')->uri(array(
                    'action' => Auth::instance()->logged_in('login') ? 'company_created' : 'registration_done'
                )));
            }
        }

        /* Регионы и города */
        $regions = array(''=>"Выберите регион");
        $regions += ORM::factory('BoardCity')->where('parent_id', '=', 0)->cached(Model_BoardCity::CITIES_CACHE_TIME)->find_all()->as_array('id','name');
        $cities = '';
        $region = NULL;
        if($company->city_id > 0){
            $city = ORM::factory('BoardCity', $company->city_id);
            $region = $city->parent_id;
            $cities = $this->_render_city_list($city->parent(), $company->city_id);
        }
        else{
            if(NULL !== $region = Arr::get($_POST, 'region')){
                $cities = $this->_render_city_list(ORM::factory('BoardCity', $region));
            }
        }

        $this->scripts[] = "media/libs/jquery-form-styler/jquery.formstyler.min.js";
        $this->styles[] = "media/libs/jquery-form-styler/jquery.formstyler.css";
        $this->scripts[] = "assets/user/js/profile.js";

        $this->template->content = $this->getContentTemplate($this->content_template);
        $this->user_content = $this->getContentTemplate('catalog/register');
        $this->user_content->bind('errors', $errors);
        $this->user_content->bind('data', $data);
        $this->user_content->set(array(
            'company' => $company,

            'regions' => $regions,
            'region' => $region,
            'cities' => $cities,
        ));
    }


    /**
     * Сообщение о том, что регистрация окончена
     */
    public function action_registration_done(){
        $this->template->content = $this->getContentTemplate($this->content_template);
        $this->user_content = $this->getContentTemplate('catalog/registration_done');
        $this->user_content->set(array(
            'site_name' => $this->config['project']['name'],
        ));
    }


    /**
     * Сообщение о том, что регистрация окончена
     */
    public function action_company_created(){
        $this->template->content = $this->getContentTemplate($this->content_template);
        $this->user_content = $this->getContentTemplate('catalog/company_created');
        $this->user_content->set(array(
            'site_name' => $this->config['project']['name'],
        ));
    }
}