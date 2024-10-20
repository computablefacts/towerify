<div class="container">
  <div class="row">
    <div class="col">
      <div class="alert alert-danger border border-danger">
        {{ __('Your trial ended on :date.', ['date' => Auth::user()->endOfTrial()->format('Y-m-d')]) }}
      </div>
    </div>
  </div>
</div>