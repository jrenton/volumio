@extends('layouts.master')

@section('content')
<div class="container">
	<h1>Settings</h1>
	<form class="form-horizontal" method="post">
		<fieldset>
			<legend>Player Name</legend>
            <div class="form-group" id="environment">
				<div class="form-group">
				<label class="control-label" for="hostname">Player name</label>
					<div class="controls">
						<input class="input-large" class="input-large" type="text" id="hostname" name="hostname" value="{{ $hostname }}" autocomplete="off">
					</div>
				</div>
			</div>
			<p>
				Changes how your Volumio device is called. This affects Player Name, Airplay Name and UPnP Name. A reboot is required for 
				changes to take effect.
			</p>
		<div class="form-actions">
				<button class="btn btn-primary btn-lg" value="apply" name="apply" type="submit">Apply</button>
			</div>
		</fieldset>
	</form>
	<form class="form-horizontal" method="post">
		<fieldset>
			<legend>Services management</legend>
			<p>Enable or disable certain Volumio functionalities</p>
			<div class="form-group">
				<label class="control-label">Airplay</label>
				<div class="controls">
					<div class="toggle">
						<label class="toggle-radio" for="toggleshairport2" >ON</label>
<input type="radio" name="shairport" id="toggleshairport1" value="1" {{ $shairport == 1 ? "checked=\"checked\"" : "" }}>
						

						<label class="toggle-radio" for="toggleshairport1">OFF</label>

             
<input type="radio" name="shairport" id="toggleshairport2" value="0" {{ $shairport == 0 ? "checked=\"checked\"" : "" }}>
					
						
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label">UPNP Control</label>
				<div class="controls">
					<div class="toggle">
						<label class="toggle-radio" for="toggleupnpmpdcli2" >ON</label>
						
<input type="radio" name="upnpmpdcli" id="toggleupnpmpdcli1" value="1" {{ $upnpmpdcli == 1 ? "checked=\"checked\"" : "" }}>

						<label class="toggle-radio" for="toggleupnpmpdcli1">OFF</label>
<input type="radio" name="upnpmpdcli" id="toggleupnpmpdcli2" value="0" {{ $upnpmpdcli == 0 ? "checked=\"checked\"" : "" }}>
           	
						
					</div>
				</div>
			</div> 
			<div class="form-group">
				<label class="control-label">UPNP\DLNA Indexing</label>
				<div class="controls">
					<div class="toggle">
						<label class="toggle-radio" for="toggledjmount2" >ON</label>
						
<input type="radio" name="djmount" id="toggledjmount1" value="1" {{ $djmount == 1 ? "checked=\"checked\"" : "" }}>


						<label class="toggle-radio" for="toggledjmount1">OFF</label>


<input type="radio" name="djmount" id="toggledjmount2" value="0" {{ $djmount == 0 ? "checked=\"checked\"" : "" }}>
		
						
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label">DLNA Library Server</label>
				<div class="controls">
					<div class="toggle">
						<label class="toggle-radio" for="toggleminidlna2" >ON</label>
						
<input type="radio" name="minidlna" id="toggleminidlna1" value="1" {{ $minidlna == 1 ? "checked=\"checked\"" : "" }}>

						<label class="toggle-radio" for="toggleminidlna1">OFF</label>

<input type="radio" name="minidlna" id="toggleminidlna2" value="0" {{ $minidlna == 0 ? "checked=\"checked\"" : "" }}>
	
						
					</div>
				</div>
			</div>
			<div class="form-actions">
				<button class="btn btn-primary btn-lg" value="apply" name="apply" type="submit">Apply</button>
			</div>
		</fieldset>
	</form>
	<form class="form-horizontal" method="post">
		<fieldset>
			<legend>Streaming Services</legend>
			<p>Enable or disable Spotify©  Streaming Service (Requires a Spotify© Premium Account)</p>
			<div class="form-group">
				<label class="control-label">Spotify Service</label>
				<div class="controls">
					<div class="toggle">
						<label class="toggle-radio" for="togglespotify2" >ON</label>
						
<input type="radio" name="spotify" id="togglespotify1" value="1" {{ $spotify == 1 ? "checked=\"checked\"" : "" }}>


						<label class="toggle-radio" for="togglespotify1">OFF</label>
<input type="radio" name="spotify" id="togglespotify2" value="0" {{ $spotify == 0 ? "checked=\"checked\"" : "" }}>
			
						
					</div>
				</div>
			<div id="displayspotifyblock" class="form-group subgroup">	
			<div class="form-group">
				<label class="control-label" for="spotusername">Spotify Username</label>
				<div class="controls">
					<input class="input-large" class="form-control" type="text" id="spotusername" name="spotusername" value={{ $spotusername }}" data-trigger="change" >
				</div>
			</div>
			<div class="form-group">
				<label class="control-label" for="spotpassword">Spotify Password</label>
				<div class="controls">
					<input class="input-large" class="form-control" type="password" id="spotpassword" name="spotpassword" value="" data-trigger="change" >
				</div>
			</div>
				
			
				<label class="control-label">Prefer Higher Quality Stream</label>
				<div class="controls">
					<div class="toggle">
						<label class="toggle-radio" for="togglespotifybitrate2" >ON</label>
						
<input type="radio" name="spotifybitrate" id="togglespotifybitrate1" value="1" {{ $spotifybitrate == 1 ? "checked=\"checked\"" : "" }}>
		

						<label class="toggle-radio" for="togglespotifybitrate1">OFF</label>
<input type="radio" name="spotifybitrate" id="togglespotifybitrate2" value="0" {{ $spotifybitrate == 0 ? "checked=\"checked\"" : "" }}>			
						
					</div>
				<p>
				Retrieve 320kbps Streams. Turn this off if you experience dropouts or if you have bandwith concerns.
			</p>	
				</div>
			</div>
			</div>
			<div class="form-actions">
				<button class="btn btn-primary btn-lg" value="apply" name="apply" type="submit">Apply</button>
			</div>
		</fieldset>
	</form>
	<div $_divi2s >
	<form class="form-horizontal" method="post">
		<fieldset>
			<legend>I2S driver</legend>
			<p>Activate i2s driver for Raspberry PI compatible i2s DACs</p>
			<div class="form-group">
				<label class="control-label">I2S DAC</label>
				<div class="controls">
					<select class="input-large" name="i2s">		
                    <option value="i2soff" {{ $i2s == 'i2soff' ? "selected" : "" }}>None</option>
                    <option value="Hifiberry" {{ $i2s == 'Hifiberry' ? "selected" : "" }}>Hifiberry</option>
                    <option value="Hifiberryplus" {{ $i2s == 'Hifiberryplus' ? "selected" : "" }}>Hifiberry +</option>
                    <option value="HifiberryDigi" {{ $i2s == 'HifiberryDigi' ? "selected" : "" }}>Hifiberry Digi</option>
                    <option value="HifiberryAmp" {{ $i2s == 'HifiberryAmp' ? "selected" : "" }}>Hifiberry Amp</option>
                    <option value="Iqaudio" {{ $i2s == 'Iqaudio' ? "selected" : "" }}>IQaudIO DAC</option>
                    <option value="IqaudioDacPlus" {{ $i2s == 'IqaudioDacPlus' ? "selected" : "" }}>IQaudIO DAC Plus</option>
                    <option value="RpiDac" {{ $i2s == 'RpiDac' ? "selected" : "" }}>RPi-DAC</option>
                    <option value="Generic" {{ $i2s == 'Generic' ? "selected" : "" }}>Generic</option>

					</select>
				</div>
			</div>		
			<div class="form-actions">
				<button class="btn btn-primary btn-lg" value="apply" name="apply" type="submit">Apply</button>
			</div>
		</fieldset>
	</form>
	</div>
<form class="form-horizontal" method="post">
		<fieldset>
			<legend>Miscellaneous</legend>
			<p>Various & useful system settings</p>
			<div class="form-group">
				<label class="control-label">Startup Sound</label>
				<div class="controls">
					<div class="toggle">
						<label class="toggle-radio" for="togglestartupsound2" >ON</label>
					    <input type="radio" name="startupsound" id="togglestartupsound1" value="1" {{ $startupsound == 1 ? "checked=\"checked\"" : "" }}>

						<label class="toggle-radio" for="togglestartupsound1">OFF</label>
                        <input type="radio" name="startupsound" id="togglestartupsound2" value="0" {{ $startupsound == 0 ? "checked=\"checked\"" : "" }}>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label">Library view</label>
				<div class="controls">
					<div class="toggle">
						<label class="toggle-radio" for="toggledisplaylib2" >ON</label>
						<input type="radio" name="displaylib" id="toggledisplaylib1" value="1" {{ $displaylib == 1 ? "checked=\"checked\"" : "" }}>
			
						<label class="toggle-radio" for="toggledisplaylib1">OFF</label>
                        <input type="radio" name="displaylib" id="toggledisplaylib2" value="0" {{ $displaylib == 0 ? "checked=\"checked\"" : "" }}>
					</div>
					<span class="help-block">The library view will be displayed only when this setting is enabled AND, because of its layout, if the screen resolution allows it (bigger than 800x600).
					WARNING: activating the library panel may have an impact on the client-side performances.</span>
				</div>
			</div>
			<div id="displaylibastabblock" class="form-group subgroup">
				<label class="control-label">Show as tab</label>
				<div class="controls">
					<div class="toggle">
						<label class="toggle-radio" for="toggledisplaylibastab2" >ON</label>
						
                        <input type="radio" name="displaylibastab" id="toggledisplaylibastab1" value="1" {{ $displaylibastab == 1 ? "checked=\"checked\"" : "" }}>


						<label class="toggle-radio" for="toggledisplaylibastab1">OFF</label>
                        <input type="radio" name="displaylibastab" id="toggledisplaylibastab2" value="0" {{ $displaylibastab == 0 ? "checked=\"checked\"" : "" }}>

					</div>
					<span class="help-block">If turned off, the library will be accessible from the "Browse" view.</span>
				</div>
			</div>
			<div class="form-actions">
				<button class="btn btn-primary btn-lg" value="apply" name="apply" type="submit">Apply</button>
			</div>
		</fieldset>
	</form>
	<form class="form-horizontal" method="post">
		<fieldset>
			<legend>Sound quality tweaks</legend>
			<p>
				These profiles include a set of performance tweaks that act on some system kernel parameters.<br>
				It does not have anything to do with DSPs or other sound effects: the output is kept untouched (bit perfect).<br>
				It happens that these parameters introduce an audible impact on the overall sound quality, acting on kernel latency parameters (and probably on the amount of overall<a href="http://www.thewelltemperedcomputer.com/KB/BitPerfectJitter.htm" title="Bit Perfect Jitter by Vincent Kars" target="_blank"> jitter</a>).<br>
				Sound results may vary depending on where music is listened, so choose according to your personal taste.<br>
				(If you can't hear any tangible differences... nevermind, just stick to the default settings.)
			</p>
			<div class="form-group">
				<label class="control-label">Kernel profile</label>
				<div class="controls">
					<select class="input-large" name="orionprofile">
                        <option value="default" {{ $orionprofile == 'default' ? "selected" : "" }}>default</option>
                        <option value="ACX" {{ $orionprofile == 'ACX' ? "selected" : "" }}>ACX</option>
                        <option value="Buscia" {{ $orionprofile == 'Buscia' ? "selected" : "" }}>Buscia</option>
                        <option value="Mike" {{ $orionprofile == 'Mike' ? "selected" : "" }}>Mike</option>
                        <option value="Orion" {{ $orionprofile == 'Orion' ? "selected" : "" }}>Orion</option>
					</select>
				</div>
			</div>
			<div class="form-actions">
				<button class="btn btn-primary btn-lg" value="save" name="save" type="submit">Apply settings</button>
			</div>
		</fieldset>
	</form>			
	<form class="form-horizontal" method="post">
		<fieldset>
			<legend>Compatibility fixes</legend>
			<p>For people suffering problems with some receivers and DACs.</p>
			<div class="form-group">
				<label class="control-label">CMedia fix</label>
				<div class="controls">
					<div class="toggle">
						<label class="toggle-radio" for="toggleOption2" >ON</label>
                        <input type="radio" name="cmediafix" id="togglecmedia1" value="1" {{ $cmediafix == 1 ? "checked=\"checked\"" : ""}}">

						<label class="toggle-radio" for="toggleOption1">OFF</label>

                        <input type="radio" name="cmediafix" id="togglecmedia2" value="0" {{ $cmediafix == 0 ? "checked=\"checked\"" : "" }}>

					</div>
					<span class="help-block">For those who have a CM6631 receiver and experiment issues (noise, crackling) between tracks with different sample rates and/or bit depth.<br> 
					A "dirty" fix that should avoid the problem, do NOT use if everything works normally.</span>
				</div>
			</div>
			<div class="form-actions">
				<button class="btn btn-primary btn-lg" value="apply" name="apply" type="submit">Apply fixes</button>
			</div>
		</fieldset>
	</form>
	<form class="form-horizontal" method="post">
		<fieldset>
			<legend>System Updates</legend>
			<p>&nbsp;</p>
			<div class="form-group">
				<label class="control-label">Check and retrieve System Updates</label>
				<div class="controls">
				<p><button class="btn btn-lg btn-primary" type="submit" name="syscmd" value="updateui" id="syscmd-updateui"><i class="fa fa-paper-plane-o sx"></i>Check Updates</button></p>
				<span class="help-block">You can check wether minor updates are available. The system will then retrieve them, if found. You'll be then asked to apply or ignore.</span>
				</div>
			</div>
					</fieldset>
	</form>
	<!--
	<form class="form-horizontal" data-validate="parsley" action="" method="post">
        <fieldset>
            <legend>Last.FM scrobbling</legend>
            <div class="form-group" >
                <label class="control-label" for="port">Port</label>
                <div class="controls">
                    <input class="input-large" class="input-large" type="text" id="lastfm_user" name="lastfm[user]" value="$_lastfm[user]" data-trigger="change">
					<input class="input-large" class="input-large" type="text" id="lastfm_pass" name="lastfm[pass]" value="$_lastfm[pass]" data-trigger="change">
                    <span class="help-block">This setting is the TCP port that is desired for the daemon to get assigned to.</span>
                </div>
            </div>
		</fieldset>
<form class="form-horizontal" method="post">
		<fieldset>
			<legend>Backup / Restore configuration</legend>
			<p>&nbsp;</p>
			<div class="form-group">
				<label class="control-label">Backup player config</label>
				<div class="controls">
					<input class="btn" type="submit" name="syscmd" value="backup" id="syscmd-backup">
				</div>
			</div>
					</fieldset>
	</form>
	<form class="form-horizontal" method="post">
		<fieldset>
			<div class="form-group" >
				<label class="control-label" for="port">Configuration file</label>
				<div class="controls">
			
			<div class="fileupload fileupload-new" data-provides="fileupload">
					  <span class="btn btn-file"><span class="fileupload-new">restore</span><span class="fileupload-exists">Change</span><input type="file" /></span>
					  <span class="fileupload-preview"></span>
					  <a href="#" class="close fileupload-exists" data-dismiss="fileupload" style="float: none">×</a>
					</div>
				</div>
			</div>
			<div class="form-actions">
				<button class="btn btn-primary btn-lg" value="restore" name="syscmd" type="submit">Restore config</button>
			</div>
		</fieldset>
	</form>
		-->
</div>
@endsection