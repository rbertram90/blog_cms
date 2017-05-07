<div class="ui grid">
    <div class="one column row">
        <div class="column">
            {viewCrumbtrail(array("/overview/{$blog.id}", {$blog.name}, "/config/{$blog.id}", 'Settings'), 'General Settings')}
        </div>
    </div>
    <div class="one column row">
        <div class="column">
            {viewPageHeader('General Settings', 'id.png', {$blog.name})}

            <form action="/config/{$blog.id}/general/submit" method="POST" class="ui form">
                
                <div class="field">
                    <label for="fld_blogname">Blog Name</label>
                    <input type="text" value="{$blog.name}" name="fld_blogname" />
                </div>
                
                <div class="field">
                    <label for="fld_blogdesc">Description</label>
                    <textarea name="fld_blogdesc">{$blog.description}</textarea>
                </div>
                
                <div class="field">
                    <label for="fld_category">Category</label>
                    <select id="fld_category" name="fld_category">
                        {foreach from=$categorylist item=category}
                            <option value="{$category}">{ucfirst($category)}</option>
                        {/foreach}
                    </select>
                    <!--Set Default-->
                    <script type="text/javascript">$("#fld_category").val("{$blog.category}");</script>
                </div>
                
                <div class="field">
                    <label for="fld_blogsecurity">Who should be able to read your blog?</label>
                    <select id="fld_blogsecurity" name="fld_blogsecurity">
                        <option value="anon">Everyone</option>
                        <option value="members">Logged In Members</option>
                        <option value="friends">Your Friends</option>
                        <option value="private">Private (Just You)</option>
                    </select>
                    <!--Set Default-->
                    <script type="text/javascript">$("#fld_blogsecurity").val("{$blog.visibility}");</script>
                </div>

                <input type="submit" class="ui button floated right teal" value="Update" />
                <input type="button" value="Cancel" class="ui button floated right" name="goback" onclick="window.history.back()" />

            </form>
        </div>
    </div>
    
</div>