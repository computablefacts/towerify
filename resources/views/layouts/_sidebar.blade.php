<style>

  .bd-sidebar {
    grid-area: sidebar;
    background-color: white;
  }

  .sidebar {
    position: relative;
    display: flex;
    flex-direction: column;
    font-size: 14px
  }

  @media (min-width: 992px) {
    .sidebar {
      position: sticky;
      height: calc(100vh - 56px);
      width: 242px;
      top: 56px;
      overflow-y: auto
    }
  }

  @media (min-width: 1200px) {
    .sidebar {
      width: 272px
    }
  }

  .sidebar > ul {
    margin-bottom: 0
  }

  .sidebar__section-heading {
    padding-left: 24px
  }

  .sidebar__link {
    position: relative;
    padding-left: 40px;
    padding-top: 8px;
    padding-bottom: 8px;
    line-height: 1.2;
    color: var(--ds-text);
    background-color: transparent
  }

  .sidebar__link:hover {
    color: var(--ds-text);
    text-decoration: none;
    background-color: var(--ds-background-neutral-subtle-hovered)
  }

  .sidebar__link.active {
    color: var(--ds-text-selected);
    background-color: var(--ds-background-selected)
  }

  .sidebar__link.active::after {
    content: "";
    display: block;
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background-color: var(--ds-border-selected);
    z-index: 2
  }

</style>
<aside class="bd-sidebar border-end">
  <div class="sidebar">
    <div class="collapse d-lg-block py-3 py-lg-4">
      @foreach(app_sidebar() as $section)
      @if(!isset($section['hidden']) || !$section['hidden'])
      <div class="sidebar__section-heading d-block my-2 fw-semibold text-capitalize ls-wider fs-sm">
        {{ $section['section_name'] }}
      </div>
      <ul class="list-unstyled">
        @foreach($section['section_items'] as $item)
        @if(!isset($item['hidden']) || !$item['hidden'])
        <li>
          @if(isset($item['post_form']) && $item['post_form'])
            <?php $id = \Illuminate\Support\Str::random(10) ?>
          <a href="{{ $item['route'] }}"
             class="sidebar__link d-flex justify-content-between {{ isset($item['active']) && $item['active'] ? 'active' : '' }}"
             onclick="event.preventDefault();document.getElementById('{{ $id }}').submit();">
            {{ $item['label'] }}
          </a>
          <form id="{{ $id }}" action="{{ $item['route'] }}" method="POST" style="display:none">
            @csrf
          </form>
          @else
          <a href="{{ $item['route'] }}"
             class="sidebar__link d-flex justify-content-between {{ isset($item['active']) && $item['active'] ? 'active' : '' }}"
             {{ isset($item['target']) && $item['target'] ? "target='{$item['target']}'" : '' }}>
          {{ $item['label'] }}
          </a>
          @endif
        </li>
        @endif
        @endforeach
      </ul>
      @endif
      @endforeach
    </div>
  </div>
</aside>