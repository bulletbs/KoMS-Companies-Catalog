<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Котроллер для вывода главной страницы НОВОСТЕЙ
 */

class Controller_Catalog extends Controller_System_Page
{
    const MOST_CONTENT_INTERVAL_DAYS = 30;

    const MAIN_PAGE_CACHE = 'main_page';
    const MAIN_PAGE_CACHE_TIME = 1800;

    public $secured_actions = array(
        'new' => 'login',
    );

    public $skip_auto_render = array(
        'most',
        'similar',
        'goto',
    );

    public $skip_auto_content_apply = array(
        'main',
    );

    public $categories;

    protected $cfg;

    public function before(){
        parent::before();
        $this->scripts[] = "assets/catalog/js/catalog.js";
        $this->scripts[] = "assets/catalog/js/search.js";
        $this->styles[] = "assets/catalog/css/catalog.css";
        $this->styles[] = "media/libs/pure-release-0.6.0/grids.css";
        $this->scripts[] = "media/libs/rating/jquery.rating-2.0.min.js";
        $this->styles[] = "media/libs/rating/jquery.rating.css";

        $this->cfg = Kohana::$config->load('catalog')->as_array();
        if($this->auto_render){
            $this->breadcrumbs = Breadcrumbs::factory()->add($this->cfg['module_name'], Route::get('catalog')->uri(), 1);
            $this->template->set("search_form", Widget::factory('CatalogSearch'));
        }
    }

    /**
     * HMVC action for rendering catalog on mainpage
     */
    public function action_main(){
//        Cache::instance()->delete(self::MAIN_PAGE_CACHE);
//        if(!$content = Cache::instance()->get(self::MAIN_PAGE_CACHE)){

            /* META */
            $title = $this->cfg['main_title'];
            $this->title = $this->cfg['main_title'];

            /* Init Pagination module */
            $count = ORM::factory('CatalogCompany')->where('enable','=','1')->count_all();
            $pagination = Pagination::factory(array(
                'total_items' => $count,
                'group' => 'catalog',
            ))->route_params(array(
                'controller' => Request::current()->controller(),
            ));
            $companies = ORM::factory('CatalogCompany')->order_by('vip', 'desc')->where('enable','=','1')->order_by('addtime', 'DESC')->offset($pagination->offset)->limit($pagination->items_per_page)->find_all()->as_array('id');
            $photos = ORM::factory('CatalogCompanyPhoto')->companiesPhotoList(array_keys($companies));

            $content = View::factory('catalog/main')
                ->set('title', $title)
                ->set('companies', $companies)
                ->set('photos', $photos)
                ->set('categories', $this->categories)
                ->set('pagination', $pagination)
                ->render()
            ;
//            Cache::instance()->set(self::MAIN_PAGE_CACHE, $content, self::MAIN_PAGE_CACHE_TIME);
//        }
        $this->template->content = $content;
    }

    /**
     *  Output all category articles
     */
    public function action_search(){
        $city = NULL;
        $category = NULL;

        /* Загрузка региона */
        $city_alias = $this->request->param('city_alias');
        if($city_alias){
            $city_id = Model_CatalogCity::getCityIdByAlias($city_alias);
            $city = ORM::factory('CatalogCity', $city_id);
            if(!$city->loaded())
                $this->redirect(Route::get('catalog')->uri());
            $parents = $city->parents(true, true);
            foreach($parents as $_parent)
                $this->breadcrumbs->add($_parent->name, $_parent->getUri());
        }

        /* Загрузка категории */
        $cat_alias = $this->request->param('cat_alias');
        if($cat_alias){
            $category_id = Model_CatalogCategory::getCategoryIdByAlias($cat_alias);
            $category = ORM::factory('CatalogCategory', $category_id);
            if(!$category->loaded())
                $this->redirect(Route::get('catalog')->uri());
            $parents = $category->parents(true, true);
            foreach($parents as $_parent)
                $this->breadcrumbs->add($_parent->name, $_parent->id == $category->id ? FALSE : $_parent->getUri($city_alias));
        }

        /* Meta tags */
        $this->title = htmlspecialchars( $category->name .' - '.$this->config['view']['title']);


        /* Поиск компаний */
//        $companies = ORM::factory('CatalogCompany');
        $model = ORM::factory('CatalogCompany');
        $companies = DB::select()->from($model->table_name())->as_object(get_class($model));
        if($city instanceof ORM){
            $companies->where(!$city->parent_id ? 'pcity_id' : 'city_id','=', $city->id);
        }
        if($category instanceof ORM){
            $companies->join(array('catalog_company2category', 'c2c'), 'INNER')->on('catalogcompany.id','=','c2c.company_id');
            if(!$category->parent_id)
                $companies->where('c2c.category_id','IN', $category->getChildrenId());
            else
                $companies->where('c2c.category_id','=', $category->id);
        }
        $_query = Arr::get($_GET, 'query');
        if(!empty($_query) && mb_strlen($_query) >= 3){
            $companies->and_where(DB::expr('MATCH(`name`)'), 'AGAINST', DB::expr("('".$_query)."' IN BOOLEAN MODE)");
        }
        $companies = $companies
            ->and_where('enable','=','1')
            ->order_by('vip', 'desc')
            ->order_by('addtime', 'DESC');


        /* Init Pagination module */
        $count = clone($companies);
        $count = $count->select(DB::expr('count(*) cnt'))->cached(Model_BoardAd::CACHE_TIME)->as_assoc()->execute();
        $pagination = Pagination::factory(array(
            'total_items' => $count[0]['cnt'],
            'group' => 'catalog',
        ))->route_params(array(
            'controller' => Request::current()->controller(),
            'city_alias' => $city_alias,
            'cat_alias' => $cat_alias,
        ));

        /* Receive companies */
        $companies = $companies->offset($pagination->offset)
            ->limit($pagination->items_per_page)
            ->execute()->as_array('id') ;
        $photos = ORM::factory('CatalogCompanyPhoto')->companiesPhotoList(array_keys($companies));

        /* Init template */
        $this->template->content
            ->set('category', $category)
            ->set('city', $city)
            ->set('companies', $companies)
            ->set('photos', $photos)
            ->set('pagination', $pagination)
        ;
    }

    /**
     * Company output
     * @throws HTTP_Exception_404
     */
    public function action_company(){
        $id = $this->request->param('id');
        $company = ORM::factory('CatalogCompany', $id);
        if($company->loaded() && $company->enable==1){
            /* Views increment */
            DB::update($company->table_name())->set(array('views'=>DB::expr('views+1')))->where('id', '=', $id)->execute();

            /* breadcrumbs & similar articles */
            /* Breadcrumbs & part parents */
            $city_parents = ORM::factory('CatalogCity', $company->city_id)->parents(true, true)->as_array('id');
            foreach($city_parents as $_parent)
                $this->breadcrumbs->add($_parent->name, $_parent->getUri());
            $this->breadcrumbs->add($company->name, FALSE);

            /* Meta tags */
            $this->title = htmlspecialchars( !empty($company->title) ? $company->title : $company->name .' - '.$this->config['view']['title'], ENT_QUOTES);
            $this->description = htmlspecialchars( substr( strip_tags($company->desc) , 0, 255), ENT_QUOTES);
//            $this->keywords = !empty($company->keywords) ? $company->keywords : $this->config->view['keywords'];

            /* Photos */
            $logo = $company->photos->find();

            /* Ads */
            $ad_categories = $company->loadCompanyCategories();

            /* Libs */
            $this->styles[] = "media/libs/pure-release-0.5.0/forms.css";
            $this->styles[] = "media/libs/lightbox/lightbox.css";
            $this->scripts[] = "media/libs/lightbox/lightbox.js";
            $this->scripts[] = 'media/js/catalog/company.js';
            $this->styles[] = "assets/board/css/board.css";
            $this->template->content
                ->set('logo', $logo)
                ->set('company', $company)
                ->set('ad_categories', $ad_categories);
        }
        else{
            throw new HTTP_Exception_404('Requested page not found');
        }
    }

    /**
     * Company Ads search
     */
    public function action_ads(){
        $id = $this->request->param('id');
        $company = ORM::factory('CatalogCompany', $id);
        if($company->loaded()){

            $this->breadcrumbs->add( $company->name, $company->getUri() );
            $ads = Model_BoardAd::boardOrmFinder()->and_where('company_id', '=', $company->id);

            /* Categories */
            $category_id = Model_CatalogCategory::getAliases( $this->request->param('cat_alias', 0) );
            $category = ORM::factory('CatalogCategory', $category_id);
            if($category->loaded()){
                $parents = $category->parents(true, true);
                foreach($parents as $_cat)
                    $this->breadcrumbs->add( $_cat->name, $company->adsUri($_cat->id) );
                $ads->and_where( (!$category->parent_id ? 'p' : '').'category_id', '=', $category_id);
                $categories = $company->loadCompanyCategories($category->parent_id>0 ? $category->parent_id : $category->id);
            }
            else
                $categories = $company->loadCompanyCategories();

            /* requesting Ads */
            $counter = clone($ads);
            $counter->select(DB::expr('count(*) cnt'));
            $count = $counter->cached(Model_BoardAd::CACHE_TIME)->as_assoc()->execute();
            $pagination = Pagination::factory(array(
                'total_items' => $count[0]['cnt'],
                'group' => 'board',
            ))->route_params(array(
                'controller' => Request::current()->controller(),
                'id' => $company->id,
                'alias' => Text::transliterate($company->name),
                'cat_alias' => $this->request->param('cat_alias'),
            ));

            $ads->offset($pagination->offset)->limit($pagination->items_per_page);
            $ads = $ads->execute();

            $this->styles[] = "assets/board/css/board.css";
            $this->template->content->set(array(
                'ads' => $ads,
                'pagination' => $pagination,
                'company' => $company,
                'categories' => $categories
            ));
        }
        else{
            throw new HTTP_Exception_404('Requested page not found');
        }
    }

    /**
     * Отображение формы отправки сообщения (AJAX)
     */
    public function action_send_message(){
        if(!$this->request->is_ajax())
            $this->go(Route::get('catalog')->uri());
        $id = $this->request->param('id');
        $company = ORM::factory('CatalogCompany', $id);
        if($company->loaded()){
            $this->json['status'] = TRUE;
            $errors = array();
            if($this->request->method() == Request::POST){
                $validation = Validation::factory($_POST)
                    ->rule('email', 'not_empty')
                    ->rule('email', 'email', array(':value'))
                    ->rule('text', 'not_empty')
                    ->rule('text', 'min_length', array(':value',10))
                    ->rule('text', 'max_length', array(':value',1000))
                    ->labels(array(
                        'email' => __('Your e-mail'),
                        'text' => __('Message text'),
                        'captcha' => __('Enter captcha code'),
                    ))
                ;
                if(!$this->logged_in)
                    $validation->rules('captcha', array(
                        array('not_empty'),
                        array('Captcha::checkCaptcha', array(':value', ':validation', ':field'))
                    ));
                if($validation->check()){
                    Email::instance()
                        ->to($company->email)
                        ->from($this->config['robot_email'])
                        ->subject($this->config['project']['name'] .': '. __('Message from catalog'))
                        ->message(View::factory('catalog/company_mailto_letter', array(
                                'name' => $company->name,
                                'email'=> Arr::get($_POST, 'email'),
                                'text'=> strip_tags(Arr::get($_POST, 'text')),
                                'site_name'=> $this->config['project']['name'],
                            ))->render()
                            , true)
                        ->send();
                    Flash::success(__("Your message successfully sended"));
                    $this->json['content'] = Flash::render('global/flash');
                    return;
                }
                else
                    $errors = $validation->errors('error/validation');
            }
            $this->json['content'] = View::factory('catalog/company_mailto')->set(array(
                'errors' => $errors,
                'company_id' => $company->id,
            ))->render();
        }
    }

    /**
     * Redirection to article source
     * @throws HTTP_Exception_404
     */
    public function action_goto(){
        $id = $this->request->param('id');
        $company = ORM::factory('CatalogCompany', $id);
        if($company->loaded() && $company->enable==1 && !empty($company->website)){
            $company->gotoSource();
        }
        else{
            throw new HTTP_Exception_404('Requested page not found');
        }
    }
}