@if(Auth::user()->canListServers())
@once
<script src="https://cdnjs.cloudflare.com/ajax/libs/cytoscape/3.29.2/cytoscape.min.js"></script>
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://cytoscape.org/cytoscape.js-popper/cytoscape-popper.js"></script>
@endonce
<div class="card card-accent-secondary tw-card mt-4">
  <div class="card-header">
    <h3 class="m-0"><b>{{ __('Interdependencies') }}</b></h3>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col" id="cy" style="width:100%;height:800px">

      </div>
    </div>
  </div>
</div>
<style>
  .popper-div {
    background-color: white;
    padding: 1em;
    border: 1px solid #00264b;
  }
</style>
<script>

  const data = <?php echo json_encode($interdependencies) ?>;

  const cy = cytoscape({
    container: document.getElementById('cy'), elements: data,

    style: [{
      selector: 'node', style: {
        'background-color': 'data(color)', 'label': 'data(label)'
      }
    }, {
      selector: 'edge', style: {
        'width': 3,
        'line-color': '#ccc',
        'target-arrow-color': '#ccc',
        'target-arrow-shape': 'triangle',
        'curve-style': 'bezier',
      }
    }, {
      selector: 'edge.hidden', style: {
        'visibility': 'hidden',
      }
    }], layout: {
      name: 'circle',
    }, userZoomingEnabled: false,
  });

  cy.on('vclick', 'node', (event) => {
    event.target.connectedEdges().toggleClass('hidden');
  });

  cy.on('vmousedown', 'edge', (event) => {
    const edge = event.target.data();
    event.target.popperRef = cy.getElementById(edge.id).popper({
      content: () => {
        const div = document.createElement('div');
        div.classList.add('popper-div');
        div.innerHTML = `<div>${edge.services.join('</div><div>')}</div>`;
        document.body.appendChild(div);
        return div;
      },
    });
  });

  cy.on('vmouseup', 'edge', function (event) {
    if (event.target.popperRef) {
      event.target.popperRef.state.elements.popper.remove();
      event.target.popperRef.destroy();
    }
  });

</script>
@endif