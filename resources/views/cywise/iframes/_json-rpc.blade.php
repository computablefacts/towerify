@once
<script>

  const onSuccessDefault = (result) => {
    if (toaster && result.msg) {
      toaster.toastSuccess(result.msg);
    }
  };

  const onErrorDefault = (error) => {
    if (toaster && error.message) {
      toaster.toastError(error.message);
    }
  };

  const onFinallyDefault = () => {
    //
  };

  function executeJsonRpcApiCall(method, params = {}, onSuccess = onSuccessDefault, onError = onErrorDefault,
    onFinally = onFinallyDefault) {
    axios.post('/api/v2/private/endpoint', {
      jsonrpc: "2.0", id: "1", method: method, params: params,
    }, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Accept-Encoding': 'gzip',
        'Authorization': 'Bearer {{ Auth::user()->sentinelApiToken() }}',
      }
    })
    .then(response => {
      if (response.data && response.data.error && onError) {
        onError(response.data.error);
      } else if (response.data && response.data.result && onSuccess) {
        onSuccess(response.data.result);
      } else {
        console.log(response);
      }
    })
    .catch(error => {
      if (toaster) {
        toaster.toastAxiosError(error);
      }
    })
    .finally(() => {
      if (onFinally) {
        onFinally();
      }
    });
  }

  function createInvitationsApiCall(users, onFinally = onFinallyDefault) {
    executeJsonRpcApiCall('invitations@create', {users: users}, onSuccessDefault, onErrorDefault, onFinally);
  }

  function createInvitationApiCall(fullname, email, onSuccess = onSuccessDefault) {
    executeJsonRpcApiCall('invitations@create', {fullname: fullname, email: email}, onSuccess);
  }

  function sendInvitationApiCall(invitationId) {
    executeJsonRpcApiCall('invitations@send', {id: invitationId});
  }

  function pullServerInfosApiCall(serverId, onFinally = onFinallyDefault) {
    executeJsonRpcApiCall('servers@pullServerInfos', {server_id: serverId}, onSuccessDefault, onErrorDefault,
      onFinally);
  }

  function testSshConnectionApiCall(serverId, ip, port, username) {
    executeJsonRpcApiCall('servers@testSshConnection', {server_id: serverId, ip: ip, port: port, username: username});
  }

  function configureServerApiCall(serverId, serverName, domain, ip, port, username) {
    executeJsonRpcApiCall('servers@configure',
      {server_id: serverId, name: serverName, domain: domain, ip: ip, port: port, username: username});
  }

  function deleteServerApiCall(serverId) {
    executeJsonRpcApiCall('servers@delete', {server_id: serverId});
  }

  function executeShellCommandApiCall(serverId, cmd, onSuccess = onSuccessDefault) {
    executeJsonRpcApiCall('servers@executeShellCommand', {server_id: serverId, cmd: cmd}, onSuccess);
  }

  function getServerMessagesApiCall(serverId) {
    executeJsonRpcApiCall('servers@messages', {server_id: serverId});
  }

  function installAppApiCall(serverId, orderId) {
    executeJsonRpcApiCall('applications@installApp', {server_id: serverId, order_id: orderId});
  }

  function uninstallAppApiCall(serverId, applicationId) {
    executeJsonRpcApiCall('applications@uninstallApp', {server_id: serverId, application_id: applicationId});
  }

  function addUserPermissionApiCall(serverId, userId, permission) {
    executeJsonRpcApiCall('applications@addUserPermission',
      {server_id: serverId, user_id: userId, permission: permission});
  }

  function removeUserPermissionApiCall(serverId, userId, permission) {
    executeJsonRpcApiCall('applications@removeUserPermission',
      {server_id: serverId, user_id: userId, permission: permission});
  }

  function addTowerifyUserPermissionApiCall(serverId, userId, permission) {
    executeJsonRpcApiCall('applications@addTowerifyUserPermission',
      {server_id: serverId, user_id: userId, permission: permission});
  }

  function createNoteApiCall(note, onSuccess = onSuccessDefault) {
    executeJsonRpcApiCall('notes@create', {note: note}, onSuccess);
  }

  function deleteNoteApiCall(noteId, onSuccess = onSuccessDefault) {
    executeJsonRpcApiCall('notes@delete', {note_id: noteId}, onSuccess);
  }

  function toggleVulnerabilityVisibilityApiCall(uid, type, title) {
    executeJsonRpcApiCall('vulnerabilities@toggleVisibility', {uid: uid, type: type, title: title});
  }

  function dismissEventApiCall(eventId) {
    executeJsonRpcApiCall('events@dismiss', {event_id: eventId});
  }

</script>
@endonce