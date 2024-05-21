@if(Auth::user()->canListServers())
@once
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endonce
<div class="card card-accent-secondary tw-card">
  <div class="card-header">
    <h3 class="m-0"><b>{{ __('Resources Usage') }}</b></h3>
  </div>
  @if($memory_usage->isEmpty())
  <div class="card-body">
    <div class="row">
      <div class="col">
        None.
      </div>
    </div>
  </div>
  @else
  <div class="card-body p-0">
    <script>

      const memory_charts = {};
      const memory_series = {};
      const disk_charts = {};
      const disk_series = {};
      const barChartConfig = (metrics, title, titleSerie1, titleSerie2) => {
        const latest = metrics[metrics.length - 1].timestamp;
        return {
          type: 'bar', data: {
            labels: metrics.map(metric => metric.timestamp), datasets: [{
              label: titleSerie1,
              data: metrics.map(metric => metric.used_space_gb),
              backgroundColor: "rgb(255, 159, 64)",
              borderColor: "rgb(255, 159, 64)",
            }, {
              label: titleSerie2,
              data: metrics.map(metric => metric.space_left_gb),
              backgroundColor: "rgb(54, 162, 235)",
              borderColor: "rgb(54, 162, 235)",
            }],
          }, options: {
            plugins: {
              title: {
                display: true, text: (metrics.length > 0 ? metrics[0].ynh_server_name + ' : ' : '') + title + ' at ' + latest + ' UTC',
              }
            }, scales: {
              x: {
                stacked: true, ticks: {
                  display: false, autoSkip: true, major: {
                    enabled: true,
                  },
                },
              }, y: {
                stacked: true,
              }
            }, responsive: true, maintainAspectRatio: false, legend: {position: 'bottom'},
          }
        };
      }

      @foreach($memory_usage as $serverName => $usage)
      memory_series['{{ $serverName }}'] = @json($usage);
      @endforeach
      @foreach($disk_usage as $serverName => $usage)
      disk_series['{{ $serverName }}'] = @json($usage);
      @endforeach

    </script>
    <?php $serverNames = $memory_usage->keys()->concat($disk_usage->keys())->unique()->sort() ?>
    @foreach($serverNames as $serverName)
    <?php $serverId = md5($serverName) ?>
    <div class="row p-4">
      <div class="col-6">
        <canvas id="chart-memory-{{ $serverId }}" style="height:300px;"></canvas>
        <script>
          memory_charts['{{ $serverName }}'] = new Chart(
            document.getElementById("chart-memory-{{ $serverId }}").getContext('2d'),
            barChartConfig(memory_series['{{ $serverName }}'], 'Memory Usage', 'Memory Used (Gb)', 'Memory Left (Gb)'));
        </script>
      </div>
      <div class="col-6">
        <canvas id="chart-disk-{{ $serverId }}" style="height:300px;"></canvas>
        <script>
          disk_charts['{{ $serverName }}'] = new Chart(
            document.getElementById("chart-disk-{{ $serverId }}").getContext('2d'),
            barChartConfig(disk_series['{{ $serverName }}'], 'Disk Usage', 'Disk Used (Gb)', 'Disk Left (Gb)'));
        </script>
      </div>
    </div>
    @endforeach
  </div>
  @endif
</div>
@endif