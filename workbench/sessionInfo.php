<?php
require_once ('session.php');
require_once ('shared.php');
if(isset($_REQUEST['switchApiVersionTo'])){
	$previousVersion = getApiVersion();
	clearSessionCache();
	$_SESSION['location'] = preg_replace("/\d\d?\.\d/",$_REQUEST['switchApiVersionTo'], $_SESSION['location']);
	$_SESSION['wsdl'] = 'soapclient/sforce.' . str_replace('.', '', $_REQUEST['switchApiVersionTo']) . '.partner.wsdl';
	header("Location: $_SERVER[PHP_SELF]?previousVersion=" . $previousVersion);
}

global $mySforceConnection;
if(isset($_REQUEST['previousVersion'])){
	try {
		$mySforceConnection->getServerTimestamp();
	} catch (Exception $e) {
		if(stripos($e->getMessage(),'UNSUPPORTED_API_VERSION') > -1) {
			clearSessionCache();
			$_SESSION['location'] = preg_replace("/\d\d?\.\d/",$_REQUEST['previousVersion'], $_SESSION['location']);
			$_SESSION['wsdl'] = 'soapclient/sforce.' . str_replace('.', '', $_REQUEST['previousVersion']) . '.partner.wsdl';
			header("Location: $_SERVER[PHP_SELF]?UNSUPPORTED_API_VERSION");
		}
		show_error($e->getMessage(),true,true);
		exit;
	}	
}

require_once ('header.php');
?>

<script type="text/javascript" src="script/simpletreemenu.js">
/***********************************************
* Simple Tree Menu - Dynamic Drive DHTML code library (www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
***********************************************/
</script>
<link rel="stylesheet" type="text/css" href="style/simpletree.css" />

<p/>
<div style='float:right;'>
	<form name="changeApiVersionForm" action="<?php $_SERVER['PHP_SELF'] ?>">
		Change API Version: 
		<?php
		print "<select  method='POST' name='switchApiVersionTo' onChange='document.changeApiVersionForm.submit();'>";
		foreach($_GLOBALS['apiVersions'] as $v) {
			print "<option value='$v'";
			if (getApiVersion() == $v) print " selected=\"selected\"";
			print ">" . $v . "</option>";
		}
		print "</select>";
		?>
	</form>
</div>

<?php

if (isset($_REQUEST['UNSUPPORTED_API_VERSION'])) {
	show_error("Selected API version is not supported by this Salesforce organization. Automatically reverted to prior version.",false,false);
}

$sessionInfo = array();
$sessionInfo['Connection'] = array(
	'API Version' => getApiVersion(),
	'Client Id' => isset($_SESSION['tempClientId']) ? $_SESSION['tempClientId'] : $_SESSION['config']['callOptions_client'], 
	'Endpoint' => $mySforceConnection->getLocation(),
	'Session Id' => $mySforceConnection->getSessionId(), 
);

foreach($mySforceConnection->getUserInfo() as $uiKey => $uiValue) {
	if(stripos($uiKey,'org') !== 0) {
		$sessionInfo['User'][$uiKey] = $uiValue;
	} else {
		$sessionInfo['Organization'][$uiKey] = $uiValue;		
	}
}

print "<p/>" .
      "<a href=\"javascript:ddtreemenu.flatten('sessionInfoTree', 'expand')\">Expand All</a> | <a href=\"javascript:ddtreemenu.flatten('sessionInfoTree', 'collapse')\">Collapse All</a>\n" .
      "<ul id='sessionInfoTree' class='treeview'>\n";

function printNode($node) {
	foreach($node as $nodeKey => $nodeValue) {
		if(is_array($nodeValue)){
			print "<li>$nodeKey<ul>\n";
			printNode($nodeValue);
			print "</ul></li>\n";
		} else {
			if(is_bool($nodeValue)) {
				$nodeValue = $nodeValue == 1 ? "true" : "false";
			}
			print "<li>$nodeKey: <span style='font-weight:bold;'>" . addLinksToUiForIds($nodeValue) . "</span></li>\n";
		}
	}
	
}

printNode($sessionInfo);

require_once ('footer.php');
?>
<script type="text/javascript">
ddtreemenu.createTree("sessionInfoTree", true);
//ddtreemenu.flatten('sessionInfoTree', 'expand');
</script>