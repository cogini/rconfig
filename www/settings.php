<?php include("includes/head.inc.php"); ?>
<body>
<!-- Masthead Include -->    
<?php include("includes/masthead.inc.php"); ?>

<div id="mainwrap">
<!-- TopNav Include -->    
<?php include("includes/topnav.inc.php"); ?>
<?php 
// start DB for queries on this page
	require_once("../classes/db.class.php");
	$db = new db();
?>
	<div id="main">
	<!-- Breadcrumb Include -->    
	<?php include("includes/breadcrumb.inc.php"); ?>
		
	<!-- Announcement Include -->    
	<?php include("includes/announcement.inc.php"); ?>
	
			<div id="content"> <!-- Main Content Start-->
			<?php 
				// echo error message if is sent back in GET from CRUD
				if(isset($_SESSION['errors'])){
				// move nested errors array to new array
				$errors = $_SESSION['errors'];	
				}
				/* "Do NOT unset the whole $_SESSION with unset($_SESSION) as this will disable the registering of session variables through the $_SESSION superglobal." */
				$_SESSION['errors'] = array();

			?>

					<fieldset id="settings">
						<legend>Server Details</legend>
				
					<?php
						// set vars for page output
						$ds = disk_total_space("/");
						$fs = disk_free_space("/");
						
						$q = $db->q("SELECT defaultNodeUsername, defaultNodePassword, defaultNodeEnable, commandDebug, commandDebugLocation, deviceConnectionTimout FROM settings WHERE id = 1");
						$result = mysql_fetch_assoc($q);
						$defaultNodeUsername = $result['defaultNodeUsername'];
						$defaultNodePassword = $result['defaultNodePassword'];
						$defaultNodeEnable = $result['defaultNodeEnable'];
						$status = $result['commandDebug'];
						$debugLocation = $result['commandDebugLocation'];
						$timeout = $result['deviceConnectionTimout'];
					?>	
					<div style="width:60%;">
						<div class="tableSummary">
						
							<div class="row">
							  <div class="cell">
								CPU
							  </div>
							  <div class="cell last">
								<?php echo get_cpu_type(); ?>
							  </div>
							 </div>
							 
							<div class="row even">
							  <div class="cell">
								Memory Free
							  </div>
							  <div class="cell last">
								<?php echo get_memory_free()."%"; ?>
							  </div>
							</div>
							
							<div class="row">
							  <div class="cell">
								Memory Total
							  </div>
							  <div class="cell last">
								<?php echo _format_bytes(get_memory_total()); ?>
							  </div>
							 </div>
							 
							<div class="row even">
							  <div class="cell">
								Disk Size
							  </div>
							  <div class="cell last">
								<?php echo _format_bytes($ds); ?>
							  </div>
							</div>
							
							<div class="row">
							  <div class="cell">
								Disk Free
							  </div>
							  <div class="cell last">
								<?php echo _format_bytes($fs); ?>
							  </div>
							 </div>
						</div>
						<br/>
						<div class="spacer"></div>
						<label>Timezone </label>
						<select id="timeZone" name="timeZone" onChange="timeZoneChange()">
							<option value="" selected>Select</option>
							<?php
								$timezone_identifiers = DateTimeZone::listIdentifiers();
								for ($i=0; $i < count($timezone_identifiers); $i++) {
									echo "<option value=\"$timezone_identifiers[$i]\">$timezone_identifiers[$i]</option>";
								}
							?>
						</select>
					<div class="spacer"></div>
						
						<div id="timeZoneNoticeDiv"></div>

					<div class="spacer"></div>
					  </div>
					</fieldset>
				
					<fieldset id="settings">
						<legend>Device Settings</legend>
					<div id="deviceSettings" class="myform stylizedForm stylized">

				<label>Default Node Username:
					</label>
					<input type="text" value="<?php echo $defaultNodeUsername;?>" id="defaultNodeUsername" name="defaultNodeUsername" placeholder="username" />
				<label>Default Node Password:
					</label>
					<input type="password" value="<?php echo $defaultNodePassword;?>" id="defaultNodePassword" name="defaultNodePassword" placeholder="password" />
				<label>Default Enable Mode Password:
					</label>
					<input type="password" value="<?php echo $defaultNodeEnable;?>" id="defaultNodeEnable" name="defaultNodePassword" placeholder="password" />
					
					<button class="smlButton" id="updateDefaultPass" onclick="updateDefaultPass(
					document.getElementById('defaultNodeUsername').value,
					document.getElementById('defaultNodePassword').value,
					document.getElementById('defaultNodeEnable').value
					)">Update</button> 
					<div class="spacer"></div>
					<span  id="updatedDefault" style="display:none; color:green;">Updated!</span>
					<div class="spacer"></div>
					<hr/>
					<br/>							

					
					<label>Connection Timeout:
						<span class="small">Timeout in seconds</span>
					</label>
						<input type="text" value="<?php echo $timeout;?>" id="deviceTout" name="deviceTout" size="1" maxlength="3" style="width:15px;margin-right:5px;"/>
						<button class="smlButton" id="deviceToutGo" onclick="deviceToutGo()">Update</button> 
						<span  id="updated" style="display:none; color:green;">Updated!</span>

					<div class="spacer"></div>
					<br/>		

					<label>Debug device output:
					<span class="small">Turn on device debug</span>
					</label>
						<select id="debugOnOff" name="debugOnOff" onChange="debugOnOff()">
							<option value="" selected>Select</option>
							<option value="0">Off</option>
							<option value="1">On</option>
						</select>
					<div class="spacer"></div>
						
						<div id="debugNoticeDiv"></div>

						<div class="spacer"></div>
						
						<div id="debugInfoDiv">
						  <div class="tableSummary">		
						
							<div id="debugLogFiles">
								<table class="tableSimple">
									<thead>
									<tr>
										<th>Debugging Logs</th>
									</tr>
									</thead>
									<tbody>
									</tbody>
								</table>
								<br/>
							<button class="smlButton" id="deleteDebugsBtn" onclick="deleteDebugFiles('<?php echo $debugLocation ?>', 'txt')">Delete Logs</button> 
							</div>	

						  </div>	
						</div>	

					</div>

					</fieldset>	
				
				    <fieldset id="settings">
				    <legend>Email Settings</legend><a name="emailSettings"></a>
				<form id="emailSettingsForm" method="post" action="lib/crud/settingsEmail.crud.php" enctype="multipart/form-data">
					<?php if(isset($errors['Success'])){echo "<span class=\"error\">".$errors['Success']."</span><br />";}?>
					<?php if(isset($errors['Fail'])){echo "<span class=\"error\">".$errors['Fail']."</span><br />";}?>

					<div id="emailSettingsDiv" class="myform stylizedForm stylized">
					
					<label>Local SMTP Server:
						<span class="small">Server IP or hostname</span>
					</label>
					<input type="text" id="smtpServerAddr" name="smtpServerAddr" placeholder="mail.example.com" />
					<div class="spacer"></div>
					
					<label>From Address:
						<span class="small">Mail from address:</span>
					</label>
					<input type="text" id="smtpFromAddr" name="smtpFromAddr" size="40" placeholder="admin@example.com">
					<?php	// echo error message if is sent back in GET from CRUD
						if(isset($errors['smtpFromAddr'])){echo "<br /><span class=\"error\">".$errors['smtpFromAddr']."</span>";}
					?>					
					<div class="spacer"></div>

					<label>Authentication:</label>
					<input type="checkbox" id="smtpAuth" name="smtpAuth" value="1">
					<div class="spacer"></div>
					
					<div  id="authDiv" style="display:none;">
					<label>Username:</label>
					<input type="text" id="smtpAuthUser" name="smtpAuthUser" size="40" placeholder="username">
					<label>Password:</label>
					<input type="password" id="smtpAuthPass" name="smtpAuthPass" size="40" placeholder="password">
						
					</div>
					<div class="spacer"></div>

					<b>E-mail Recipients	</b><br/>
					Email Recipient Address:<br/><textarea type="textarea" rows="4" cols="30" id="smtpRecipientAddr" name="smtpRecipientAddr" placeholder="user@example.com"></textarea><br/>
						<?php	// echo error message if is sent back in GET from CRUD
							if(isset($errors['smtpRecipientAddr'])){echo "<br /><span class=\"error\">".$errors['smtpRecipientAddr']."</span>";}
						?><br/>
						<em>Seperate multiple address with a semi-colon and a space i.e. user@example.com; user2@example.com</em><br/><br/>

					<button class="smlButton" id="smtpSaveButton" name="smtpSaveButton">Save</button>
					<button class="smlButton" id="smtpUpdateButton" name="smtpUpdateButton">Update</button> 
					<button class="smlButton" id="smtpClearButton" name="smtpClearButton" type="button" onclick="smtpClearSettings()">Clear SMTP Settings</button> <br/><br/>

					<input type="hidden" id="add" name="add" value="add">
					<input type="hidden" id="editid" name="editid" value="">
					<hr/>
					</div>
					</form>

					<div class="spacer"  style="padding-top:10px;"></div>
					Last Test Result: <span id="smtpLastTest" name="smtpLastTest"></span>
					<div class="spacer"></div>
					<button class="smlButton" id="smtpUpdateButton" name="smtpUpdateButton" onclick="smtpTest()">Test Mail Server</button> 
					<span  id="pleaseWait" style="display:none">Please wait... <img src='images/ajax_loader.gif' alt='Please wait... ' /></span>

				  </fieldset>	
				
				    <fieldset id="settings">
				  <legend>Software & Database Details</legend>
				  <?php 
					$dbNameRes = $db->q('SELECT DATABASE()');
					$nodesCntRes = $db->q('SELECT count(*) FROM nodes WHERE status = 1');

					$row=mysql_fetch_row($dbNameRes);
					$nodeCntRow=mysql_fetch_row($nodesCntRes);
					
					$dbName=$row[0];
					$nodesCnt = $nodeCntRow[0];

					?>
					<div style="width:60%;">
						<div class="tableSummary">

							<div class="row even">
							  <div class="cell">
								PHP Version
							  </div>
							  <div class="cell last">
								<?php echo phpversion(); ?>
							  </div>
							</div>							
							
							<div class="row even">
							  <div class="cell">
								OS Version
							  </div>
							  <div class="cell last">
								<?php echo php_uname(); ?>
							  </div>
							</div>
							
							<div class="row">
							  <div class="cell">
								Database Verson
							  </div>
							  <div class="cell last">
								<?php echo mysql_get_server_info(); ?>
							  </div>
							 </div>
							 
							<div class="row">
							  <div class="cell">
								Database Name
							  </div>
							  <div class="cell last">
								<?php echo $dbName; ?>
							  </div>
							 </div>
							 
							<div class="row">
							  <div class="cell">
								Node Count
							  </div>
							  <div class="cell last">
								<?php echo $nodesCnt; ?>
							  </div>
							 </div> 
							 
							<div class="row even">
							  <div class="cell">
								Database Connection
							  </div>
							  <div class="cell last">
								<?php echo mysql_get_host_info(); ?>
							  </div>
							</div>
							
						</div>	
					  </div>
					  <div class="spacer"></div>
						<span class="small">Purge deleted items from all database tables:</span>
							<button class="smlButton" onclick="purgeDevice()">Purge</button>
					<div class="spacer"></div>		

					<span class="small">Turn on PHP Error Logging</span>
						<select id="phpLoggingOnOff" name="phpLoggingOnOff" onChange="phpLoggingOnOff()">
							<option value="" selected>Select</option>
							<option value="0">Off</option>
							<option value="1">On</option>
						</select>
					<div class="spacer"></div>	
						<div id="getPhpLoggingStatusDiv"></div>
						<div class="spacer"></div>					
				  </fieldset>	
			</div><!-- End Content -->
		<div style="clear:both;"></div>
	</div><!-- End Main -->

<!-- JS script Include -->
<script type="text/JavaScript" src="js/settings.js"></script> 


<!-- Footer Include -->    
<?php include("includes/footer.inc.php"); ?>
</div> <!-- End Mainwrap -->
</body>
</html>