#!/usr/bin/env node

// Build Tools Module -- included by build.js.
// Copies, symlinks and compresses files into the right locations.
// Can also compact & bundle JS/CSS together for distribution.
// Copyright (c) 2015 Joseph Huckaby, MIT License.

var path = require('path');
var fs = require('fs');
var zlib = require('zlib');
var util = require('util');
var os = require('os');
var cp = require('child_process');

var async = require('async');
var UglifyJS = require("uglify-js");
var Tools = require('pixl-tools');

var mkdirp = Tools.mkdirp;
var glob = Tools.glob;

var fileStatSync = function(file) {
	// no-throw version of fs.statSync
	var stats = null;
	try { stats = fs.statSync(file); }
	catch (err) { return false; }
	return stats;
};

var fileExistsSync = function(file) {
	// replacement for fs.existsSync which is being removed
	return !!fileStatSync(file);
};

var symlinkFile = exports.symlinkFile = function symlinkFile( old_file, new_file, callback ) {
	// create symlink to file
	// Note: 'new_file' is the object that will be created on the filesystem
	// 'old_file' should already exist, and is the file being pointed to
	if (new_file.match(/\/$/)) new_file += path.basename(old_file);
	
	// if target exists and is not a symlink, skip this
	try {
		var stats = fs.lstatSync(new_file);
		if (!stats.isSymbolicLink()) return callback();
	}
	catch (e) {;}
	
	console.log( "Symlink: " + old_file + " --> " + new_file );
	
	try { fs.unlinkSync( new_file ); }
	catch (e) {;}
	
	if (!fileExistsSync( path.dirname(new_file) )) {
		mkdirp.sync( path.dirname(new_file) );
	}
	
	// fs.symlink takes a STRING (not a file path per se) as the old (existing) file, 
	// and it needs to be relative from new_file.  So we need to resolve some things.
	var sym_new = path.resolve( new_file );
	var sym_old = path.relative( path.dirname(sym_new), path.resolve(old_file) );
	
	fs.symlink( sym_old, sym_new, callback );
};

var copyFile = exports.copyFile = function copyFile( old_file, new_file, callback ) {
	// copy file
	if (new_file.match(/\/$/)) new_file += path.basename(old_file);
	console.log( "Copy: " + old_file + " --> " + new_file );
	
	try { fs.unlinkSync( new_file ); }
	catch (e) {;}
	
	if (!fileExistsSync( path.dirname(new_file) )) {
		mkdirp.sync( path.dirname(new_file) );
	}
	
	var inp = fs.createReadStream(old_file);
	var outp = fs.createWriteStream(new_file);
	inp.on('end', callback );
	inp.pipe( outp );
};

var copyFiles = exports.copyFiles = function copyFiles( src_spec, dest_dir, callback ) {
	// copy multiple files to destination directory using filesystem globbing
	dest_dir = dest_dir.replace(/\/$/, '');
	
	glob(src_spec, {}, function (err, files) {
		// got files
		if (files && files.length) {
			async.eachSeries( files, function(src_file, callback) {
				// foreach file
				var stats = fileStatSync(src_file);
				if (stats && stats.isFile()) {
					copyFile( src_file, dest_dir + '/', callback );
				}
				else callback();
			}, callback );
		} // got files
		else {
			callback( err || new Error("No files found matching: " + src_spec) );
		}
	} );
};

var compressFile = exports.compressFile = function compressFile( src_file, gz_file, callback ) {
	// gzip compress file
	console.log( "Compress: " + src_file + " --> " + gz_file );
	
	if (fileExistsSync(gz_file)) {
		fs.unlinkSync( gz_file );
	}
	
	var gzip = zlib.createGzip();
	var inp = fs.createReadStream( src_file );
	var outp = fs.createWriteStream( gz_file );
	inp.on('end', callback );
	inp.pipe(gzip).pipe(outp);
};

var copyCompress = exports.copyCompress = function copyCompress( old_file, new_file, callback ) {
	// copy file and create gzip version as well
	if (new_file.match(/\/$/)) new_file += path.basename(old_file);
	
	copyFile( old_file, new_file, function(err) {
		if (err) return callback(err);
		
		// Make a compressed copy, so node-static will serve it up to browsers
		compressFile( old_file, new_file + '.gz', callback );
	} );
};

var symlinkCompress = exports.symlinkCompress = function symlinkCompress( old_file, new_file, callback ) {
	// symlink file and create gzip version as well
	if (new_file.match(/\/$/)) new_file += path.basename(old_file);
	
	symlinkFile( old_file, new_file, function(err) {
		if (err) return callback(err);
		
		// Make a compressed copy, so node-static will serve it up to browsers
		compressFile( old_file, new_file + '.gz', callback );
	} );
};

var deleteFile = exports.deleteFile = function deleteFile( file, callback ) {
	// delete file
	console.log( "Delete: " + file );
	
	if (fileExistsSync(file)) {
		fs.unlink( file, callback );
	}
	else callback();
};

var deleteFiles = exports.deleteFiles = function deleteFiles( spec, callback ) {
	// delete multiple files using filesystem globbing
	glob(spec, {}, function (err, files) {
		// got files
		if (files && files.length) {
			async.eachSeries( files, function(file, callback) {
				// foreach file
				deleteFile( file, callback );
			}, callback );
		} // got files
		else {
			callback( err );
		}
	} );
};

var chmodFiles = exports.chmodFiles = function chmodFiles( mode, spec, callback ) {
	// chmod multiple files to specified mode using filesystem globbing
	glob(spec, {}, function (err, files) {
		// got files
		if (files && files.length) {
			async.eachSeries( files, function(file, callback) {
				// foreach file
				fs.chmod( file, mode, callback );
			}, callback );
		} // got files
		else {
			callback( err || new Error("No files found matching: " + spec) );
		}
	} );
};

var copyDir = exports.copyDir = function copyDir( src_dir, dest_dir, exclusive, callback ) {
	// recursively copy dir and contents, optionally with exclusive mode
	// symlinks are followed, and the target is copied instead
	var src_spec = src_dir + '/*';
	
	// exclusive means skip if dest exists (do not replace)
	if (exclusive && fileExistsSync(dest_dir)) return callback();
	
	mkdirp.sync( dest_dir );
	
	glob(src_spec, {}, function (err, files) {
		// got files
		if (files && files.length) {
			async.eachSeries( files, function(src_file, callback) {
				// foreach file
				var stats = fs.statSync(src_file);
				
				if (stats.isFile()) {
					copyFile( src_file, dest_dir + '/', callback );
				}
				else if (stats.isDirectory()) {
					copyDir( src_file, dest_dir + '/' + path.basename(src_file), exclusive, callback );
				}
			}, callback );
		} // got files
		else {
			callback( err );
		}
	} );
};

var bundleCompress = exports.bundleCompress = function bundleCompress( args, callback ) {
	// compress bundle of files
	var html_file = args.html_file;
	var html_dir = path.dirname( html_file );
	
	if (!fileExistsSync( path.dirname(args.dest_bundle) )) {
		mkdirp.sync( path.dirname(args.dest_bundle) );
	}
	
	var raw_html = fs.readFileSync( html_file, 'utf8' );
	var regexp = new RegExp( "\\<\\!\\-\\-\\s+BUILD\\:\\s*"+args.match_key+"_START\\s*\\-\\-\\>([^]+)\\<\\!\\-\\-\\s*BUILD\\:\\s+"+args.match_key+"_END\\s*\\-\\-\\>" );
	
	if (raw_html.match(regexp)) {
		var files_raw = RegExp.$1;
		var files = [];
		
		files_raw.replace( /\b(src|href)\=[\"\']([^\"\']+)[\"\']/ig, function(m_all, m_g1, m_g2) {
			files.push( path.join(html_dir, m_g2) );
		} );
		
		if (files.length) {
			console.log("Bundling files: ", files);
			var raw_output = '';
			
			// optionally add file header
			if (args.header) {
				raw_output += args.header.trim() + "\n";
			}
			
			if (args.uglify) {
				console.log("Running UglifyJS...");
				var result = UglifyJS.minify( files );
				if (!result || !result.code) return callback( new Error("Failed to bundle script: Uglify failure") );
				raw_output += result.code;
			}
			else {
				for (var idx = 0, len = files.length; idx < len; idx++) {
					var file = files[idx];
					raw_output += fs.readFileSync( file, 'utf8' ) + "\n";
				}
			}
			
			// optionally strip source map links
			//     /*# sourceMappingURL=materialdesignicons.min.css.map */
			if (args.strip_source_maps) {
				raw_output = raw_output.replace(/sourceMappingURL\=\S+/g, "");
			}
			
			// write out our bundle
			console.log(" --> " + args.dest_bundle);
			fs.writeFileSync(args.dest_bundle, raw_output);
			
			// swap a ref link into a copy of the HTML
			console.log(" --> " + html_file );
			raw_html = raw_html.replace( regexp, args.dest_bundle_tag );
			fs.writeFileSync(html_file, raw_html);
			
			// now make a compressed version of the bundle
			compressFile( args.dest_bundle, args.dest_bundle + '.gz', function(err) {
				if (err) return callback(err);
				
				// and compress the final HTML as well
				compressFile( html_file, html_file + '.gz', callback );
			});
			
		} // found files
		else {
			callback( new Error("Could not locate any file references: " + args.src_html + ": " + args.match_key) );
		}
	}
	else {
		callback( new Error("Could not locate match in HTML source: " + args.src_html + ": " + args.match_key) );
	}
};

var generateSecretKey = exports.generateSecretKey = function generateSecretKey( args, callback ) {
	// generate random secret key for a specified JSON config file
	// use regular expression to preserve natural file format
	var file = args.file;
	var key = args.key;
	var regex = new RegExp('(\\"'+Tools.escapeRegExp(key)+'\\"\\s*\\:\\s*\\")(.*?)(\\")');
	var secret_key = Tools.generateUniqueID(32);
	
	fs.readFile(file, 'utf8', function(err, text) {
		if (err) return callback(err);
		if (!text.match(regex)) return callback( new Error("Could not locate key to replace: " + file + ": " + key) );
		
		text = text.replace(regex, '$1' + secret_key + '$3');
		fs.writeFile( file, text, callback );
	});
};

var addToServerStartup = exports.addToServerStartup = function addToServerStartup( args, callback ) {
	// add service to init.d 
	// (RedHat and Ubuntu only -- do nothing on OS X)
	var src_file = args.src_file;
	var dest_dir = args.dest_dir;
	var service_name = args.service_name;
	var dest_file = dest_dir + '/' + service_name;
	
	// shell command that activates service on redhat or ubuntu
	var cmd = 'chkconfig '+service_name+' on || update-rc.d '+service_name+' defaults';
	
	// skip on os x, and if init dir is missing
	if (os.platform() == 'darwin') return callback();
	if (!fileExistsSync(dest_dir)) return callback( new Error("Cannot locate init.d directory: " + dest_dir) );
	if (process.getuid() != 0) return callback( new Error("Must be root to add services to server startup system.") );
	
	// copy file into place
	copyFile( src_file, dest_file, function(err) {
		if (err) return callback(err);
		
		// must be executable
		fs.chmod( dest_file, "775", function(err) {
			if (err) return callback(err);
			
			// exec shell command
			cp.exec( cmd, callback );
		} );
	} );
};

var printMessage = exports.printMessage = function printMessage( args, callback ) {
	// output a message to the console
	// use process.stdout.write because console.log has been redirected to a file.
	process.stdout.write( "\n" + args.lines.join("\n") + "\n\n" );
};
