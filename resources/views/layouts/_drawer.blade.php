<style>

  .bp4-portal {
    z-index: 2000 !important;
  }

</style>
<div id="drawer"></div>
<script>

  const drawer = {
    el: new com.computablefacts.blueprintjs.MinimalDrawer(document.getElementById('drawer'), '33%'),
    redraw: null,
    render: null
  };
  drawer.el.onOpen(el => {
    // console.log(drawer);
    const div = document.createElement('div');
    div.innerHTML = drawer.render ? drawer.render() : '';
    el.appendChild(div);
    drawer.redraw = () => div.innerHTML = drawer.render ? drawer.render() : '';
  });
  drawer.el.onClose(() => {
    drawer.redraw = null;
    drawer.render = null;
  });

</script>