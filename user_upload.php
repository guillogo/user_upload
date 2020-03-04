<?php

	//[1]. Get and parse parameters
	function print_menu()
	{
		echo "Script Command Line Directives:\n";
		echo "--------------------\n";
		echo "--file [csv file name] : the name of the CSV to be parsed\n";
		echo "--create_table : this will cause the MySQL users table to be built (and no further action will be taken)\n";
		echo "--dry_run : this will be used with the --file directive in case we want to run the script but not insert into the DB. All other functions will be executed, but the database won't be altered\n";
		echo "-u : MySQL username\n";
		echo "-p : MySQL password\n";
		echo "-h : MySQL host\n";
		echo "--help : which will output the above list of directives with details.\n";
	}

	// Create variables
	$file        = "";
	$createtable = false;
	$dryrun      = false;
	$username    = "";
	$password    = "";
	$host        = "";
	$conn        = "";

	// Let's check if $argv variable is populated.
	if (count($argv) == 0) {
		die("The script requires register_argc_argv enabled in php.ini\n");
	}

	// If the script pass this check, we're good to go.

	// If user requests help, print and exit
	if (in_array("--help", $argv)) {
		print_menu();
		die();
	}

	// File parameter validation
	if (in_array("--file", $argv)) {
		$pos = array_search("--file", $argv);
		if ($pos < $argc - 1) {
			$file = $argv[$pos + 1];
			if (!(file_exists($file) && is_file($file))) {
				die("Invalid file name provided\n");
			}
		} else {
			die("No file name provided\n");
		}
	}

	// Check if create_table parameter was sent
	if (in_array("--create_table", $argv)) {
		$createtable = true;
	}

	// Check if dry_run was sent
	if (in_array("--dry_run", $argv)) {
		$dryrun = true;
	}

	// Username parsing
	if (in_array("-u", $argv)) {
		$pos = array_search("-u", $argv);
		if ($pos < $argc - 1) {
			$username = $argv[$pos + 1];
		}
	}

	// Password parsing  
	if (in_array("-p", $argv)) {
		$pos = array_search("-p", $argv);
		if ($pos < $argc - 1) {
			$password = $argv[$pos + 1];
		}
	}

	// Host parsing
	if (in_array("-h", $argv)) {
		$pos = array_search("-h", $argv);
		if ($pos < $argc - 1) {
			$host = $argv[$pos + 1];
		}
	}
	
	// Validate required parameters
	if (!$createtable && $file=="") {
		echo "\n";
		echo "The parameter File is required. Please try again.\n";
		print_menu();
		die();		
	}

	if (!$dryrun && ($username=="" || $host=="")){
		echo "\n";
		echo "The parameters Username and Host are required. Please try again.\n";
		print_menu();
		die();		
	}
	
	// Select --create_table or --dry_run not both
	if ($createtable && $dryrun){
		echo "\n";
		echo "Select --create_table or --dry_run, not both. Please try again.\n";
		print_menu();
		die();		
	}	

	// Print the selected options
	echo "\n";
	echo "Selected options:\n";
	echo "--------------------\n";
	echo "File: $file\n";
	echo "Create table: $createtable\n";
	echo "Dry run: $dryrun\n";
	echo "Username: $username\n";
	echo "Password: $password\n";
	echo "Host: $host\n\n";
	// Database parameter is missing, assuming username as such? or create a new?
	$dbname = "dbguillermo";

	//[2]. Creating a connection to the database
	function connection($host, $username, $password, $dbname)
	{
		$conn = mysqli_connect($host, $username, $password, $dbname);
		// Check connection
		if (!$conn) {
			die("Error. Connection failed: ". mysqli_connect_errno() . "\n");
		}	

		// Create table users
		$sql = "CREATE TABLE if not exists users (
				name VARCHAR(255),
				surname VARCHAR(255),
				email VARCHAR(255) NOT NULL,
				UNIQUE KEY(email)
				)";
		
		if ($conn->query($sql) === TRUE) {
			echo "Table users created successfully\n";
		} else {
			echo "Error creating table: " . $conn->error . "\n";
		}
		// truncate table users
		$sql = "TRUNCATE TABLE users";
		
		if ($conn->query($sql) === TRUE) {
		} else {
			echo "Error deleting table: " . $conn->error . "\n";
		}
		
		return $conn;
		
	}

	if(!$dryrun) {
		// If create the connection to database	
		echo "Database information\n";
		echo "--------------------\n";
		$conn = connection($host, $username, $password, $dbname);
		if($createtable) {
			exit();
		}
	}
	
	
	//[3]. Read data from CSV
	$insert = 0;
	$notinsert = 0;
	if ($fh = fopen($file, 'r')) {
		$firstrow = fgets($fh);
		if ($dryrun){
			echo "File records\n";
			echo "--------------------\n";			
		}
		// Discard the first line - headers
		while (!feof($fh)) {
			// Reading CSV data line by line
			$line   = fgets($fh);			
			// Parse $line and split in columns
			$output = explode(",", $line);
			if (!isset($output[1])) {
			   $output[1] = null;
			}
			if (!isset($output[2])) {
			   $output[2] = null;
			}
			//Make name and surname first character uppercase
			//Lowercase and remove all illegal characters from email
			$name = ucfirst(strtolower(trim($output[0])));
			$surname = ucfirst(strtolower(trim($output[1])));
			$email = strtolower(trim($output[2]));

			if(!($name == "" && $surname == "" && $email == "")) {
			
				//[4]. Insert into database
				// If dry_run is true, read and parse data from csv but not insert to database
				// If create_table is true, connect to database, create the table and exit.
				// Validate email format
				if (filter_var($email, FILTER_VALIDATE_EMAIL)){			
					if (!$dryrun){
						$sql = "INSERT INTO users (name, surname, email) VALUES (?, ?, ?)";

						if($stmt  = $conn->prepare($sql)){
							// Bind variables to the prepared statement as parameters
							$stmt ->bind_param("sss", $name, $surname, $email);
							$stmt ->execute();
							$insert++;
						} else{
							echo "ERROR: Could not prepare query: $sql. " . $mysqli->error . "\n";
						}
					} else {
						echo $name .", ". $surname .", ". $email . "\n";;
					}
				} else {
					$notinsert++;
					echo "ERROR: Invalid email. Record ignored: ".$name .", ". $surname .", ". $email . "\n";
				}				
			}
		}
		if(!$dryrun) {
			echo "Records inserted successfully: ".$insert. "\n";
			echo "Records not inserted : ".$notinsert. "\n";
		}
		fclose($fh);
	} else {
		die("Invalid file specification\n");
	}

	if ($createtable){
		$conn->close();
	}


?>
