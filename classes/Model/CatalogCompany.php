<?php defined('SYSPATH') or die('No direct script access.');

class Model_CatalogCompany extends ORM{

    public $image;
    public $thumb;

    protected $_table_name = 'catalog_company';

    protected $_uriToMe;

    protected $_belongs_to = array(
        'user' => array(
            'model' => 'User',
            'foreign_key' => 'user_id',
        ),
    );

    protected $_self_alias;

    protected $_has_many = array(
        'photos' => array(
            'model' => 'CatalogCompanyPhoto',
            'foreign_key' => 'company_id',
        ),
        'categories'=>array(
            'model' => 'CatalogCategory',
            'through' => 'catalog_company2category',
            'foreign_key' => 'company_id',
            'far_key' => 'category_id',
        ),
        'ads' =>array(
            'model' => 'BoardAd',
            'foreign_key' => 'company_id',
        ),
    );

    public function rules(){
        return array(
            'name' => array(
                array('not_empty'),
                array('min_length', array(':value',3)),
            ),
            'desc' => array(
                array('not_empty'),
            ),
            'address' => array(
                array('not_empty'),
            ),
            'telephone' => array(
                array('not_empty'),
                array('min_length', array(':value',10)),
            ),
            'city_id' => array(
                array('not_empty'),
            ),
        );
    }

    public function labels(){
        return array(
            'id' => __('Id'),
            'category_id' => 'Категория',
            'addtime' => __('Create Time'),
            'user_id' => __('User ID'),
            'name' => __('Company name'),
            'nameLink' => __('Company name'),
            'desc' => __('Company description'),
            'hits' => __('Hits'),
            'votes' => __('Votes'),
            'rating' => __('Rating'),
            'metakey' => __('Meta keywords'),
            'metadesc' => __('Meta description'),
            'address' => __('Address'),
            'city' => __('City'),
            'city_id' => __('City'),
            'state' => __('Region'),
            'country' => __('Country'),
            'postcode' => __('Postcode'),
            'telephone' => __('Phone'),
            'fax' => __('Fax'),
            'email' => __('Email'),
            'website' => __('Website'),
            'lat' => __('Latitude'),
            'lng' => __('Longitude'),
            'zoom' => __('Zoom'),
            'enable' => __('Enable'),
            'vip' => __('Vip'),
            'views' => __('Views'),
            'comments' => __('Comments count'),
            'photos' => __('Photos'),
            'maxads' => __('ADs limit'),
        );
    }

    public function filters(){
        return array(
            'name' => array(
                array('trim')
            ),
            'desc' => array(
                array('trim')
            ),
            'address' => array(
                array('trim')
            ),
            'website' => array(
                array(array($this,'finalizeSource'))
            ),
        );
    }

    /**
     * Добавить фото к объявлению
     * @param $file
     * @param $attributes
     * @return bool|ORM
     */
    public function addPhoto( $file, $attributes = array()){
        if(!$this->loaded() || !Image::isImage($file))
            return false;
        $photo = ORM::factory('CatalogCompanyPhoto')->values(array(
            'company_id'=>$this->pk(),
        ))->values($attributes)->save();
        $photo->savePhoto($file);
        $photo->saveThumb($file);
        $photo->savePreview($file);
        return $photo->update();
    }

    /**
     * Удалить фото
     * @param $id
     * @return bool
     */
    public function deletePhoto($id){
        $photo = ORM::factory('CatalogCompanyPhoto', $id);
        if($photo){
            $photo->delete();
            return true;
        }
        return false;
    }

    /**
     * Сохранение модели
     * @param Validation $validation
     * @return ORM|void
     */
    public function save(Validation $validation = NULL){
        if(!$this->addtime){
            $this->addtime = time();
        }
        /**
         * Setting parents
         */
        if($this->changed('city_id')){
            $this->pcity_id = ORM::factory('CatalogCity', $this->city_id)->parent_id;
        }
        parent::save($validation);
    }

    /**
     * Удаление модели
     * @return ORM|void
     */
    public function delete(){
        if($this->user_id)
            $this->user->remove('roles', ORM::factory('role', array('name' => 'company')));
        foreach( $this->ads->find_all() as $ad)
            $ad->delete();
        foreach( $this->photos->find_all() as $photo)
            $photo->delete();
        if(is_dir(DOCROOT."/media/upload/catalog/". $this->id))
            rmdir(DOCROOT."/media/upload/catalog/". $this->id);
        parent::delete();
    }

    /**
     * @param null $id
     */
    public function setMainPhoto($id = NULL){
        $photo_table = ORM::factory('CatalogCompanyPhoto')->table_name();
        $main = ORM::factory('CatalogCompanyPhoto')->where('company_id' ,'=', $this->id)->and_where('main' ,'=', 1)->find();
        $exists = $main->loaded();
        if($id){
            DB::update($photo_table)->set(array('main'=>0))->where('company_id' ,'=', $this->id)->execute();
            $exists = DB::update($photo_table)->set(array('main'=>1))->where('company_id' ,'=', $this->id)->and_where('id' ,'=', $id)->execute();
        }
        if(!$exists){
            $photo = ORM::factory('CatalogCompanyPhoto')->where('company_id' ,'=', $this->id)->find();
            if($photo)
                DB::update($photo_table)->set(array('main'=>1))->where('company_id' ,'=', $this->id)->and_where('id' ,'=', $photo->id)->execute();
        }
    }

    /**
     * Getting article uri
     * @return string
     */
    public function getUri(){
        if(is_null($this->_uriToMe)){
//            $categories = Model_CatalogCategory::getCategoriesList();
//            $parts_uris = array_flip(Model_NewsCategory::$parts_uri);

            $this->_uriToMe = Route::get('catalog_company')->uri(array(
                'id' => $this->id,
//                'cat_alias' => $categories[$this->category_id]->alias,
//                'part_alias' => $parts_uris[$categories[$this->category_id]->part_id],
                'alias' => Text::transliterate($this->name, true),
            ));
        }
        return $this->_uriToMe;
    }

    /**
     * Generate link to company products page
     * @param $category_id
     * @param null $page
     * @return string
     * @throws Kohana_Exception
     */
    public function adsUri($category_id=NULL, $page = NULL){
        $params = array(
            'id' => $this->id,
            'alias' => $this->alias(),
        );
        if(!is_null($category_id))
            $params['cat_alias'] = Model_CatalogCategory::getField('alias', $category_id);
        if(!is_null($page))
            $params['page'] = $page;
        return Route::get('catalog_ads')->uri($params);
    }

    /**
     * Finalize entered website before saving model
     * @param $website
     * @return string
     */
    public function finalizeSource($website){
        if(!empty($website) && !strstr($website, 'http://'))
            $website = 'http://'.$website;
        return $website;
    }

    /**
     * Return formated source link
     * @param array $parameters
     * @return null|string
     */
    public function getSourceLink(Array $parameters = array()){
        if(!empty($this->website)){
            $name = str_replace('http://','',$this->website);
            $name = str_replace('www.','',$name);
            $name = preg_replace('/\/.*/u','',$name);
            $parameters['target'] = '_blank';
            $uri = Route::get('catalog')->uri(array(
                'action' => 'goto',
                'id' => $this->id,
            ));
            return HTML::anchor($uri, $name, $parameters);
        }
        return NULL;
    }

    /**
     * Return company full address
     * @return string
     */
    public function getCompanyAddress(){
        $address = '';
        if($this->pcity_id)
            $address .= (!empty($address) ? ', ' : '') . HTML::anchor(Model_CatalogCity::generateUri(Model_CatalogCity::getField('alias', $this->pcity_id)), Model_CatalogCity::getField('name', $this->pcity_id));
        if($this->city_id)
            $address .= (!empty($address) ? ', ' : '') . HTML::anchor(Model_CatalogCity::generateUri(Model_CatalogCity::getField('alias', $this->city_id)), Model_CatalogCity::getField('name', $this->city_id));
        if(!empty($this->country))
            $address .= (!empty($address) ? ', ' : '') . $this->country;
        if(!empty($this->state))
            $address .= (!empty($address) ? ', ' : '') . $this->state;
        if(!empty($this->city))
            $address .= (!empty($address) ? ', ' : '') . $this->city;
        if(!empty($this->address))
            $address .= (!empty($address) ? ', ' : '') . $this->address;
        return $address;
    }

    /**
     * Flip company status
     */
    public function flipStatus(){
        $this->enable = $this->enable == 0 ? 1 : 0;
        $this->update();
    }

    /**
     * Redirection to source url
     */
    public function gotoSource(){
        if(!empty($this->website)){
            header("Location: ". $this->website);
        }
        else{
            header("Location: ". $this->getUri());
        }
        die();
    }

    /**
     * Company alias getter
     * @return string
     */
    public function alias(){
        if(is_null($this->_self_alias))
            $this->_self_alias = Text::transliterate($this->name);
        return $this->_self_alias;
    }

    /**
     * Smart model field getter
     * @param string $name
     * @return mixed|string
     */
    public function __get($name){
        if($name == 'nameLink'){
            return HTML::anchor($this->getUri(), $this->name, array('target'=>'_blank'));
        }
        return parent::__get($name);
    }

    /**
     * Request links array for sitemap generation
     * @return array
     */
    public function sitemapCompanies(){
        $links = array();
        $models = ORM::factory('CatalogCompany')->where('enable','=','1')->find_all();
        foreach($models as $model)
            $links[] = $model->getUri();
        return $links;
    }


    /**
     * Count comment objects array
     * than has not been moderated before
     * @return int
     */
    public function countAds(){
        $count = $this->ads->count_all();
        return $count;
    }


    /**
     * Count comment objects array
     * than has not been moderated before
     * @return int
     */
    public function countNotModerated(){
        $count = ORM::factory($this->object_name())->where('moderate', '=', 0)->count_all();
        return $count;
    }


    /**
     * Load categories by ads
     * @param null $parent_id
     * @return mixed
     */
    public function loadCompanyCategories($parent_id = NULL){
        $field = $parent_id ? 'category_id' : 'pcategory_id';
        $sql = DB::select()
            ->from('ads')
            ->select(DB::expr('distinct('. $field .') category_id'), DB::expr('count(*) cnt'))
            ->where('publish', '=', 1)
            ->and_where('company_id', '=', $this->id)
            ->group_by(DB::expr(1))
            ->cached(Date::HOUR);
        if($parent_id > 0)
            $sql->and_where('pcategory_id', '=', $parent_id);
        $categories = $sql->execute()
            ->as_array('category_id', 'cnt');
        return $categories;
    }

    /**
     * Update company to categories relations
     * @param $company_id
     */
    public static function updateCompanyCategories($company_id){
        $company = ORM::factory('CatalogCompany', $company_id);
        if($company->loaded()){
            $company->remove('categories');
            $ids = DB::select('category_id')
                ->distinct('category_id')
                ->from('ads')
                ->where('company_id', '=', $company_id)
                ->and_where('publish', '=', 1)
                ->execute();
            ;
            foreach($ids as $_category)
                $company->add('categories', $_category['category_id']);
            $company->enable = count($ids) > 0;
            $company->save();
        }
    }
}