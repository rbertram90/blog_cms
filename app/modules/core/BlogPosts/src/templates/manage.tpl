{* Manage Posts *}

<div class="ui grid">
    
    <div class="one column row">
        <div class="column">
            {viewCrumbtrail(array("/cms/blog/overview/{$blog->id}", "{$blog->name}"), 'Manage posts')}
        </div>
    </div>
    
    <div class="two column row">
        <div class="column">
            {viewPageHeader('Manage posts', 'copy outline', "{$blog->name}")}
        </div>
        <div class="column">
            <div class="ui form">
                <div class="ui horizontal segments margin" style="margin:0;">
                    <div class="ui segment">
                        <div class="field">
                            <label for="numtoshow">Show</label>
                            <select id="numtoshow" name="numtoshow" class="ui fluid dropdown">
                                <option>5</option>
                                <option selected>10</option>
                                <option>15</option>
                                <option>20</option>
                            </select>
                        </div>
                    </div>
                    <div class="ui segment">
                        <div class="field">
                            <label for="sortby">Sort</label>
                            <select id="sortby" name="sortby" class="ui fluid dropdown">
                                <option value="timestamp DESC">Date posted (newest first)</option>
                                <option value="timestamp ASC">Date posted (oldest first)</option>
                                <option value="title ASC">Title (A - Z)</option>
                                <option value="title DESC">Title (Z - A)</option>
                                <option value="author_id ASC">Author ID (low > high)</option>
                                <option value="author_id DESC">Author ID (high > low)</option>
                                <option value="hits DESC">Views (most first)</option>
                                <option value="hits ASC">Views (least first)</option>
                                <option value="uniqueviews DESC">Visitors (most first)</option>
                                <option value="uniqueviews ASC">Visitors (least first)</option>
                            </select>
                        </div>
                    </div>
                    <div class="ui segment">
                        <div class="inline field">
                            <div class="ui checkbox">
                                <input type="checkbox" class="hidden" id="filterdrafts" name="filterdrafts" checked>
                                <label>Show drafts</label>
                            </div>
                        </div>
                        <div class="inline field">
                            <div class="ui checkbox">
                                <input type="checkbox" class="hidden" id="filterscheduled" name="filterscheduled" checked />
                                <label>Show scheduled</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script>$('.ui.checkbox').checkbox();</script>
            <script>$('.ui.dropdown').dropdown();</script>
        </div>
    </div>
    
    <div class="one column row">
        <div class="column">
            <div id="manage_posts_messages"></div>
            <div id="posts_display"><img src="/images/ajax-loader.gif" alt="Loading..."></div>
        </div>
    </div>
    
</div>
    
<script>
    // E.g. 3:11, 22nd Aug 2015
    function formatDate(date) {
        var d = new Date(date);
        
        switch(d.getDate()) {
            case 1,21,31:
                var suffix = 'st';
                break;
            case 2,22:
                var suffix = 'nd';
                break;
            case 3,23:
                var suffix = 'rd';
                break;
            default:
                var suffix = 'th';
                break;
        }
        
        if (d.getMinutes() < 10) {
            minutes = '0' + d.getMinutes();
        }
        else {
            minutes = d.getMinutes()
        }

        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        return d.getHours() + ':' + minutes + ', ' + d.getDate() + suffix + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
    }
    
    var refreshData = function(pagenum) {
        pagenum = parseInt(pagenum);
        var numtoshow = $("#numtoshow").val();
        var sortby = $("#sortby").val();
        var filterdrafts = $("#filterdrafts").is(':checked');
        var filterscheduled = $("#filterscheduled").is(':checked');

        $.get("/api/posts",
            {
                blogID:         {$blog->id},
                start:          pagenum,
                limit:          numtoshow,
                sort:           sortby,
                showdrafts:     filterdrafts,
                showscheduled:  filterscheduled

            }, function(data) {
                var start = pagenum * numtoshow - (numtoshow - 1);
                var end   = (pagenum * numtoshow) > data.postcount ? data.postcount : pagenum * numtoshow;
                var numpages = Math.ceil(data.postcount / numtoshow);
                var output = '';

                output += '<div class="ui secondary clearing segment">';
                output += '<a href="/cms/settings/posts/' + data.blog.id + '" class="ui right floated icon labeled button"><i class="cog icon"></i>Post settings</a>';  
                output += '<a href="/cms/posts/create/' + data.blog.id + '" class="ui right floated icon labeled teal button"><i class="plus icon"></i>New post</a>';  
                output += "<p class='ui right aligned'>Showing <strong>" + start + "</strong> - <strong>" + end + "</strong> of <strong>" + data.postcount + "</strong></p></div>";

                              
                output += "<table class='ui table'><thead>";
                output += "<tr><th>Title</th><th></th><th>Tag(s)</th><th>Author</th>";
    
                output += "<th>Visitors <a href='#' class='helptext' onclick='javascript:alert(\"This is the count of \'unique visitors\' for each post, not the number of times it has been viewed. So it will count 1 view even if someone refreshes the page multiple times\");'>[?]</a></th>";
    
                output += "<th>Views <a href='#' class='helptext' onclick='javascript:alert(\"This is the number of times each blog post has been loaded, if someone was to refresh the page 1000 times then it will show 1000 views, so this statistic may be unreliable\");'>[?]</a></th>";
    
                output += "<th>Type</th><th>Word count</th></tr></thead>";
            
                for(var i=0; i<numtoshow; i++) {
                                        
                    var post = data.posts[i];
                    
                    if(!post) break;
                    
                    var tagoutput = "";
                    
                    if (post.tags.length > 0) {
                        var tags = post.tags.split(","); // todo: split out

                        for(var k=0; k<tags.length; k++) {
                            tag = tags[k].trim();
                            tag = tag.replace("+", " ");
                            tagoutput += "<div class='ui horizontal label'><a href='/blogs/" + data.blog.id + "/tags/" + tag + "'>" + tag + "</a></div>";
                        }
                    }
                    else {
                        tagoutput = "<i>None</i>";
                    }
                    
                    output += "<tr><td>";
                    // @todo apply custom domain compatible link
                    output += " <a href='/blogs/" + data.blog.id + "/posts/" + post.link + "'>" + post.title + "</a>"
                    
                    if (new Date(post.timestamp) > new Date()) {
                        // Scheduled
                        output += " <i>Scheduled</i>";
                    }
                    
                    if (post.draft == 1) {
                        // Draft
                        output += " <i>Draft</i>";
                    }
                    
                    // todo: add scheduled and draft flags
                    output += " <br><span class='date'>" + formatDate(post.timestamp) + "</span>";
                                        
                    output += "</td><td width='100'>";
                    output += " <div class='option-dropdown' style='width:100px;'>";
                    output += "   <div class='default-option'>- Actions -</div>";
                    output += "   <div class='hidden-options'>";
                    output += "     <a href='/cms/posts/edit/" + post.id + "'>Edit</a>";
                    output += "     <a class='clone_post_link' data-postid='" + post.id + "'>Clone</a>";
                    output += "     <a class='delete_post_link' data-postid='" + post.id + "'>Delete</a>";

                    output += " </div></div></td><td>" + tagoutput;

                    output += "</td><td>";
                    output += " <a href='/account/user/" + post.author_id + "' class='user-link'>";
                    output += "   <span data-userid='" + post.author_id + "'>" + post.username + "</span></a>";
                    
                    output += "</td><td>";
                    output += " <div class='ui circular label'>" + post.uniqueviews + "</div>";

                    output += "</td><td>";
                    output += " <div class='ui circular label'>" + post.hits + "</div>";

                    output += "</td><td>";
                    output += " <div class='ui label'>" + post.type + "</div>";
                    
                    output += "</td><td>" + post.wordcount;
                    output += " </td></tr>";
                }
            
                output += '</table>';
            
                output += '<div class="ui pagination menu">';

                // Don't show back link if current page is first page.
                if (pagenum == 1) {
                    output += '<a class="disabled item"><i class="icon angle left"></i></a>';
                }
                else {
                    output += '<a href="#" class="item" onclick="refreshData(\'' + (pagenum-1) + '\'); return false;"><i class="icon angle left"></i></a>';
                }

                var pstart = 1;
                var pend = numpages;

                if (numpages > 10) {
                    pstart = pagenum - 5;
                    if (pstart < 1) pstart = 1;

                    pend = pstart + 10;
                    if (pend > numpages) pend = numpages;
                }

                // loop through each page and give link to it.
                for (var j=pstart; j<=pend; j++) {
                    if (pagenum == j) output += '<a class="active item">' + j + '</a>';
                    else output += '<a href="#" class="item" onclick="refreshData(\'' + j + '\'); return false;">' + j + '</a>';
                }
                // If last page don't give next link.
                if (pagenum < numpages) {
                    output += '<a href="#" class="item" onclick="refreshData(\'' + (pagenum+1) + '\'); return false;"><i class="icon angle right"></i></a>';
                }
                else {
                    output += '<a class="disabled item"><i class="icon angle right"></i></a>';
                }
            
                output += '</div>';
                
                output += '<script>';
                output += '  $(".user-link").mouseenter(function() {ldelim} showUserProfile($(this), "/", "/") {rdelim});';
                output += '  $(".user-link").mouseleave(function() {ldelim} hideUserProfile($(this)) {rdelim});';
                output += '<\/script>';

                $("#posts_display").html(output);

                $(".delete_post_link").click(function(event) {
                    event.preventDefault();
                    $("#delete_post_button").data("postid", $(this).data('postid'));
                    $("#delete_post_modal").modal('setting', 'closable', false).modal('show');
                });

                $(".clone_post_link").click(function(event) {
                    event.preventDefault();
                    $("#clone_post_button").data("postid", $(this).data('postid'));
                    $("#clone_post_modal").modal('show');
                });
            }
        );
    };
    
    // change number that is shown - return to first page
    $("#numtoshow").change(function()       { refreshData(1); });
    $("#sortby").change(function()          { refreshData(1); });
    $("#filterdrafts").change(function()    { refreshData(1); });
    $("#filterscheduled").change(function() { refreshData(1); });

    // Init
    refreshData(1);

</script>

<div class="ui basic modal" id="delete_post_modal">
  <div class="ui icon huge header">
    <i class="trash alternate outline icon"></i>
    Delete post
  </div>
  <div class="content" style="text-align:center;">
    <p>Are you sure you want to delete this post?</p>
  </div>
  <div class="actions" style="text-align:center;">
    <a class="big ui green ok inverted button" id="delete_post_button">
      <i class="checkmark icon"></i>
      Delete
    </a>
    <div class="big ui red basic cancel inverted button">
      <i class="remove icon"></i>
      Nevermind
    </div>
  </div>
</div>

<div class="ui basic modal" id="clone_post_modal">
  <div class="ui icon huge header">
    <i class="copy outline icon"></i>
    Clone post
  </div>
  <div class="content" style="text-align:center;">
    <p>Are you sure you want to clone this post?</p>
  </div>
  <div class="actions" style="text-align:center;">
    <a class="big ui green ok inverted button" id="clone_post_button">
      <i class="checkmark icon"></i>
      Clone
    </a>
    <div class="big ui red basic cancel inverted button">
      <i class="remove icon"></i>
      Nevermind
    </div>
  </div>
</div>

<script>
    $("#delete_post_button").click(function(event) {
        event.preventDefault();
        var postID = $(this).data("postid");

        $.ajax({
            url: '/api/posts/delete',
            type: 'post',
            data: {
                postID: postID,
                blogID: {$blog->id}
            }
        }).done(function (data, textStatus, jqXHR) {
            $("#delete_post_modal").modal('hide');
            refreshData(1);
            $("#manage_posts_messages").html('<p class="ui success message">Post deleted</p>');

        }).fail(function (jqXHR, textStatus, errorThrown) {
            data = JSON.parse(jqXHR.responseText);
            $("#manage_posts_messages").html('<p class="ui error message">' + data.errorMessage + '</p>');
        });
    });

    $("#clone_post_button").click(function(event) {
        event.preventDefault();
        var postID = $(this).data("postid");

        $.ajax({
            url: '/api/posts/clone',
            type: 'post',
            data: {
                postID: postID,
                blogID: {$blog->id}
            }
        }).done(function (data, textStatus, jqXHR) {
            $("#delete_post_modal").modal('hide');
            refreshData(1);
            $("#manage_posts_messages").html('<p class="ui success message">Post cloned</p>');

        }).fail(function (jqXHR, textStatus, errorThrown) {
            data = JSON.parse(jqXHR.responseText);
            $("#manage_posts_messages").html('<p class="ui error message">' + data.errorMessage + '</p>');
        });
    });
</script>