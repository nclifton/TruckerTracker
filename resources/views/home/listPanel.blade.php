<div class="list-panel scroll-panel">
    <ul id="{{$subject}}_list" class="{{$subject}}-list container">
        @foreach($lines as $line)
           @include('home.'.$subject.'Line',array($subject=>$line,'styleAttr'=>''))
        @endforeach
        @include('home.'.$subject.'Line',array($subject=>'','styleAttr'=>'display:none'))
    </ul>
</div>