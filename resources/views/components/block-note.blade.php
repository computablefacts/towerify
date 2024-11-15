<div id="block-note"></div>
<script>
  window.addEventListener('load', function () {
    const settings = {
      initialContent: [{
        type: "paragraph", content: "Welcome to this demo!", props: {
          "textColor": "default", "backgroundColor": "yellow", "textAlignment": "left"
        },
      }, {
        type: "heading", content: "This is a heading block",
      }, {
        type: "paragraph", content: "This is a paragraph block", props: {
          "textColor": "red", "backgroundColor": "default", "textAlignment": "left"
        },
      }, {
        type: "paragraph",
      },],
    };
    window.BlockNote.render("block-note", settings);
  });
</script>