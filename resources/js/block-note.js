import React, {useState} from 'react';
import ReactDOM from 'react-dom';
import "@blocknote/core/fonts/inter.css";
import {BlockNoteView} from "@blocknote/mantine";
import "@blocknote/mantine/style.css";
import {BlockNoteSchema, defaultBlockSpecs, filterSuggestionItems, insertOrUpdateBlock} from "@blocknote/core";
import {
  createReactBlockSpec, getDefaultReactSlashMenuItems, SuggestionMenuController, useCreateBlockNote
} from "@blocknote/react";
import {HiSparkles} from "react-icons/hi";
import {Menu} from "@mantine/core";

const ctx = {
  history: [],
};

const QaBlock = createReactBlockSpec({
  type: "qa_block", propSchema: {
    questions: {
      default: [],
    }, answers: {
      default: [],
    },
  }, content: "inline",
}, {
  render: (props) => {
    const handleChange = (event, question) => {
      const answers = [...props.block.props.answers];
      const answer = answers.find(answer => answer.question === question);
      if (answer) {
        answer.answer = event.target.value;
      } else {
        answers.push({question: question, answer: event.target.value});
      }
      props.editor.updateBlock(props.block, {type: "qa_block", props: {answers: answers}});
    };
    const handleClick = (event) => {
      const answers = props.block.props.answers;
      console.log(answers);
      const text = answers.map(answer => `${answer.question} ${answer.answer}`).join("\n");
      props.editor.insertBlocks([{type: "paragraph", content: text}], props.block, 'after');
    };
    return (<div style={{width: "100%"}}>{props.block.props.questions.map(question => {
      return (<div key={question}>
        <div style={{
          backgroundColor: "var(--ds-background-discovery)", color: "var(--ds-text-discovery)", padding: "3px"
        }}>
          {question}
        </div>
        <input type={"text"}
               style={{width: "100%", border: "none", padding: "3px", outline: "unset"}}
               onChange={(event) => handleChange(event, question)}
               placeholder={"Saisissez votre réponse ici..."}
               required>
        </input>
      </div>);
    })}
      <input type={"button"}
             value={"Regénérer..."}
             style={{backgroundColor: "#0d6efd", color: "white", border: "none", padding: "5px"}}
             onClick={handleClick}>
      </input>
    </div>);
  }
});

const AiBlock = createReactBlockSpec({
  type: "ai_block", propSchema: {
    prompt: {
      default: "AI Assistant",
    }, collections: {
      default: [],
    }, collection: {
      default: "",
    }, instructions: {
      default: null,
    },
  }, content: "inline",
}, {
  render: (props) => {
    const [loading, setLoading] = useState(false);
    const handleChange = (event) => {
      props.editor.updateBlock(props.block, {type: "ai_block", props: {instructions: event.target.value}});
    };
    const handleKeyDown = (event) => {
      if (event.key === 'Enter') {
        event.preventDefault();
        const propz = props.block.props;
        if (propz.instructions && propz.instructions.trim()) {
          setLoading(true);
          axios
          .post(`/cb/web/llm`, {collection: propz.collection, instructions: propz.instructions})
          .then(function (response) {
            if (response.data) {
              insertOrUpdateBlock(props.editor, {type: "paragraph", content: response.data});
            } else {
              console.log(response.data);
            }
          })
          .catch(error => console.log(error))
          .finally(() => setLoading(false));
        }
      }
    };
    return (
      <div style={{width: "100%", display: "flex", justifyContent: "center", alignItems: "center", flexGrow: "1"}}>
        <div style={{
          backgroundColor: "var(--ds-background-discovery)", color: "var(--ds-text-discovery)", padding: "3px"
        }}>
          @{props.block.props.prompt}&nbsp;
        </div>
        {props.block.props.collections.length > 0 && <Menu withinPortal={false} zIndex={999999}>
          <Menu.Target>
            <div style={{
              cursor: "pointer",
              backgroundColor: "var(--ds-background-information)",
              color: "var(--ds-text-information)",
              padding: "3px"
            }}>
              &nbsp;{props.block.props.collection}&nbsp;
            </div>
          </Menu.Target>
          <Menu.Dropdown>
            {props.block.props.collections.map(col => {
              return (<Menu.Item key={col} onClick={() => props.editor.updateBlock(props.block,
                {type: "ai_block", props: {collection: col}})}>{col}</Menu.Item>);
            })}
          </Menu.Dropdown>
        </Menu>}
        <input type={"text"}
               style={{flexGrow: "1", border: "none", padding: "3px", outline: "unset", minHeight: "30px"}}
               ref={props.contentRef}
               disabled={loading}
               onKeyDown={handleKeyDown}
               onChange={handleChange}
               placeholder={"Saisissez vos instructions ici..."}
               autoFocus
               required>
        </input>
        {loading && <span className="tw-loader-25"></span>}
      </div>)
  }
});

const getCustomSlashMenuItems = (editor, isSlash) => {

  const items = isSlash ? getDefaultReactSlashMenuItems(editor).filter(
    (item) => item.group !== 'Media' && item.group !== 'Others') : [];

  if (!isSlash) {
    items.push({
      group: 'Cywise',
      key: 'ai_command',
      icon: <HiSparkles size={18}/>,
      title: 'CyberBuddy',
      subtext: 'Use AI to generate paragraph',
      onItemClick: () => insertOrUpdateBlock(editor, {
        type: "ai_block", props: {
          prompt: 'CyberBuddy', collection: "anssi", collections: ["anssi", "pssi"],
        }
      }),
    });
  }
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
      getItems={async (query) => filterSuggestionItems(getCustomSlashMenuItems(editor, true), query)}
    />
    <SuggestionMenuController
      triggerCharacter={"@"}
      getItems={async (query) => filterSuggestionItems(getCustomSlashMenuItems(editor, false), query)}
    />
  </BlockNoteView>);
}

function renderBlockNote(id, settings) {
  const el = document.getElementById(id);
  if (el) {
    ctx.settings = settings;
    ctx.settings.schema = BlockNoteSchema.create({
      blockSpecs: {
        ...defaultBlockSpecs, ai_block: AiBlock, qa_block: QaBlock,
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
