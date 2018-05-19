<div class="ui grid">
    <div class="one column row">
        <div class="column">
            {viewCrumbtrail(["/cms/blog/overview/{$blog['id']}", "{$blog['name']}", "/cms/blog/contributors", "Contributors"], 'Create Contributor')}
        </div>
    </div>
    <div class="one column row">
        <div class="column">
            {viewPageHeader('Create Contributor', 'user plus', "{$blog['name']}")}
        </div>
    </div>
</div>

<form action="/cms/contributors/create/{$blog.id}" method="POST" class="ui form">
                    
    <h2 class="ui header">Basic Details</h2>
    <div class="two fields">
        <div class="field">
            <label for="fld_name">First Name</label>
            <input type="text" required name="fld_name">
        </div>
        <div class="field">
            <label for="fld_surname">Surname</label>
            <input type="text" required name="fld_surname">
        </div>
    </div>
    <div class="field">
        <label for="fld_email">Email</label>
        <input type="text" required name="fld_email">
    </div>
    <div class="field">
        <label for="fld_email_2">Re-type Email</label>
        <input type="text" required name="fld_email_2">
    </div>

    <div class="field">
        <label for="fld_gender">Gender</label>
        <select required name="fld_gender" class="ui dropdown">
            <option>Male</option>
            <option>Female</option>
        </select>
    </div>

    <h2 class="ui header">Account Setup</h2>
    <div class="field">
        <label for="fld_username">Username</label>
        <input type="text" required name="fld_username">
    </div>
    <div class="field">
        <label for="fld_password">Password</label>
        <input type="password" required name="fld_password">            
    </div>
    <div class="field">
        <label for="fld_password_2">Re-type Password</label>
        <input type="password" required name="fld_password_2">
    </div>

    <input type="button" name="fld_cancel_registration" value="Cancel" class="ui button right floated" onclick="window.history.back();">
    <input type="submit" name="fld_submit_registration" value="Submit" class="ui button teal right floated" style='font-weight:bold;'>
</form>

<script>
$(".ui.dropdown").dropdown();
</script>