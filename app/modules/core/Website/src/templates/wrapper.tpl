<!DOCTYPE html>
<html>
<head>
    <title>{$page_title}</title>
    <meta charset="UTF-8">
    <meta description="{$page_description}">
    {$stylesheets}
    {$scripts}
</head>
<body>
    <div class="ui grid container">
        <div class="row">
            <div class="column">
                <div class="ui teal inverted center aligned segment">
                    <a href="/"><img src="/images/logo.png" alt="HamletCMS"></a>
                </div>

                {$body_content}
            </div>
        </div>
    </div>
</body>
</html>