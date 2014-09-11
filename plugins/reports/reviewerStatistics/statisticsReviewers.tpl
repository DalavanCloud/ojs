

{assign var="pageTitle" value="plugins.generic.reviewerstatistics.func"}
{assign var="pageCrumbTitle" value="plugins.generic.reviewerstatistics.func"}


{include file="common/header.tpl"}

<div>&nbsp;</div>

<div>
	<strong>
	</strong>  
</div> 


<p>Ano selecionado: {$statisticsYear}. &nbsp;
    <a href={url}/reviewerStatisticsPlugin/?statisticsYear=ALL>ALL YEARS</a>&nbsp;
    <a href={url}/reviewerStatisticsPlugin/?statisticsYear={$prevYear}>{$prevYear}</a>&nbsp;
    <a href={url}/reviewerStatisticsPlugin/?statisticsYear={$nextYear}>{$nextYear}</a>
</p>

<table border="1">					
		<tr>
			<td>Username</td>
			<td>Name</td>
			<td>Email</td>
			<td>Invited</td>
			<td>Accepted</td>
			<td>Completed</td>
			<td>Declined</td>				
		</tr>
		{foreach from=$reviewerStatistics item=returner key=key name=ret}				
			<tr>			  
				<td>&nbsp;{$returner.username}</td>				
                <td>&nbsp;{$returner.name}</td>
				<td>&nbsp;{$returner.email}</td>
                <td>&nbsp;{$returner.invited}</td>
				<td>&nbsp;{$returner.accepted}</td>
				<td>&nbsp;{$returner.completed}</td>
				<td>&nbsp;{$returner.declined}</td>
			</tr>
		{/foreach}								
</table>
</p>

{include file="common/footer.tpl"}


