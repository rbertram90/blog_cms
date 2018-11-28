<?php
namespace rbwebdesigns\blogcms\BlogPosts\model;

use rbwebdesigns\blogcms\BlogCMS;
use rbwebdesigns\core\model\RBFactory;

class PostViews extends RBFactory
{

    function __construct($modelManager)
    {
        $this->tableName = TBL_POST_VIEWS;
        $this->tablePosts = TBL_POSTS;

        $this->fields = [
            'postid',
            'userip',
            'userviews'
        ];

        parent::__construct($modelManager);
    }

    /**
     * Get the total number of times any post on a blog has been visited
     * 
     * @param int $blogID
     */
    public function getTotalPostViewsByBlog($blogID)
    {
        $query = "SELECT sum(userviews) as totalViews
            FROM {$this->tableName}
            WHERE postid
            IN (SELECT id FROM {$this->tablePosts} WHERE blog_id='$blogID')";

        $result = $this->db->query($query)
            ->fetch(\PDO::FETCH_ASSOC);
        
        return intval($result['totalViews']);
    }
    
}