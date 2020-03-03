<?php

  //Get and parse parameters
  function print_menu() {
    echo "Script usage:\n\n";
    echo "--file [csv file name] : the name of the CSV to be parsed\n";
    echo "--create_table : this will cause the MySQL users table to be built (and no further action will be taken)\n";
    echo "--dry_run : this will be used with the --file directive in case we want to run the script but not insert into the DB. All other functions will be executed, but the database won't be altered\n";
    echo "-u : MySQL username\n";
    echo "-p : MySQL password\n";
    echo "-h : MySQL host\n";
    echo "--help : which will output the above list of directives with details.\n";
  }

  // Create variables
  $file = "";
  $createtable = false;
  $dryrun = false;
  $username = "";
  $password = "";
  $host = "";

  // Let's check if $argv variable is populated.
  if(count($argv)==0) {
    die("The script requires register_argc_argv enabled in php.ini\n");
  }

  // If the script pass this check, we're good to go.

  // If user requests help, print and exit
  if(in_array("--help",$argv)) {
    print_menu();
    die();
  }

  // File parameter validation
  if(in_array("--file",$argv)) {
    $pos = array_search("--file", $argv);
    if($pos<$argc-1) {
      $file = $argv[$pos+1];   
      if(!(file_exists($file) && is_file($file))) {
        die("Invalid file name provided\n");
      }
    } else {
      die("No file name provided\n");
    }
  }

  // Check if create_table parameter was sent
  if(in_array("--create_table",$argv)) {
    $createtable = true;
  }

  // Check if dry_run was sent
  if(in_array("--dry_run",$argv)) {
    $dryrun = true;
  }

  // Username parsing
  if(in_array("-u",$argv)) {
    $pos = array_search("-u", $argv);
    if($pos<$argc-1) {
      $username = $argv[$pos+1];
    }
  }

  // Password parsing  
  if(in_array("-p",$argv)) {
    $pos = array_search("-p", $argv);
    if($pos<$argc-1) {
      $password = $argv[$pos+1];
    }
  }

  // Host parsing
  if(in_array("-h",$argv)) {
    $pos = array_search("-h", $argv);
    if($pos<$argc-1) {
      $host = $argv[$pos+1];
    }
  }

  // Print the selected options
  echo "File: $file\n";
  echo "Create table: $createtable\n";
  echo "Dry run: $dryrun\n";
  echo "Username: $username\n";
  echo "Password: $password\n";
  echo "Host: $host\n";

 ?>