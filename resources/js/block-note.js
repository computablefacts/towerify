import React from 'react';
import ReactDOM from 'react-dom';
import "@blocknote/core/fonts/inter.css";
import {BlockNoteView} from "@blocknote/mantine";
import "@blocknote/mantine/style.css";
import {useCreateBlockNote} from "@blocknote/react";

const ctx = {};

function BlockNoteElement() {
  const editor = useCreateBlockNote(ctx.settings);
  return (<BlockNoteView editor={editor}/>);
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
