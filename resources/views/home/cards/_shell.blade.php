@if(Auth::user()->canManageServers())
<style>

  :root {
    --shell-margin: 25px;
  }

  #shell {
    background: #222;
    box-shadow: 0 0 5px rgba(0, 0, 0, .3);
    font-size: 10pt;
    display: flex;
    flex-direction: column;
    align-items: stretch;
    max-width: calc(100vw - 2 * var(--shell-margin));
    /* max-height: calc(100vh - 2 * var(--shell-margin)); */
    max-height: 400px;
    resize: both;
    overflow: hidden;
    width: 100%;
    height: 100%;
    margin: var(--shell-margin) auto;
  }

  #shell-content {
    overflow: auto;
    padding: 5px;
    white-space: pre-wrap;
    flex-grow: 1;
    color: white;
  }

  #shell-logo {
    font-weight: bold;
    color: #FF4180;
    text-align: center;
  }

  .shell-prompt {
    font-weight: bold;
    color: #75DF0B;
  }

  .shell-prompt > span {
    color: #1BC9E7;
  }

  #shell-input {
    display: flex;
    box-shadow: 0 -1px 0 rgba(0, 0, 0, .3);
    border-top: rgba(255, 255, 255, .05) solid 1px;
    padding: 10px 0;
  }

  #shell-input > label {
    flex-grow: 0;
    display: block;
    padding: 0 5px;
    height: 30px;
    line-height: 30px;
  }

  #shell-input #shell-cmd {
    height: 30px;
    line-height: 30px;
    border: none;
    background: transparent;
    color: #eee;
    font-family: monospace;
    font-size: 10pt;
    width: 100%;
    align-self: center;
    box-sizing: border-box;
  }

  #shell-input div {
    flex-grow: 1;
    align-items: stretch;
  }

  #shell-input input {
    outline: none;
  }

</style>
<div class="card card-accent-secondary tw-card">
  <div class="card-header">
    <h3 class="m-0"><b>{{ __('Shell') }}</b></h3>
  </div>
  <div class="p-3" style="background-color:#fff3cd;">
    {{ __('This shell does not allow you to execute interactive commands. Shell commands cannot exceed 255 characters.')
    }}
  </div>
  <div class="card-body">
    <div id="shell">
      <pre id="shell-content">
        <div id="shell-logo">
          {{ $server->name }} - {{ $server->ip() }}<span></span>
          Logged in as {{ $server->ssh_username }}!<span></span>
        </div>
      </pre>
      <div id="shell-input">
        <label for="shell-cmd" id="shell-prompt" class="shell-prompt">???</label>
        <div>
          <input id="shell-cmd" name="cmd" onkeydown="_onShellCmdKeyDown(event)"/>
        </div>
      </div>
    </div>
  </div>
</div>
<script>

  const loadingIndicator = 'Running...';
  const commandHistory = [];
  let historyPosition = 0;
  let eShellCmdInput = null;
  let eShellContent = null;

  function genPrompt() {
    return "{{ $server->ssh_username }}" + "@" + "{{ $server->ip() }}:<span title=\"\">~</span>#";
  }

  function escapeHtml(string) {
    return string
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;");
  }

  function _insertCommand(command) {
    eShellContent.innerHTML += "\n\n";
    eShellContent.innerHTML += '<span class=\"shell-prompt\">' + genPrompt() + '</span> ';
    eShellContent.innerHTML += escapeHtml(command);
    eShellContent.innerHTML += "\n" + loadingIndicator;
    eShellContent.scrollTop = eShellContent.scrollHeight;
  }

  function _insertStdout(stdout) {
    eShellContent.innerHTML = eShellContent.innerHTML.substring(0,
      eShellContent.innerHTML.lastIndexOf(loadingIndicator)) + escapeHtml(stdout);
    eShellContent.scrollTop = eShellContent.scrollHeight;
  }

  function _updatePrompt() {
    const eShellPrompt = document.getElementById("shell-prompt");
    eShellPrompt.innerHTML = genPrompt();
  }

  function featureShell(command) {

    _insertCommand(command);

    if (/^\s*clear\s*$/.test(command)) {
      eShellContent.innerHTML = `
        <div id=\"shell-logo\">
          {{ $server->name }} - {{ $server->ip() }}<span></span>
          {{ Auth::user()->ynhUsername() }}<span></span>
        </div>
      `;
    } else {
      axios.post(`/ynh/servers/{{ $server->id }}/execute-shell-command`, {
        cmd: command
      }).then(function (data) {
        _insertStdout(data.data.success.join('\n'));
      }).catch(error => {
        // TODO
      }).finally(() => {
        // TODO
      });
    }
  }

  function insertToHistory(cmd) {
    commandHistory.push(cmd);
    historyPosition = commandHistory.length;
  }

  function _onShellCmdKeyDown(event) {
    switch (event.key) {
      case "Enter": {
        event.preventDefault();
        if (eShellCmdInput.value) {
          featureShell(eShellCmdInput.value);
          insertToHistory(eShellCmdInput.value);
        }
        eShellCmdInput.value = "";
        break;
      }
      case "ArrowUp": {
        event.preventDefault();
        if (historyPosition > 0) {
          historyPosition--;
          eShellCmdInput.blur();
          eShellCmdInput.focus();
          eShellCmdInput.value = commandHistory[historyPosition];
        }
        break;
      }
      case "ArrowDown": {
        event.preventDefault();
        if (historyPosition >= commandHistory.length) {
          break;
        }
        historyPosition++;
        if (historyPosition === commandHistory.length) {
          eShellCmdInput.value = "";
        } else {
          eShellCmdInput.blur();
          eShellCmdInput.focus();
          eShellCmdInput.value = commandHistory[historyPosition];
        }
        break;
      }
    }
  }

  eShellCmdInput = document.getElementById("shell-cmd");
  eShellContent = document.getElementById("shell-content");
  _updatePrompt();
  eShellCmdInput.focus();

</script>
@endif