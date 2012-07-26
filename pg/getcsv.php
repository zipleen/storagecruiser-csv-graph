<?php

error_reporting(E_ALL);
session_start();

// db
require_once 'adodb5/adodb.inc.php' ;
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
$ADODB_CACHE_DIR = getcwd()."/adodb_cache/";

include "cnf.php";
$dblink = NewADOConnection("pgsql");
//$dblink->debug = true;
//$dblink->Connect("Driver={SQL Server};Server=".$host.";Database=".$db.";",$user,$pass) or die("cannot connect to db!".$dblink->ErrorMsg());
$dblink->Connect($host, $user, $pass, $db) or die($dblink->ErrorMsg());
//db

function getValueFor($table, $id)
{
	global $dblink;
	// verifica se a prop existe, se nao existe, devolve!
	$name = trim($name);
	$id = $dblink->GetOne("SELECT name FROM $table WHERE id=".$dblink->qstr($id) );
	
	return $id;
}


function add_field($dev, $prop)
{
	global $dblink;
	if($dev=="")
	{
		echo "DEV NAO TEM NADA!";
		return;
	}
	if($prop=="")
	{
		echo "PROP NAO TEM NADA!";
		return;
	}
	$id = $dblink->GetOne("SELECT * from dev_prop WHERE id_dev=".$dblink->qstr($dev)." AND id_prop=".$dblink->qstr($prop));
	if($id!==NULL)
	{
		// parece q ha, adicionar ah sessao!
		if(!isSet($_SESSION['fields']))
			$_SESSION['fields'] = array();
			
		$c = count($_SESSION['fields']);
		$c++;
		$_SESSION['fields'][$c] = array();
		$_SESSION['fields'][$c]['dev'] = $dev;
		$_SESSION['fields'][$c]['prop'] = $prop;
	}
	else
	{
		echo "DEV ($dev) E PROP ($prop) NAO EXISTEM NA DB!!!";
		return;
	}
}

function getcbox($table, $selected)
{
	global $dblink;
	$html = "<option value=''></option>";
	
	$ans = $dblink->Execute("SELECT DISTINCT * FROM $table ORDER BY name");
	if($ans && $ans->RecordCount()>0)
	{
		while(!$ans->EOF)
		{
			$ch = "";
			if($selected==$ans->fields['id'])
				$ch = " selected";
				
			$html .= "<option value='".$ans->fields['id']."' $ch>".$ans->fields['name']."</option>";
			$ans->MoveNext();
		}
	}
	return $html;
}

function getcboxRel($table, $rel_table, $rel_value, $selected)
{
	global $dblink;
	$html = "<option value=''></option>";
	
	$ans = $dblink->Execute("SELECT DISTINCT id_$table as id, $table as name FROM dev_prop_view WHERE id_$rel_table=".$dblink->qstr($rel_value)." ORDER by name");
	//echo "SELECT DISTINCT id_$table, $table as name FROM dev_prop_view WHERE id_$rel_table=".$dblink->qstr($rel_value)." ";
	if($ans && $ans->RecordCount()>0)
	{
		while(!$ans->EOF)
		{
			$ch = "";
			if($selected==$ans->fields['id'])
				$ch = " selected";
				
			$html .= "<option value='".$ans->fields['id']."' $ch>".$ans->fields['name']."</option>";
			$ans->MoveNext();
		}
	}
	return $html;
}


function display_options($prop)
{
	?>
	<html>
	<head>
	</head>
	<body>
	<script language="JavaScript">
		
		function goto1(form) { 
		
		location.href="getcsv.php?prop=" + document.forms[0].prop.options[document.forms[0].prop.selectedIndex].value;
		}
		//-->
		</SCRIPT>

	<?php
	global $dblink;
	// mostrar campos ja adicionados
	if(isSet($_SESSION['fields']))
	{
		echo "<h2> Colunas para construir </h2>";
		foreach($_SESSION['fields'] as $id=>$data)
		{
			echo "$id : ".getValueFor("props", $data['prop']). " - ".getValueFor("devs", $data['dev']). " <a href='?action=remove_field&id=$id'>remove</a><br/>";
		}
	}
	// mostrar campos para adicionar
	echo "<h2> Adicionar campo</h2>";
	
	/*
	echo "<form action='?action=add_field' method='post'> Device: <select name='dev' onchange='goto1(this.form)'>" . getcbox("devs", $_REQUEST['dev']) ."</select>";
	
	if($dev!="")
	{
		echo " Property: <select name='prop' >" . getcboxRel("prop", "dev", $dev, $_REQUEST['prop']) ."</select> <input type='submit' value='add_field' />";
	}
	echo "</form>";
	
	*/
	echo "<form action='' method='post'> Property: <select name='prop' onchange='goto1(this.form)'>" . getcbox("props", $_REQUEST['prop']) ."</select> <input type='submit' name='action' value='addlog' >";
	
	if($prop!="")
	{
		echo " Devices: <select name='dev' >" . getcboxRel("dev", "prop", $prop, $_REQUEST['dev']) ."</select> <input type='submit' name='action' value='add_field' />";
	}
	echo "</form>";

	// mostrar data
	echo "<h2>Data</h2><form action='?action=getcsv' method='post'>";
	echo "De: <input type='text' value='".$dblink->GetOne("SELECT MIN(data) from data")."' name='de' size='40'/> Ate: <input type='text' value='".$dblink->GetOne("SELECT MAX(data) from data")."' name='ate' size='40' />";
	echo "<br/><input type='submit' value='get csv'></form>";
}

function get_csv()
{
	//"select distinct data as datarow, (select valor from data where data=datarow and id_dev=1 and id_prop=2) as '1_2', (select valor from data where data=datarow and id_dev=1 and id_prop=3) as '1_2' from `data` where data>='2012-06-08 00:00:15' and data<='2012-06-08 00:10:00'"
	global $dblink;
	include_once("adodb5/toexport.inc.php");
	
	$de = $_POST['de'];
	$ate = $_POST['ate'];
	$sql_c = "";
	foreach($_SESSION['fields'] as $id=>$data)
	{
		$sql_c .= ",(SELECT valor from data WHERE data=d1.data and id_dev=".$data['dev']." and id_prop=".$data['prop'].") as \"".getValueFor("devs", $data['dev']). " - ".getValueFor("props", $data['prop'])."\"";
	}
	$sql = "COPY (select distinct data as datarow $sql_c from data d1 WHERE data>=".$dblink->qstr($de)." AND data<=".$dblink->qstr($ate)." ORDER by data) TO '".dirname(__FILE__)."/out.csv' CSV;";

	echo $sql."<br/>";
	
	$rs = $dblink->Execute($sql);
	if(!$rs)
		echo "errro: ".$dblink->ErrorMsg();
	//$fp = fopen("out.csv", "w");

	//if ($fp) {
	
	 // rs2csvfile($rs, $fp); # write to file (there is also an rs2tabfile function)
	
	 // fclose($fp);
	  echo "download csv <a href='out.csv'>here</a>";
	  
	//}else echo "error writing file!!!";
	
}

switch($_REQUEST['action'])
{
	case 'add_field':
		add_field($_POST['dev'],$_POST['prop']);
		display_options();
		break;

	case 'addlog':
		//$rs = $dblink->Execute("select id, SUBSTRING(name, -2), CONV(SUBSTRING(name, -2), 16, 10) as num from devs where name like 'LogicalVolume%' and CONV(SUBSTRING(name, -2), 16, 10)>= 0 and CONV(SUBSTRING(name, -2), 16, 10)<=27 order by num");
		$rs = $dblink->Execute("select id, SUBSTRING(name, -2), hex_to_int(substr(name, 18, 2) ) as num from devs where name like 'LogicalVolume%' and hex_to_int(substr(name, 18, 2) )>= 0 and hex_to_int(substr(name, 18, 2) )<=27 order by num");
		if($rs && $rs->RecordCount()>0)
		{
			while(!$rs->EOF)
			{
				add_field($rs->fields['id'], $_POST['prop']);
				$rs->MoveNext();
			}
		}
		display_options();
		break;
	case 'remove_field':
		if(isSet($_SESSION['fields']))
		{
			
			if(isSet($_SESSION['fields'][$_GET['id']]) )
			{
				unset($_SESSION['fields'][$_GET['id']]);
			}
		}
		display_options();
		break;

	case 'remove_fields':
		unset($_SESSION['fields']);
		display_options();
		break;

	case 'getcsv':
		get_csv();
		break;
	
	default:
		display_options($_REQUEST['prop']);
		break;
}
?>
