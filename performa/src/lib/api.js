// Performa API Layer
// Copyright (c) 2019 Joseph Huckaby
// Released under the MIT License

var fs = require('fs');
var assert = require("assert");
var async = require('async');

var Class = require("pixl-class");
var Tools = require("pixl-tools");
var PixlRequest = require("pixl-request");

module.exports = Class.create({
	
	__mixins: [
		require('./api/config.js'),
		require('./api/admin.js'),
		require('./api/apikey.js'),
		require('./api/alerts.js'),
		require('./api/commands.js'),
		require('./api/groups.js'),
		require('./api/monitors.js'),
		require('./api/submit.js'),
		require('./api/view.js'),
		require('./api/file.js')
	],
	
	api_ping: function(args, callback) {
		// hello
		callback({ code: 0 });
	},
	
	api_echo: function(args, callback) {
		// for testing: adds 1 second delay, echoes everything back
		setTimeout( function() {
			callback({
				code: 0,
				query: args.query || {},
				params: args.params || {},
				files: args.files || {}
			});
		}, 1000 );
	},
	
	api_stats: function(args, callback) {
		// web server stats
		callback({ code: 0, stats: this.web.getStats() });
	},
	
	api_check_user_exists: function(args, callback) {
		// checks if username is taken (used for showing green checkmark on form)
		var self = this;
		var query = args.query;
		var path = 'users/' + this.usermgr.normalizeUsername(query.username);
		
		if (!this.requireParams(query, {
			username: this.usermgr.usernameMatch
		}, callback)) return;
		
		// do not cache this API response
		this.forceNoCacheResponse(args);
		
		this.storage.get(path, function(err, user) {
			callback({ code: 0, user_exists: !!user });
		} );
	},
	
	forceNoCacheResponse: function(args) {
		// make sure this response isn't cached, ever
		args.response.setHeader( 'Cache-Control', 'no-cache, no-store, must-revalidate, proxy-revalidate' );
		args.response.setHeader( 'Expires', 'Thu, 01 Jan 1970 00:00:00 GMT' );
	},
	
	getServerBaseAPIURL: function(hostname) {
		// construct fully-qualified URL to API on specified hostname
		// use proper protocol and ports as needed
		var api_url = '';
		
		if (this.web.config.get('https') && this.web.config.get('https_force')) {
			api_url = 'https://' + hostname;
			if (this.web.config.get('https_port') != 443) api_url += ':' + this.web.config.get('https_port');
		}
		else {
			api_url = 'http://' + hostname;
			if (this.web.config.get('http_port') != 80) api_url += ':' + this.web.config.get('http_port');
		}
		api_url += this.api.config.get('base_uri');
		
		return api_url;
	},
	
	requireValidUser: function(session, user, callback) {
		// make sure user and session are valid
		// otherwise throw an API error and return false
		
		if (session && (session.type == 'api')) {
			// session is simulated, created by API key
			if (!user) {
				return this.doError('api', "Invalid API Key: " + session.api_key, callback);
			}
			if (!user.active) {
				return this.doError('api', "API Key is disabled: " + session.api_key, callback);
			}
			return true;
		} // api key
		
		if (!session) {
			return this.doError('session', "Session has expired or is invalid.", callback);
		}
		if (!user) {
			return this.doError('user', "User not found: " + session.username, callback);
		}
		if (!user.active) {
			return this.doError('user', "User account is disabled: " + session.username, callback);
		}
		return true;
	},
	
	requireAdmin: function(session, user, callback) {
		// make sure user and session are valid, and user is an admin
		// otherwise throw an API error and return false
		if (!this.requireValidUser(session, user, callback)) return false;
		
		if (session.type == 'api') {
			// API Keys cannot be admins
			return this.doError('api', "API Key cannot use administrator features", callback);
		}
		
		if (!user.privileges.admin) {
			return this.doError('user', "User is not an administrator: " + session.username, callback);
		}
		
		return true;
	},
	
	requirePrivilege: function(user, priv_id, callback) {
		// make sure user has the specified privilege
		// otherwise throw an API error and return false
		if (user.privileges.admin) return true; // admins can do everything
		if (user.privileges[priv_id]) return true;
		
		if (user.key) {
			return this.doError('api', "API Key ('"+user.title+"') does not have the required privileges to perform this action ("+priv_id+").", callback);
		}
		else {
			return this.doError('user', "User '"+user.username+"' does not have the required account privileges to perform this action ("+priv_id+").", callback);
		}
	},
	
	getClientInfo: function(args, params) {
		// proxy over to user module
		// var info = this.usermgr.getClientInfo(args, params);
		var info = null;
		if (params) info = Tools.copyHash(params, true);
		else info = {};
		
		info.ip = args.ip;
		info.headers = args.request.headers;
		
		// augment with our own additions
		if (args.admin_user) info.username = args.admin_user.username;
		else if (args.user) {
			if (args.user.key) {
				// API Key
				info.api_key = args.user.key;
				info.api_title = args.user.title;
			}
			else {
				info.username = args.user.username;
			}
		}
		
		return info;
	},
	
	loadSession: function(args, callback) {
		// Load user session or validate API Key
		var self = this;
		var session_id = args.cookies['session_id'] || args.request.headers['x-session-id'] || args.params.session_id || args.query.session_id;
		
		if (session_id) {
			this.logDebug(9, "Found Session ID: " + session_id);
			
			this.storage.get('sessions/' + session_id, function(err, session) {
				if (err) return callback(err, null, null);
				
				// also load user
				self.storage.get('users/' + self.usermgr.normalizeUsername(session.username), function(err, user) {
					if (err) return callback(err, null, null);
					
					// set type to discern this from API Key sessions
					session.type = 'user';
					
					// get session_id out of args.params, so it doesn't interfere with API calls
					delete args.params.session_id;
					
					// pass both session and user to callback
					callback(null, session, user);
				} );
			} );
			return;
		}
		
		// no session found, look for API Key
		var api_key = args.request.headers['x-api-key'] || args.params.api_key || args.query.api_key;
		if (!api_key) return callback( new Error("No Session ID or API Key could be found"), null, null );
		
		this.logDebug(9, "Found API Key: " + api_key);
		
		this.storage.listFind( 'global/api_keys', { key: api_key }, function(err, item) {
			if (err) return callback(new Error("API Key is invalid: " + api_key), null, null);
			
			// create simulated session and user objects
			var session = {
				type: 'api',
				api_key: api_key
			};
			var user = item;
			
			// get api_key out of args.params, so it doesn't interfere with API calls
			delete args.params.api_key;
			
			// pass both "session" and "user" to callback
			callback(null, session, user);
		} );
		return;
	},
	
	requireParams: function(params, rules, callback) {
		// proxy over to user module
		assert( arguments.length == 3, "Wrong number of arguments to requireParams" );
		return this.usermgr.requireParams(params, rules, callback);
	},
	
	doError: function(code, msg, callback) {
		// proxy over to user module
		assert( arguments.length == 3, "Wrong number of arguments to doError" );
		return this.usermgr.doError( code, msg, callback );
	}
	
});
