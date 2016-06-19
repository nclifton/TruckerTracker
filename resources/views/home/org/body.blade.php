<!-- Nav tabs -->
<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#config" aria-controls="config" role="tab" data-toggle="tab"
                                              id="org-config-tab-link">Configuration</a></li>
    <li role="presentation"><a href="#users" aria-controls="users" role="tab" data-toggle="tab" id="org-users-tab-link">Users</a>
    </li>
</ul>

<!-- Tab panes -->
<div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="config">
        @include('home.org.configTabPanel')
    </div>
    <div role="tabpanel" class="tab-pane" id="users">
        @include('home.org.usersTabPanel')
    </div>
</div>
