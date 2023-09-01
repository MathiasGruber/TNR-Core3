<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">    

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Event Panel</title>
<link href="./files/style.min.css" rel="stylesheet" type="text/css" />
<script language="JavaScript" type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script> 
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css" />
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>

{if isset($extraJava)}
    {$extraJava}
{/if}
</head>
<body>
<div align="center">
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td colspan="2" class="header">
        <a href="?id={$smarty.get.id}">&raquo; TheNinja-RPG Core3 Event Panel &laquo;</a>
      </td>
    </tr>
    <tr>
      <td width="160px" class="menu">
        {include file="file:{$absPath}/files/general_includes/menuInclude.tpl" title="Menu Inclusion"}   
      </td>
      <td class="content" align="center" valign="top" id="contentTable">
        {include file="file:{$absPath}/files/general_includes/contentInclude.tpl" title="Content Inclusion"}   
      </td>
    </tr>
    <tr>
      <td colspan="2" class="footer">&copy; Studie-Tech ApS, TheNinja-RPG.com 2005-{$YEAR}, Peak Memory Usage: {$memory}</td>
    </tr>
  </table>
</div>
<div style="text-align:center;padding-top:5px;"><a href="..">Return to main site</a></div>
</body>
</html>
