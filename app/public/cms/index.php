<?php
namespace rbwebdesigns\blogcms;

use Codeliner;
use Athens\CSRF;
use rbwebdesigns\core\Sanitize;

/****************************************************************
  CMS Entry point
****************************************************************/

    // Include cms setup script
    require_once __DIR__ . '/../../setup.inc.php';

    
/****************************************************************
  Route request
****************************************************************/
    
    $request = BlogCMS::request();
    $response = BlogCMS::response();
    
    // Controller naming is important!
    // For simplicity, the code makes the following assumptions:
    //
    // For pages within the CMS Url path should be structured as:
    //   <controllerName>/<actionName>/<parameters>
    //
    // The url structure for blogs is slightly different
    //   /blogs/<blog_id>/<action>
    //
    // Controller file is created under /app/controller folder named:
    //   <controllerName>_controller.inc.php
    $controllerName = $request->getControllerName();
    
    if ($controllerName == 'account') {
        $action = $request->getUrlParameter(0, 'login');

        if ($action == 'login' || $action == 'register' || $action == 'resetpassword') {
            require SERVER_ROOT . '/app/controller/account_controller.inc.php';
            $controller = new \rbwebdesigns\blogcms\AccountController();
            $controller->$action($request, $response);
            exit;
        }
    }

    // User must be logged in to do anything in the CMS
    if (!USER_AUTHENTICATED) {
        $response->redirect('/cms/account/login', 'Login required', 'warning');
    }

    $user = BlogCMS::session()->currentUser;
    $modelContributors = BlogCMS::model('\rbwebdesigns\blogcms\model\Contributors');
    
    // Check the user has access to view/edit this blog
    $blogID = $request->getUrlParameter(1);
    if (strlen($blogID) == 10 && is_numeric($blogID)) {
        BlogCMS::$blogID = $blogID;

        // Surely must be an ID for a blog
        // Check the user has edit permissions
        BlogCMS::$userIsContributor = $modelContributors->isBlogContributor($user['id'], $blogID);

        if (!BlogCMS::$userIsContributor) {
            $response->redirect('/', 'You\'re not a contributor for that blog!', 'error');
        }
    }

    // Check form submissions for CSRF token
    CSRF::init();


/****************************************************************
  Setup controller
****************************************************************/
    
    // Check if we've got a valid controller
    $controllerFilePath = SERVER_ROOT . '/app/controller/' . $controllerName . '_controller.inc.php';

    if(!file_exists($controllerFilePath)) {
        $response->redirect('/cms', 'Page not found', 'error');
    }
    
    // Get controller class file
    require_once $controllerFilePath;
    
    // Dynamically instantiate new class
    $controllerClassName = '\rbwebdesigns\blogcms\\' . ucfirst($controllerName) . 'Controller';
    $controller = new $controllerClassName();

/****************************************************************
  Get body content
****************************************************************/

    // Add default stylesheet(s)
    $response->addStylesheet('/css/semantic.css');
    // $this->addStylesheet('/resources/css/core');
    $response->addStylesheet('/resources/css/header.css');
    // $this->addStylesheet('/resources/css/forms');
    $response->addStylesheet('/css/blogs_stylesheet.css');

    // Add default script(s)
    $response->addScript('/resources/js/jquery-1.8.0.min.js');
    $response->addScript('/js/semantic.js');
    $response->addScript('/resources/js/core-functions.js');
    $response->addScript('/resources/js/validate.js');
    $response->addScript('/resources/js/ajax.js');
    $response->addScript('/js/sidemenu.js');

    $response->setTitle('Default title');
    $response->setDescription('Default page description');

    // Call the requested function
    $action = $request->getUrlParameter(0, 'defaultAction');
    ob_start();
    $controller->$action($request, $response);
    $response->setBody(ob_get_contents());
    ob_end_clean();

    // Cases where template not required
    if ($controllerName == 'ajax' || $controllerName == 'api' || $request->isAjax) {
        $response->writeBody();
        exit;
    }

    
/****************************************************************
  Generate side menu
****************************************************************/

    $sideMenu = new Menu();
    $sideMenuLinks = [
        [
            'url' => '/cms',
            'icon' => 'list ul',
            'label' => 'My Blogs',
        ],
        [
            'label' => 'Blog Actions',
            'permissions' => ['is_contributor'],
        ],
        [
            'key' => 'overview',
            'url' => '/cms/blog/overview/'. $blogID,
            'icon' => 'chart bar',
            'permissions' => ['is_contributor'],
            'label' => 'Dashboard',
        ],
        [
            'key' => 'posts',
            'url' => '/cms/posts/manage/'. $blogID,
            'icon' => 'copy outline',
            'permissions' => ['is_contributor'],
            'label' => 'Posts',
        ],
        [
            'key' => 'comments',
            'url' => '/cms/comments/all/'. $blogID,
            'icon' => 'comments outline',
            'label' => 'Comments',
            'permissions' => ['manage_comments'],
        ],
        [
            'key' => 'files',
            'url' => '/cms/files/manage/'. $blogID,
            'icon' => 'image outline',
            'label' => 'Files',
            'permissions' => ['delete_files'],
        ],
        [
            'key' => 'settings',
            'url' => '/cms/settings/menu/'. $blogID,
            'icon' => 'cogs',
            'label' => 'Settings',
            'permissions' => ['change_settings'],
        ],
        [
            'key' => 'users',
            'url' => '/cms/contributors/manage/'. $blogID,
            'icon' => 'users',
            'label' => 'Contributors',
            'permissions' => ['manage_contributors'],
        ],
        [
            'key' => 'blog',
            'url' => '/blogs/'. $blogID,
            'icon' => 'book',
            'label' => 'View Blog',
            'permissions' => ['is_contributor'],
        ],
        [
            'label' => 'Your Account'
        ],
        [
            'key' => 'profile',
            'url' => '/cms/account/user',
            'icon' => 'user',
            'label' => 'View Profile',
        ],
        [
            'key' => 'accountsettings',
            'url' => '/cms/account/settings',
            'icon' => 'cogs',
            'label' => 'Settings',
        ],
        [
            'key' => 'logout',
            'url' => '/cms/account/logout',
            'icon' => 'arrow left',
            'label' => 'Logout',
        ]
    ];

    foreach ($sideMenuLinks as $link) {
        $newLink = new MenuLink();
        if ($link['url']) $newLink->url = $link['url'];
        if ($link['icon']) $newLink->icon = $link['icon'];
        if ($link['label']) $newLink->text = $link['label'];
        if ($link['permissions']) $newLink->permissions = $link['permissions'];
        if ($link['target']) $newLink->target = $link['target'];

        if (isset($link['key']) && $link['key'] == BlogCMS::$activeMenuLink) {
            $newLink->active = true;
        }

        if (!$newLink->accessible()) continue;

        $sideMenu->addLink($newLink);
    }

    BlogCMS::runHook('onMenuGenerated', ['id' => 'cms_main_actions', 'menu' => $sideMenu]);

    $response->setVar('page_sidemenu', $sideMenu);

    
/****************************************************************
  Run wrapping template
****************************************************************/

    // Run Template here
    $response->writeTemplate('template.tpl');
