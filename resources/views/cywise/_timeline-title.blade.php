<li class="timeline-item">
  <span class="timeline-item-hour">
    <span style="margin-left: -92px">{{ $time }}</span>
  </span>
  <span class="timeline-item-icon | faded-icon"
        style="@if($bgColor) background-color:{{ $bgColor }} !important;@endif @if($txtColor) color:{{ $txtColor }} !important; @endif">
    @if(isset($svg))
    {!! $svg !!}
    @else
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
      <path fill="none" d="M0 0h24v24H0z"/>
      <path fill="currentColor" d="M12 13H4v-2h8V4l8 8-8 8z"/>
    </svg>
    @endif
  </span>
  @if(!empty($comment))
  <div class="timeline-item-wrapper">
    <div class="timeline-item-description">
      <span>{{ $title }}</span>
    </div>
    <div class="comment">
      {!! $comment !!}
    </div>
  </div>
  @else
  <div class="timeline-item-description">
    <span>{{ $title }}</span>
  </div>
  @endif
</li>