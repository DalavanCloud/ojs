{**
 * plugins/generic/sectionEditorOptions/templates/sectionEditorOptionsSettingsForm.tpl
 *
 * Copyright (c) 2013-2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Usage statistics plugin management form.
 *
 *}
{strip}
{assign var="pageTitle" value="plugins.generic.sectionEditorOptions.displayName"}
{include file="common/header.tpl"}
{/strip}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#sectionEditorOptionsSettingsForm').pkpHandler('$.pkp.controllers.form.FormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="sectionEditorOptionsSettingsForm" method="post" action="{plugin_url path="save"}">

	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="sectionEditorOptionsSettingsFormNotification"}

	{fbvFormArea id="sectionEditorOptions" title="plugins.generic.sectionEditorOptions.settings.workflowOptions"}
		{fbvFormSection for="denyEditorialDecision" list=true description="plugins.generic.sectionEditorOptions.settings.denyEditorialDecision.description"}
			{fbvElement type="checkbox" id="denyEditorialDecision" value=1 checked=$denyEditorialDecision label="plugins.generic.sectionEditorOptions.settings.denyEditorialDecision"}
		{/fbvFormSection}
		{fbvFormSection for="denyContact" list=true description="plugins.generic.sectionEditorOptions.settings.denyContact.description"}
			{fbvElement type="checkbox" id="denyContact" value=1 checked=$denyContact label="plugins.generic.sectionEditorOptions.settings.denyContact"}
		{/fbvFormSection}
		{fbvFormSection for="denyReviewFilesAccess" list=true description="plugins.generic.sectionEditorOptions.settings.denyReviewFilesAccess.description"}
			{fbvElement type="checkbox" id="denyReviewFilesAccess" value=1 checked=$denyReviewFilesAccess label="plugins.generic.sectionEditorOptions.settings.denyReviewFilesAccess"}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormButtons id="sectionEditorOptionsSettingsFormSubmit" submitText="common.save" hideCancel=true}
</form>
{include file="common/footer.tpl"}
