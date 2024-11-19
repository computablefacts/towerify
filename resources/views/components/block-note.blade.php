<div id="block-note"></div>
<script>
  window.addEventListener('load', () => {
    window.BlockNote.render("block-note", {});
    window.BlockNote.observers = new com.computablefacts.observers.Subject();
    window.BlockNote.observers.register('template-change', template => {
      window.BlockNote.ctx.editor.replaceBlocks(window.BlockNote.ctx.blocks /* old */, template.template /* new */);
    });
  });
</script>