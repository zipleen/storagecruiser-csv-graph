<?php

error_reporting(E_ALL);

// db
require_once 'adodb5/adodb.inc.php' ;
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
$ADODB_CACHE_DIR = getcwd()."/adodb_cache/";

include "cnf.php";
$dblink = NewADOConnection("mysql");
//$dblink->debug = true;
//$dblink->Connect("Driver={SQL Server};Server=".$host.";Database=".$db.";",$user,$pass) or die("cannot connect to db!".$dblink->ErrorMsg());
$dblink->Connect($host, $user, $pass, $db) or die($dblink->ErrorMsg());
//db

function getValueFor($table, $name)
{
	global $dblink;
	// verifica se a prop existe, se nao existe, devolve!
	$name = trim($name);
	$id = $dblink->GetOne("SELECT id FROM $table WHERE name=".$dblink->qstr($name) );
	if($id===NULL)
	{
		// nao existe, vamos criar!
		$id = $dblink->Replace($table, array('name'=>$name), 'id', true);
	}
	return $id;
}

function checkValueForPair($dev, $prop)
{
	global $dblink;
	//$id = $dblink->GetOne("SELECT * from dev_prop WHERE id_dev='$dev' AND id_prop='$prop'");
	//if($id===NULL)
		$dblink->Replace("dev_prop", array('id_dev'=>$dev, 'id_prop'=>$prop), array('id_dev','id_prop'), true);
	$dblink->Execute("INSERT into dev_prop('id_dev','id_prop') VALUES('$dev','$prop')");
}

function processFile($filename)
{
	global $dblink;
	
	$fp = fopen($filename, "r");
	// apanhar o cabecalho!
	$cab = fgetcsv($fp, 0, ",");
	if(count($cab)<=1)
	{
		echo "file $filename is not a valid csv file";
		return;
	}
	// pegar no cabecalho, buscar propriedades!
	$cab_props = array();
	$cab_devs = array();
	for($i=1;$i<count($cab);$i++)
	{
		// dividir por "-" e eliminar os espacos
		list($devs, $props) = explode(" - ", $cab[$i]);
		// buscar o dev e o props!
		$cab_props[$i] = getValueFor("props", $props);
		$cab_devs[$i] = getValueFor("devs", $devs);
		checkValueForPair($cab_devs[$i], $cab_props[$i]);
	}
	// vamos pegar no dev_prop e preenche-lo
	echo "Processing $filename";
return;	
	while (($data = fgetcsv($fp)) !== FALSE) 
	{
		
        if($data[0]=="Date")
        	continue;
        if($data[0]=="")
        	continue;
        
        foreach($cab_props as $it=>$prop_value)
        {
        	
	        $data_t = array( 'id_dev'=> $cab_devs[$it], 'id_prop'=>$prop_value, 'valor'=>$data[$it], 'data'=>$data[0] );
	        $dblink->Replace('data', $data_t, array('id_dev','id_prop','data'), true);
	        echo ".";
        }	
        
    }
    echo " done\n";
}

// ler directoria!
if(!is_dir($argv[1]))
	die("directoria no argumento nao eh valida");
	
if ($handle = opendir($argv[1])) 
{
    while (false !== ($entry = readdir($handle))) 
    {
    	if(is_dir($argv[1].DIRECTORY_SEPARATOR.$entry))
    	{
	    	if ($handle1 = opendir($argv[1].DIRECTORY_SEPARATOR.$entry)) 
			{
			    while (false !== ($entry1 = readdir($handle1))) 
			    {
			    	if(is_file($argv[1].DIRECTORY_SEPARATOR.$entry.DIRECTORY_SEPARATOR.$entry1))
			    	{
			    		// check extension
			    		$ext = substr($entry1, stripos($entry1, "."));
			    		if($ext==".csv")
			    		{
				    		processFile($argv[1].DIRECTORY_SEPARATOR.$entry.DIRECTORY_SEPARATOR.$entry1);
			    		}
				    	
			    	}
			    }
			    
			}
    	}
    	
    	if(is_file($argv[1].DIRECTORY_SEPARATOR.$entry))
    	{
    		// check extension
    		$ext = substr($entry, stripos($entry, "."));
    		if($ext==".csv")
    		{
	    		processFile($argv[1].DIRECTORY_SEPARATOR.$entry);
    		}
	    	
    	}
        
    }
}
?>
