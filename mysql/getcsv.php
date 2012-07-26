<?php

error_reporting(E_ALL);
session_start();

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
		echo "DEV E PROP NAO EXISTEM NA DB!!!";
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

function display_options($prop)
{
	?>
	<html>
	<head>
	</head>
	<body>
	<Script language="JavaScript">
		
		function goto(form) { var index=form.select.selectedIndex
		if (form.select.options[index].value != "0") {
		location=form.select.options[index].value;}}
		//-->
		</SCRIPT>

	<?php
	// mostrar campos ja adicionados
	if(isSet($_SESSION['fields']))
	{
		echo "<h2> Colunas para construir </h2>";
		foreach($_SESSION['fields'] as $id=>$data)
		{
			echo "$id : ".getValueFor("props", $data['prop']). " - ".getValueFor("devs", $data['dev']). " <a href='?action=remove_fields&id=$id'>remove</a><br/>";
		}
	}
	// mostrar campos para adicionar
	echo "<h2> Adicionar campo</h2>";
	echo "<form action='?action=add_field' method='post'> Device: <select name='dev' onchange='goto(this.form)'>" . getcbox("devs", $_GET['dev']) ."</select>";
	
	
	// mostrar data
}


switch($_GET['action'])
{
	case 'add_field':
		add_field($_POST['dev'],$_POST['prop']);
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
		display_options();
		break;
}
?>