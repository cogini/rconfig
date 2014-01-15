<div id="breadcrumb">
		<h2>
			<?php
			switch ($config_page){
				case "login.php":
				echo "Login Page";
					break;
				case "compatibility.php":
				echo "Compatibility Page";
					break;
				case "dashboard.php":
				echo "Dashboard";
					break;
				/* Devices Subpages */
					case "devices.php":
					echo "Devices";
						break;
					case "devicemgmt.php":
					echo "Devices > Device Management";
						break;
					case "customProperties.php":
					echo "Devices > Custom Properties";
						break;
					case "categories.php":
					echo "Devices > Categories";
						break;
					case "commands.php":
					echo "Devices > Commands";
						break;
					case "vendors.php":
					echo "Devices > Vendors";
						break;
				/* Config Tools Subpages */
					case "configoverview.php":
					echo "Configurations > Overview";
						break;
					case "configcompare.php":
					echo "Configurations > Comparison";
						break;					
					case "search.php":
					echo "Configurations > Search";
						break;		
					case "configreports.php":
					echo "Reports";
						break;	
					case "configlogging.php":
					echo "Logging Information";
						break;		
				/* Compliance Subpages */
					case "complianceoverview.php":
					echo "Configurations > Compliance Overview";
						break;		
					case "compliancereports.php":
					echo "Configurations > Compliance Reports";
						break;	
					case "compliancepolicies.php":
					echo "Configurations > Compliance Policies";
						break;	
					case "compliancepolicyelements.php":
					echo "Configurations > Compliance Policy Elements";
						break;							
				/* Settings Subpages */
					case "settings.php": 
					echo "General Settings";
						break;
					case "scheduler.php":
					echo "Settings > Scheduled Tasks";
						break;
					case "useradmin.php":
					echo "Settings > Users Management";
						break;
					case "settingsBackup.php":
					echo "Settings > Backup";
						break;
					case "updater.php":
					echo "Update";
						break;
				default:
			echo "<font color=\"red\">Page Title Not Found</font>";			
			}
			?>
			<!-- <img id="helpIcon" src="images/helpIcon16.png" alt="Click for Help!" title="Click for Help!"/> -->
		</h2>
	</div>