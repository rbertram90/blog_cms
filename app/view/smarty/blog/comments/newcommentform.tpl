{if $userAuthenticated}
    <h2>Add Comment</h2>

    <div class="ui form">
        <form action="/blogs/{$post.blog_id}/posts/{$post.id}/addcomment" method="POST">
            
            <div class="field">
                <textarea name="fld_comment" rows="2"></textarea>
            </div>

            <div class="push-right">
                <input type="submit" name="fld_submitcomment" class="ui button primary" value="Add">
            </div>
        </form>
    </div>
{/if}