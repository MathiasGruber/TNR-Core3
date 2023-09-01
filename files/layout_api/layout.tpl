<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE root [
  <!ELEMENT root ANY>
  <!ELEMENT loginStatus ANY>
  <!ELEMENT loginSession ANY>
  <!ELEMENT menuTable ANY>
  <!ELEMENT contentTable ANY>
  <!ATTLIST gameVersion _ID ID #REQUIRED>
  <!ATTLIST sleepLink _ID ID #REQUIRED>
  <!ATTLIST loginStatus _ID ID #REQUIRED>
  <!ATTLIST loginSession _ID ID #REQUIRED>
  <!ATTLIST notifications _ID ID #REQUIRED>
  <!ATTLIST menuGroup _ID ID #REQUIRED>
  <!ATTLIST contentTable _ID ID #REQUIRED>
  <!ATTLIST externalLinks _ID ID #REQUIRED>
 
]>
<root>
    {if (isset($smarty.session.uid) && !empty($smarty.session.uid))}
        <loginStatus _ID="loginStatus">{$smarty.session.uid}</loginStatus>
        <loginSession _ID="loginSession">{$sessionID}</loginSession>
    {else}
        <loginStatus _ID="loginStatus">0</loginStatus>
        <loginSession _ID="loginSession">0</loginSession>
    {/if}
    <gameVersion _ID="gameVersion">{$gameVersion}</gameVersion>    
    <sleepLink _ID="sleepLink">{if isset($sleepLink)}{$sleepLink}{/if}</sleepLink>
    
    
    <externalLinks _ID="externalLinks">
        <li href="http://www.theninja-forum.com/index.php?/forum/216-bug-reports-core-31/">Report Bugs</li>
        <li href="http://www.theninja-forum.com/index.php?app=nexus&module=support">Contact TNR</li>
    </externalLinks>
    
    <menuGroup _ID="notifications">
        {if isset($notifications) && count($notifications) != 0}
          {foreach $notifications as $notification}
          
            {if !is_array($notification['text'])}
              <li class="menuLink" >{$notification['text']}</li>
            {else}
              <li class="menuLink" href="{$notification['text'][0]}">{$notification['text'][1]}</li>
            {/if}
            
            {if $notification['buttons'] != 'none' && !is_array($notification['buttons'][0])}
              <li href="{$notification['buttons'][0]}"> ^  - > {$notification['buttons'][1]}</li>
            {else if $notification['buttons'] != 'none'}
              {foreach $notification['buttons'] as $button_key => $button}
                <li href="{$button[0]}"> ^  - > {$button[1]}</li>
              {/foreach}
            {/if}
          {/foreach}
        {/if}
        {if isset($MSG)}
            {foreach $MSG as $entry} 
                {if isset($entry.href)} 
                    {if isset($entry.hrefTxt)} 
                        <li class="menuLink" href="{$entry.href}">{$entry.txt}. {$entry.hrefTxt}</li>
                    {elseif !isset($entry.options)}                     
                        <li class="menuLink" href="{$entry.href}">{$entry.txt}</li>
                    {/if} 
                {else} 
                    <li class="menuLink">{$entry.txt}</li>
                {/if} 
            {/foreach}
        {/if}
    </menuGroup>    
 
    {$menuGroups = ['character','communication','village','training','missions','map','combat','support','general']}
    {foreach $menuGroups as $group}
        <menuGroup _ID="menu_{$group}">
            {if isset($menuArray[$group][0])} 
                {if $userStatus == "asleep" && $group != "missions"}
                    {if isset($sleepLink)}
                        {$sleepLink}
                    {else}
                        <li class="menuLink" href="?id=2">You are Sleeping</li>
                    {/if}
                {/if}
                {foreach $menuArray[$group] as $item} 
                    <li class="menuLink" href="{$item.link}">{$item.name}</li>
                {/foreach}
            {else}             
                {if $userStatus == "asleep"}
                    {if isset($sleepLink)}
                        {$sleepLink}
                    {else}
                        <li class="menuLink" href="?id=2">You are Sleeping</li>
                    {/if}
                {else}
                    <li class="menuLink" href="?id=2">Currently N/A</li>
                {/if}
            {/if}
        </menuGroup>
    {/foreach}          
    <contentTable  _ID="maincontent">
        {if isset($errorLoad)} {include file="file:{$absPath}/{$errorLoad|replace:'.tpl':'_mobile.tpl'}" title="Errors"} {/if} 
        {if !isset($hideContent) || $hideContent != true} 
            {if isset($CONTENT)} {$CONTENT} {/if} 
            {if isset($contentLoad)}{include file="file:{$absPath}/{$contentLoad|replace:'.tpl':'_mobile.tpl'}" title="Content"} {/if} 
            {if isset($extraContentLoad)}{include file="file:{$absPath}/{$extraContentLoad|replace:'.tpl':'_mobile.tpl'}" title="ExtraContent"}{/if} 
        {/if}
    </contentTable>
</root>