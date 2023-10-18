<?php

$NOT_SET = "NOT_SET";
require ( "antimat.dat" ); // �������-������ ( ����� ���� �������� �� ��������������)

function InitParam( $N, $V )
{
		global $Names, $Values;
		$Names = $N;
		$Values = $V;
}

function GetParam( $Name )
{
		global $Names, $Values, $NOT_SET;
		$Name = strtolower( $Name );
		$Nlist = explode( ":", $Names );
		for ( $i = 0; $i < count( $Nlist ); $i++ )
				if ( $Nlist[$i] == $Name )
						break;
		if ( $i == count( $Nlist ) )
				return $NOT_SET;
		$Vlist = explode( ":", $Values );
		return stripslashes( str_replace( "!~!", ":", $Vlist[$i] ) );
}


function SetParam( $Name, $Value )
{
		global $Names, $Values, $NOT_SET;
		$Nlist = explode( ":", $Names );
		$Name = strtolower( $Name );
		$Value = addslashes( str_replace( ":", "!~!", $Value ) );
		for ( $i = 0; $i < count( $Nlist ); $i++ )
				if ( $Nlist[$i] == $Name )
						break;

		if ( $i == count( $Nlist ) and ( $Value != $NOT_SET ) )
		{ // ��������� ��� � ��������
				$Names .= ":$Name";
				$Values .= ":$Value";
		}
		else
		{
				$Vlist = explode( ":", $Values );
				$Vlist[$i] = $Value;
				$Values = implode( ":", $Vlist );
				if ( $Value == $NOT_SET )
				{ // �������� ����� � ��������
						$Nlist[$i] = $NOT_SET;
						$Names = implode( ":", $Nlist );
						$Names = str_replace( ":$NOT_SET", "", $Names );
						$Values = str_replace( ":$NOT_SET", "", $Values );
				}
		}
}


function checkpass( $nick, $pass, $fields, &$result, $skippass = 0 )
{ //
		global $PassDelay;
		if ( $fields == "" )
				$fields = "pass,lastrefr";
		else
				if ( $fields !== "*" )
						$fields .= ",pass,lastrefr";

		$now = time();
		$sql = "select $fields from users where nick='$nick'";
		$result = mysql_query( $sql ) or die( mysql_error() );
		if ( mysql_num_rows( $result ) != 1 )
				return "����� �� ������";

		$row = mysql_fetch_array( $result );
		$dt = $PassDelay - $now + $row['lastrefr'];
		if ( $dt > 0 )
				return "��������� ����� $dt" . "sec";

		if ( $row['pass'] != $pass && !$skippass )
		{
				$sql = "update users set lastrefr=$now where nick='$nick'";
				mysql_query( $sql ) or die( mysql_error() );
				return "�������� ������";
		}
		return "";
}

function openDB()
{
		global $server, $user, $dbpass, $dbname;
		$sesDB = mysql_connect( $server, $user, $dbpass );
		if ( !$sesDB )
				return "���� ������ ����������. ��������� ����� 5��� -1";
		$ok = mysql_select_db( $dbname, $sesDB ) or msg(mysql_error());
		if ( !$ok )
				return "���� ������ ����������. ��������� ����� 5��� -2";
		return "";
}

function SetData( $login, $pass, $data )
{ // ���������� ������ ������ � ������ ������ ��� ��������� �� ������.
		global $error, $Names, $Values;
		if ( empty( $login ) )
				return "����� �� �����";
		if ( empty( $pass ) )
				return "������ �� �����";

		$maxdata = 5000; // ������������ ����� ������
		if ( strlen( $data ) > $maxdata )
				return "������� ������� ������.";

		$ok = checkpass( $login, $pass, "names,vals", $result, 1 ); // ��������� ��� ������!
		if ( $ok != "" )
				return $ok;
		InitParam( mysql_result( $result, 0, "names" ), mysql_result( $result, 0, "vals" ) );

		SetParam( 'gamedata', $data );

		$sqlUpd = "update users set names='$Names', vals='$Values' where nick = '$login'";
		mysql_query( $sqlUpd ) or die( mysql_error() );
}

function GetData( $login, $pass, &$data, $srv = 0 )
{ // ���������� ������ ������ � ������ ������ (������ ������������ � $data) ��� ��������� �� ������.
		global $error, $Names, $Values, $NOT_SET;
		if ( empty( $login ) )
				return "����� �� �����";
		if ( empty( $pass ) )
				return "������ �� �����";

		$ok = checkpass( $login, $pass, "names,vals", $result );
		if ( $ok != "" )
				return $ok;
		InitParam( mysql_result( $result, 0, "names" ), mysql_result( $result, 0, "vals" ) );
		$data = GetParam( "gamedata" );
		if ( $data == $NOT_SET )
				return "������ �� �������";

		return "";
}

function SetUser( $login, $oldpass, $newpass )
{ // ����������� ������ ������������ (oldpass = "") ��� ����� ������.
		// ���������� ������ ������ � ������ ������ ��� ��������� �� ������.
		global $RegStatus, $DefRefrInt, $DefMessLim, $CommonMode;

		if ( empty( $login ) )
				return "����� �� �����";
		if ( empty( $newpass ) )
				return "������ �� �����";
		if ( !ValidNN( $login ) )
				return "�������� ��������� � ������";
		if ( !ValidPass( $newpass ) )
				return "�������� ��������� � ������";

		$login = substr( $login, 0, 10 );
		$newpass = substr( $newpass, 0, 10 );

		$BadWord = GetBadWord( $login );
		if ( $BadWord != "" )
				return "����� �������� ����������� �����";

		if ( $oldpass != "" )
		{
				$ok = checkpass( $login, $oldpass, "", $result );
				if ( $ok != "" )
						return $ok;
				$sqlUpd = "update users set pass='$newpass' where nick = '$login' and pass='$oldpass'";
				mysql_query( $sqlUpd ) or die( mysql_error() );
		}
		else
		{
				$sqlSel = "select * from users where nick = '$login'";
				$result = mysql_query( $sqlSel ) or die( mysql_error() );
				$Count = mysql_num_rows( $result );
				if ( $Count != 0 )
						return "����� ����� ��� ��������������";
				$now = time();
				$sqllogin = "insert into users (status,sent,regtime,refrint,messlim,mode,nick,pass) values ('$RegStatus','0', '$now', '$DefRefrInt','$DefMessLim', '$CommonMode','$login', '$newpass')";
				mysql_query( $sqllogin ) or die( mysql_error() );
		}
		return "";
}

?>