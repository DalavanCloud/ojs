{**
 * header.tpl
 *
 * Copyright (c) 2003-2004 The Public Knowledge Project
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site header.
 *
 * $Id: header.tpl,v 1.37 2006/01/05 19:32:40 alec Exp $
 *}

{if !$pageTitleTranslated}{translate|assign:"pageTitleTranslated" key=$pageTitle}{/if}

{if $pageCrumbTitle}{translate|assign:"pageCrumbTitleTranslated" key=$pageCrumbTitle}{elseif !$pageCrumbTitleTranslated}{assign var="pageCrumbTitleTranslated" value=$pageTitleTranslated}{/if}
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset}" />
	<title>{$pageTitleTranslated}</title>
	<meta name="description" content="{$metaSearchDescription}" />
	<meta name="keywords" content="{$metaSearchKeywords}" />
	{$metaCustomHeaders}
	<link rel="stylesheet" href="{$baseUrl}/styles/common.css" type="text/css" />
	{foreach from=$stylesheets item=cssUrl}
	<link rel="stylesheet" href="{$cssUrl}" type="text/css" />
	{/foreach}
	<script type="text/javascript" src="{$baseUrl}/js/general.js"></script>
	{$additionalHeadData}
</head>
<body> 
<div id="container">
<div id="header">
	<div id="topNavigation">
	{if $enableLanguageToggle}
		<div class="languageOptions">
			<ul>
			{foreach name=outer key=chv item=locale from=$languageToggleLocales} 
				{foreach key=key item=item from=$locale}
					<li><a href="#" onclick="location.href={if $languageToggleNoUser}'{$currentUrl}{if strstr($currentUrl, '?')}&{else}?{/if}setLocale='{$chv}{else}('{url page="user" op="setLocale" path="NEW_LOCALE" source=$smarty.server.REQUEST_URI escape=false}'.replace('NEW_LOCALE', '{$chv}')){/if}">{$locale}</a></li>
				{/foreach}
			{/foreach}
			</ul>
			</div>		
	{/if}
	
	
	{if $isUserLoggedIn}
	<div class="userOptions">
	<span class="blockTitle">{translate key="navigation.loggedInAs"}</span>
	<strong>{$loggedInUsername|escape}</strong>
	<br><a name="signOut" href="{url page="login" op="signOut"}">{translate key="navigation.logout"}</a>
	</div>
	<div class="block">
	</div>
	{/if}
	</div>
	
<div id="headerTitle">
<h1>
<a href="{$scielo_url}"><img src="/logo_scielo.gif" align="absmiddle" border="0" /></a>
</h1>
<h2>{if $displayPageHeaderLogo}
	<img src="{$publicFilesDir}/{$displayPageHeaderLogo.uploadName|escape:"url"}" width="{$displayPageHeaderLogo.width}" height="{$displayPageHeaderLogo.height}" border="0" alt="" />
{/if}
{if $displayPageHeaderTitle && is_array($displayPageHeaderTitle)}
	<img src="{$publicFilesDir}/{$displayPageHeaderTitle.uploadName|escape:"url"}" width="{$displayPageHeaderTitle.width}" height="{$displayPageHeaderTitle.height}" border="0" alt="" />
{elseif $displayPageHeaderTitle}
	{$displayPageHeaderTitle}
{elseif $alternatePageHeader}
	{$alternatePageHeader}
{elseif $siteTitle}
	{$siteTitle}
{else}
	{translate key="common.openJournalSystems"}
{/if}

</h2>
</div>
</div>

<div id="body">

{if (!$sideBarShow)}
	</br>
{else}
	<div id="sidebar">
		{ include file="common/sidebar.tpl" }
	</div>
{/if}

{if (!$sideBarShow)}
	<div id="main" class="fullWidth">
{else}
    <div id="main">
{/if}

<div id="breadcrumb">
	
	 <a href="{url page="index"}">{translate key="navigation.home"}</a> 
	{foreach from=$pageHierarchy item=hierarchyLink}
		&gt; <a href="{$hierarchyLink[0]}" class="hierarchyLink">{if not $hierarchyLink[2]}{translate key=$hierarchyLink[1]}{else}{$hierarchyLink[1]}{/if}</a> 
	{/foreach}
	
	{if !$pageIndex}
		&gt; <a href="{$currentUrl}" class="current">{$pageCrumbTitleTranslated}</a>
	{/if}

</div>

{if $submission}
	
	<div class="artTitle">#{$submission->getJournalArticleId()} : {$submission->getArticleTitle()|truncate:60:"...":true}</div>
{else}
	<h2>{$pageTitleTranslated}</h2>
{/if}

<div id="content">

