<?php
namespace rbwebdesigns\blogcms;

class PublicController
{
    protected $request, $response;

    public function __construct(&$request, &$response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function home()
    {
        $this->response->setTitle('Website homepage');
        $this->response->setDescription('Front page to your website powered by Blog CMS');
        $this->response->write('public/home.tpl');
    }

}