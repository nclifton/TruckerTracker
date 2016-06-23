<!-- Nav tabs -->
<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#config" aria-controls="config" role="tab" data-toggle="tab"
                                              id="org-config-tab-link">Configuration</a>
    </li>
    <li role="presentation" class=""><a href="#twilio" aria-controls="twilio" role="tab" data-toggle="tab"
                                        id="org-twilio-tab-link">Twilio</a>
    </li>
    <li role="presentation" class="disabled"><a href="#users" aria-controls="users" role="tab" data-toggle=""
                                                id="org-users-tab-link">Users</a>
    </li>
</ul>

<!-- Tab panes -->
<div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="config">
        @include('home.org.configTabPanel')
    </div>
    <div role="tabpanel" class="tab-pane" id="twilio">
        @include('home.org.twilioTabPanel')
    </div>
    <div role="tabpanel" class="tab-pane" id="users">
        @include('home.org.usersTabPanel')
    </div>
</div>
