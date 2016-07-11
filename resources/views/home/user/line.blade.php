<li id="user{{$user?$user->id:''}}"
    class="row list_panel_line user_line"
    data="{{$user?$user->id:''}}"
    style="{{$styleAttr}}">
    <span class="line_fluid_column">
        <span class="overflow_container">
            <span class="overflow_ellipsis name_email">
                {{$user?$user->name:''}} {{$user?$user->email:''}}
            </span>
        </span>
    </span>
</li>