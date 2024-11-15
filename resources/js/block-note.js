import React from 'react';
import ReactDOM from 'react-dom';
import "@blocknote/core/fonts/inter.css";
import {BlockNoteView} from "@blocknote/mantine";
import "@blocknote/mantine/style.css";
import {filterSuggestionItems,} from "@blocknote/core";
import {getDefaultReactSlashMenuItems, SuggestionMenuController, useCreateBlockNote} from "@blocknote/react";

const ctx = {};

const getCustomSlashMenuItems = (editor) => [...getDefaultReactSlashMenuItems(editor).filter(
  function (item) {
    // console.log(item)
    return item.group !== 'Media' && item.group !== 'Others';
}),];

function BlockNoteElement() {
  const editor = useCreateBlockNote(ctx.settings);
  return (<BlockNoteView
    editor={editor}
    slashMenu={false}
  >
    <SuggestionMenuController
      triggerCharacter={"/"}
      getItems={async (query) => filterSuggestionItems(getCustomSlashMenuItems(editor), query)}
    />
  </BlockNoteView>);
}

function renderBlockNote(id, settings) {
  const el = document.getElementById(id);
  if (el) {
    ctx.settings = settings;
    ReactDOM.render(<BlockNoteElement/>, el);
  }
}

const BlockNote = {
  render: renderBlockNote,
};

export {
  BlockNote as default
};
