#!/usr/bin/php
<?php

$dir = dirname(__FILE__);
$target = time().'.csv';

if(isset($argv[1])){
	$dir = $argv[1];
}
if(isset($argv[2])){
	$target = $argv[2];
}

// Things are written into 'merged' directory.
if(!file_exists('merged')){
	mkdir('merged');
}

// Filtering the CSV files.
$csv_files = scandir($dir);
$csv_files = array_filter($csv_files, function($csv_file){
	// Only .csv shall be picked
	return preg_match('/\.csv$/', $csv_file);
});

// Merging now.
$fh_merged = fopen("merged/$target", 'w');

// Write header
fwrite($fh_merged, "ACCOUNT,PAYEE_ITEM_DESC,CATEGORY,AMOUNT,STATUS,TRAN_DATE,REMARKS\n");

foreach($csv_files as $csv_file){

	// Extracting account name from AccountName_Currency.csv.	
	$account = $csv_file;
	$account = ucfirst(preg_replace('/_[[:alpha:]]+\.csv$/', '', $account));
	$account = str_replace('_', ' ', $account);
	
	print "Merging '$account'..\n";
	
	$fh = fopen($csv_file, 'r');
	
	while(!feof($fh)){
	
		$buffer = fgets($fh);
		
		// Exclude headers
		if(preg_match('/^PAYEE_ITEM_DESC/', $buffer)){
			continue;
		}
		
		// Prepend the account name
		$buffer = <<<ROW
"$account",$buffer
ROW;
		
		// Replace date
		$pattern_date = '/\d{1,2} [A-Z][a-z]{2} \d{4}/';
		preg_match($pattern_date, $buffer, $matches);
		// If it's an empty match, this is not a valid entry as date is always there.
		if(empty($matches)){
			continue;
		}
		foreach($matches as $match){
			$buffer = str_replace($match, date('n/j/Y', strtotime($match)), $buffer);
		}
		fwrite($fh_merged, "$buffer");
	}
	fclose($fh);
}

fclose($fh_merged);

?>
