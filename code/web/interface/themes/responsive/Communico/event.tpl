<div class="col-xs-12">
	<div class="row">
		<div class="col-sm-4">
			<div class="panel active">
				{if (!empty($recordDriver->getEventCoverUrl()))}
				<div class="panel-body">
					<a href="{$recordDriver->getLinkUrl()}"><img class="img-responsive img-thumbnail {$coverStyle}" src="{$recordDriver->getEventCoverUrl()}" alt="{$recordDriver->getTitle()|escape}"></a>
				</div>
				{/if}
			</div>
			{if !empty($recordDriver->getAudiences())}
				<div class="panel active">
					<div class="panel-heading">
						{translate text="Audience" isPublicFacing=true}
					</div>
					<div class="panel-body">
						{foreach from=$recordDriver->getAudiences() item=audience}
							<div class="col-xs-12">
								<a href='/Events/Results?filter[]=age_group_facet%3A"{$audience|escape:'url'}"'>{$audience}</a>
							</div>
						{/foreach}
					</div>
				</div>
			{/if}
			{if !empty($recordDriver->getProgramTypes())}
				<div class="panel active">
					<div class="panel-heading">
						{translate text="Program Type" isPublicFacing=true}
					</div>
					<div class="panel-body">
						{foreach from=$recordDriver->getProgramTypes() item=type}
							<div class="col-xs-12">
								<a href='/Events/Results?filter[]=program_type_facet%3A"{$type|escape:'url'}"'>{$type}</a>
							</div>
						{/foreach}
					</div>
				</div>
			{/if}
		</div>
		<div class="col-sm-8">
				<h1>{$recordDriver->getTitle()}</h1>
				<ul>
					<li>Date: {$recordDriver->getStartDate()|date_format:"%A %B %e, %Y"}</li>
					<li>Time: {$recordDriver->getStartDate()|date_format:"%l:%M %p"} to {$recordDriver->getEndDate()|date_format:"%l:%M %p"}</li>
					<li>Branch: {$recordDriver->getBranch()}</li>
					{if !empty($recordDriver->getRoom())}
						<li>Room: {$recordDriver->getRoom()}</li>
					{/if}
					{if !empty($recordDriver->getType())}
						<li>Event Type: {$recordDriver->getType()}</li>
					{/if}
				</ul>
				<a class="btn btn-primary" href="{$recordDriver->getExternalUrl()}" target="_blank">
					{if $recordDriver->isRegistrationRequired()}
						{translate text="Register on Communico" isPublicFacing=true}
					{else}
						{translate text="View on Communico" isPublicFacing=true}
					{/if}
				</a>
			<br></br>
		</div>
		{*If there is no image or program types we need to make a new row to display properly*}
		{*A new row causes incorrect displays if there is an image or a panel for program type*}
		{if (empty($recordDriver->getEventCoverUrl()) && empty($recordDriver->getProgramTypes()))}
	</div>
	<div class="row">
		<div class="col-sm-offset-4 col-sm-8">
		{else}
		<div class="col-sm-8">
		{/if}
			{$recordDriver->getDescription()}
			<br></br>
			{$recordDriver->getFullDescription()}
		</div>
	</div>

	<div class="row">
		{if !empty($loggedIn) && (in_array('Administer Communico Settings', $userPermissions))}
			<div id="more-details-accordion" class="panel-group">
				<div class="panel" id="staffPanel">
					<a data-toggle="collapse" href="#staffPanelBody">
						<div class="panel-heading">
							<div class="panel-title">
								<h2>{translate text=Staff isPublicFacing=true}</h2>
							</div>
						</div>
					</a>
					<div id="staffPanelBody" class="panel-collapse collapse">
						<div class="panel-body">
							<h3>{translate text="Communico Event API response" isPublicFacing=true}</h3>
							<pre>{$recordDriver->getStaffView()|print_r}</pre>
						</div>
					</div>
				</div>
			</div> {* End of tabs*}
		{/if}
		</div>
	</div>
</div>