<?php
/**
 * Helper function to keep the title consistant accross the website
 * 
 * @param string $title
 *   required - text to show as title
 * @param string $icon
 *   required - name of icon in resources/icons/64 folder
 * @param string $subtitle
 *   optional - string to show as subtitle
 * 
 * @return - string - HTML to echo
 */
function viewPageHeader($title, $icon, $subtitle='') {

    if (strlen($subtitle) > 0) {
        $subtitle = '<div class="sub header">' . $subtitle . '</div>';
    }

    return <<<HTML
        <h1 class="ui header">
            <i class="{$icon} icon"></i>
            <div class="content">
                {$title}
                {$subtitle}
            </div>
        </h1>
HTML;
}

/**
 * Helper function to keep crumbtrail consistant
 * 
 * @param array $path
 *   array with alternating url and name values for each link in the crumbtrail
 * @param string $currentPage
 *   label for the current page
 * 
 * @return string
 *   HTML for crumbtrail
 */
function viewCrumbtrail($path, $currentpage) {

    $output = <<<EOD
        <div class="ui breadcrumb"><a href="/" class="section">Home</a>
EOD;

    for($i = 0; $i < count($path) - 1; $i=$i+2):
    
        $output.= <<<EOD
        <i class="right angle icon divider"></i>
<a href="{$path[$i]}" class="section">{$path[$i+1]}</a>
EOD;
        
    endfor;
    
    return $output.'<i class="right angle icon divider"></i><div class="active section">'.$currentpage.'</div></div>';
}
