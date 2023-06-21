{strip}
	{* All CSS should be come before javascript for better browser performance *}
	{* TODO: Fix minification of css *}
	{if !empty($debugCss) || true}
    	{css filename="main.css"}
	{else}
		{css filename="main.min.css"}
	{/if}
	{if !empty($additionalCss)}
		<style type="text/css">
			{$additionalCss}
		</style>
	{/if}

	{* Include correct all javascript *}
	{if !empty($ie8)}
		{* include to give responsive capability to ie8 browsers, but only on successful detection of those browsers. For that reason, don't include in aspen.min.js *}
		<script src="/interface/themes/responsive/js/lib/respond.min.js?v={$gitBranch|urlencode}.{$cssJsCacheCounter}"></script>
	{/if}

	{* This is all merged using the merge_javascript.php file called automatically with a File Watcher*}
	{* Code is minified using uglify.js *}
	<script src="/interface/themes/responsive/js/aspen.js?v={$gitBranch|urlencode}.{$cssJsCacheCounter}"></script>

	{/strip}
	<script type="text/javascript">
		{* Override variables as needed *}
		{literal}
		$(document).ready(function(){{/literal}
			Globals.url = '{$url}';
			Globals.loggedIn = {if !empty($loggedIn)}true{else}false{/if};
			Globals.opac = {if !empty($onInternalIP)}true{else}false{/if};
			Globals.activeModule = '{$module}';
			Globals.activeAction = '{$action}';
			Globals.masqueradeMode = {if !empty($masqueradeMode)}true{else}false{/if};
			{if !empty($repositoryUrl)}
				Globals.repositoryUrl = '{$repositoryUrl}';
				Globals.encodedRepositoryUrl = '{$encodedRepositoryUrl}';
			{/if}

			{if !empty($automaticTimeoutLength)}
			Globals.automaticTimeoutLength = {$automaticTimeoutLength};
			{/if}
			{if !empty($automaticTimeoutLengthLoggedOut)}
			Globals.automaticTimeoutLengthLoggedOut = {$automaticTimeoutLengthLoggedOut};
			{/if}
			{* Set Search Result Display Mode on Searchbox *}
			{if empty($onInternalIP)}
			AspenDiscovery.Searches.getPreferredDisplayMode();
			{/if}
			{if !empty($userHasCatalogConnection)}
				Globals.hasILSConnection = true;
			{/if}
			{if array_key_exists('Axis 360', $enabledModules)}
				Globals.hasAxis360Connection = true;
			{/if}
			{if array_key_exists('Cloud Library', $enabledModules)}
				Globals.hasCloudLibraryConnection = true;
			{/if}
			{if array_key_exists('Hoopla', $enabledModules)}
				Globals.hasHooplaConnection = true;
			{/if}
			{if array_key_exists('OverDrive', $enabledModules)}
				Globals.hasOverDriveConnection = true;
			{/if}
			{if !empty($hasInterlibraryLoanConnection)}
				Globals.hasInterlibraryLoanConnection = true;
			{/if}
			Globals.loadingTitle = '{translate text="Loading" inAttribute=true isPublicFacing=true}';
			Globals.loadingBody = '{translate text="Loading, please wait" inAttribute=true isPublicFacing=true}';
			Globals.requestFailedTitle = '{translate text="Request Failed" inAttribute=true isPublicFacing=true}';
			Globals.requestFailedBody = '{translate text="There was an error with this AJAX Request." inAttribute=true isPublicFacing=true}';
			Globals.rtl = {if $userLang->isRTL()}true{else}false{/if};
			Globals.bypassAspenLoginForSSO = {if $bypassAspenPatronLogin}true{else}false{/if};
			Globals.ssoLoginUrl = '{$bypassLoginUrl}';
			{literal}
		});

      //If location has been found, redirect to closest library 
      // **TODO: Add pathfinding to find closest library instead of just looking for exact match (after proof of concept)
			function success(pos){
			if (sessionStorage.getItem('alreadyClicked') !== 'true') {
				const lat = pos.coords.latitude;
				const long = pos.coords.longitude;
				sessionStorage.setItem('alreadyClicked','true'); //if location has been found this session, do not redirect again.
				switch(true) {
				// Bath
					case (lat == 51.37851 && long == -2.35972):
						window.location.replace("https://academic-demo.aspendiscovery.co.uk/");
						break;
				// Bridgewater
					case (lat == 51.12805 && long == -3.00425):
						window.location.replace("https://west.aspendiscovery.co.uk/");
						break;
				// Charmouth
					case (lat == 50.73855 && long == -2.90369):
						window.location.replace("https://east.aspendiscovery.co.uk/");
						break;
					default:
						break;
					};
			}
				}
      //if location is not found, handle errors gracefully.
		function error(err){
			if (err.code === 1) {
				alert("Please enable location data to be redirected to the closest library site");
			} else {
				alert('Position Unavailable');
			}	
			console.log("Unable to retrieve your location");
		};
      //options for geolocation 
			const options = {
        		enableHighAccuracy: true,
        		timeout: 5000,
        		maximumAge: 0
			};
      //if the geolocation api cannot be found, throw error.
			if (!navigator.geolocation){
				throw new Error('No geolocation available');
			}
      //else, find current location 
				window.navigator.geolocation.getCurrentPosition(success, error, options);
		{/literal}
	</script>{strip}

	{if !empty($includeAutoLogoutCode)}
		{if !empty($debugJs)}
			<script type="text/javascript" src="/interface/themes/responsive/js/aspen/autoLogout.js?v={$gitBranch|urlencode}.{$cssJsCacheCounter}"></script>
		{else}
			<script type="text/javascript" src="/interface/themes/responsive/js/aspen/autoLogout.min.js?v={$gitBranch|urlencode}.{$cssJsCacheCounter}"></script>
		{/if}
	{/if}
{/strip}
