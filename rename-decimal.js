#!/usr/bin/env node 

/**
 * Simple script to rename screwed up file names (caused by rdiff-backup). 
 *
 * Usage: ./rename-decimal.js DIR
 *
 * @author yclian
 * @since 20120221
 * @version 20120221
 */

var _ = require('underscore');
var fs = require('fs');

var recursive_readdir = function(p){

	console.log('> ' + p);

	_.each(fs.readdirSync(p), function(f){
	
		var fp = p + '/' + f;
		fp = fp.replace(/\/\//g, '/');
		
		if(fs.statSync(fp).isDirectory()){ 
			recursive_readdir(fp);
		}
		
		if(fp.match(/;\d{3}/)){
			var fp2 = p + '/' + f.replace(/;(\d{3})/g, function(){
				return String.fromCharCode(arguments[1]);
			});
			fp2 = fp2.replace(/\/\//g, '/');
			fs.renameSync(fp, fp2);
			console.log("'" + fp + "' => '" + fp2 + "'");
		}
	});
	
	console.log('< ' + p);
}

recursive_readdir(process.argv[2]);
