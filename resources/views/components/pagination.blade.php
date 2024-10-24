<div class="row">
  <div class="col">
    <ul class="pagination justify-content-center mt-3 mb-3">
      <li class="page-item {{ $currentPage <= 1 ? 'disabled' : '' }}">
        <a class="page-link" href="{{ route('home', ['tab' => 'chunks', 'page' => 1]) }}">
          <span>&laquo;&nbsp;{{ __('First') }}</span>
        </a>
      </li>
      <li class="page-item {{ $currentPage <= 1 ? 'disabled' : '' }}">
        <a class="page-link"
           href="{{ route('home', ['tab' => 'chunks', 'page' => $currentPage <= 1 ? 1 : $currentPage - 1]) }}">
          <span>&lt;&nbsp;{{ __('Previous') }}</span>
        </a>
      </li>
      <!--
      @if($currentPage > 1)
      <li class="page-item">
        <a class="page-link" href="{{ route('home', ['tab' => 'chunks', 'page' => $currentPage - 1]) }}">
          {{ $currentPage - 1 }}
        </a>
      </li>
      @endif
      -->
      <li class="page-item">
        <a class="page-link active"
           href="{{ route('home', ['tab' => 'chunks', 'page' => $currentPage]) }}">
          {{ $currentPage }}
        </a>
      </li>
      <!--
      @if($currentPage < $nbPages)
      <li class="page-item">
        <a class="page-link" href="{{ route('home', ['tab' => 'chunks', 'page' => $currentPage + 1]) }}">
          {{ $currentPage + 1 }}
        </a>
      </li>
      @endif
      -->
      <li class="page-item {{ $currentPage >= $nbPages ? 'disabled' : '' }}">
        <a class="page-link"
           href="{{ route('home', ['tab' => 'chunks', 'page' => $currentPage >= $nbPages ? $nbPages : $currentPage + 1])}}">
          <span>{{ __('Next') }}&nbsp;&gt;</span>
        </a>
      </li>
      <li class="page-item {{ $currentPage >= $nbPages ? 'disabled' : '' }}">
        <a class="page-link" href="{{ route('home', ['tab' => 'chunks', 'page' => $nbPages]) }}">
          <span>{{ __('Last') }}&nbsp;&raquo;</span>
        </a>
      </li>
    </ul>
  </div>
</div>