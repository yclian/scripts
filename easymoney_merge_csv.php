#!/usr/bin/php
<?php

/*
 * Copyright 2010 Yuen-Chi Lian.
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not 
 * use this file except in compliance with the License. You may obtain a copy 
 * of the License at:
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software 
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT 
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the 
 * License for the specific language governing permissions and limitations under
 * the License. 
 */

/**
 * Name: easymoney_merge_csv.php
 * Sypnosis: ./easymoney_merge_csv.php <dir_with_csv> <destination>
 * 
 * @author yclian
 * @since 20100819
 * @version 20100819
 */

$dir = dirname(__FILE__);
$target = "$dir/target/".time().'.csv';

if(isset($argv[1])){
    $dir = $argv[1];
}
if(isset($argv[2])){
    $target = $argv[2];
}

// Things are written into 'merged' directory.
if(!file_exists(dirname($target))){
    mkdir(dirname($target));
}

// Filtering the CSV files.
$csv_files = scandir($dir);
$csv_files = array_filter($csv_files, function($csv_file){
    // Only .csv shall be picked
    return preg_match('/\.csv$/', $csv_file);
});

// Merging now.
$fh_merged = fopen($target, 'w');

// Write header
fwrite($fh_merged, "ACCOUNT,PAYEE_ITEM_DESC,CATEGORY,AMOUNT,STATUS,TRAN_DATE,REMARKS\n");

print "Processing CSVs in $dir: ".var_export($csv_files, TRUE)."\n";

foreach($csv_files as $csv_file){

    // Extracting account name from AccountName_Currency.csv.    
    $account = $csv_file;
    $account = ucfirst(preg_replace('/_[[:alpha:]]+\.csv$/', '', $account));
    $account = str_replace('_', ' ', $account);
    
    print "Merging '$account'..\n";
    
    $fh = fopen("$dir/$csv_file", 'r');
    
    while($fh && !feof($fh)){
    
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
        fwrite($fh_merged, $buffer);
    }
    fclose($fh);
}

fclose($fh_merged);

?>
