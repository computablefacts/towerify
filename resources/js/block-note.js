import React, {useEffect, useRef} from 'react';
import ReactDOM from 'react-dom';
import "@blocknote/core/fonts/inter.css";
import {BlockNoteView} from "@blocknote/mantine";
import "@blocknote/mantine/style.css";
import {BlockNoteSchema, defaultBlockSpecs, filterSuggestionItems, insertOrUpdateBlock} from "@blocknote/core";
import {
  createReactBlockSpec, getDefaultReactSlashMenuItems, SuggestionMenuController, useCreateBlockNote
} from "@blocknote/react";
import {HiSparkles} from "react-icons/hi";

const ctx = {};

const AiBlock = createReactBlockSpec({
  type: "ai_block", propSchema: {
    prompt: {
      default: "AI Assistant",
    },
  }, content: "inline",
}, {
  render: (props) => {
    const inputRef = useRef(null);
    useEffect(() => {
      if (inputRef.current) {
        inputRef.current.focus();
      }
    }, []);
    const handleKeyDown = (event) => {
      if (event.key === 'Enter') {
        event.preventDefault();
        const block = props.block;
        const text = inputRef.current.value;
        console.log(block, text);
        axios.post(`/cb/web/llm`, {collection: 'anssi', instruction: text})
        .then(function (response) {
          if (response.data) {
            insertOrUpdateBlock(props.editor, {type: "paragraph", content: response.data});
            props.editor.deleteBlock(block.id);
          } else {
            toaster.toastError(response.data);
          }
        }).catch(error => toaster.toastAxiosError(error));
      }
    };
    return (
      <div style={{width: "100%", display: "flex", justifyContent: "center", alignItems: "center", flexGrow: "1"}}>
        <div style={{backgroundColor: "#0194ff", color: "white", padding: "3px"}}>
          @{props.block.props.prompt}
        </div>
        <input type={"text"}
               style={{flexGrow: "1", border: "none", padding: "3px"}}
               ref={inputRef}
               onKeyDown={handleKeyDown}>
        </input>
      </div>)
  }
});

const getCustomSlashMenuItems = (editor) => {

  const items = getDefaultReactSlashMenuItems(editor).filter(
    (item) => item.group !== 'Media' && item.group !== 'Others');

  items.push({
    group: 'Cywise',
    key: 'ai_command',
    icon: <HiSparkles size={18}/>,
    title: 'CyberBuddy',
    subtext: 'Use AI to generate paragraph',
    onItemClick: () => insertOrUpdateBlock(editor, {type: "ai_block", props: {prompt: 'CyberBuddy'}}),
  });

  return items;
};

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
    ctx.settings.schema = BlockNoteSchema.create({
      blockSpecs: {
        ...defaultBlockSpecs, ai_block: AiBlock,
      },
    });
    ReactDOM.render(<BlockNoteElement/>, el);
  }
}

const BlockNote = {
  render: renderBlockNote,
};

export {
  BlockNote as default
};
