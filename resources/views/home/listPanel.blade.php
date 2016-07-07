<div class="list-panel scroll-panel">
    <ul id="{{str_replace('.','-',$subject)}}_list" class="{{str_replace('.','-',$subject)}}-list container">
        @include('home.'.$subject.'.line',array($subject=>'','styleAttr'=>'display:none'))
        @foreach($lines as $line)
           @include('home.'.$subject.'.line',array($subject=>$line,'styleAttr'=>''))
        @endforeach
    </ul>
</div>