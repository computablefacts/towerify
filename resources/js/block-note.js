import React from 'react';
import ReactDOM from 'react-dom';
import "@blocknote/core/fonts/inter.css";
import {BlockNoteView} from "@blocknote/mantine";
import "@blocknote/mantine/style.css";
import {useCreateBlockNote} from "@blocknote/react";

function BlockNoteElement() {
  const editor = useCreateBlockNote();
  return (<BlockNoteView editor={editor}/>);
}

function renderBlockNote(id) {
  const el = document.getElementById(id);
  if (el) {
    ReactDOM.render(<BlockNoteElement/>, el);
  }
}

const BlockNote = {
  render: renderBlockNote,
};

export {
  BlockNote as default
};
