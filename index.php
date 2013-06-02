<?php
	require('classes/File.php');
	$uploadsPath = '/wp-content/uploads';
	$uploadsDirectory = $_SERVER['DOCUMENT_ROOT'] . $uploadsPath;
	$uploadsWebDirectory = $_SERVER['SERVER_NAME'] . $uploadsPath;
	$items = scandir($uploadsDirectory);
	
	$standardFilesList = File::getStandardFilesList();
	$starterFilename = File::getStarterFilename();
	$starterFileDependenciesList = File::getStarterFileDependencies();
	
	$standardFiles = array();
	$starterFileDependencies = array();
	foreach($standardFilesList as $standardFile) {
		$file = File::getFile($standardFile, $uploadsDirectory);
		$standardFiles[] = $file;
		
		if(in_array($file->name, $starterFileDependenciesList)) {
			$starterFileDependencies[] = $file;
		}
	}
	
	$otherFiles = array();
	foreach($items as $item) {
		// Exclude standard files, dot files, and directories from this list
		if(!in_array($item, $standardFilesList)
			&& strpos($item, '.') !== 0
			&& !is_dir($uploadsDirectory . '/' . $item)) {
			
			$file = File::getFile($item, $uploadsDirectory);
			$otherFiles[] = $file;
		}
	}
	
	usort($otherFiles, "sortFilesByDateDesc");
	
	function sortFilesByDateDesc($a, $b) {
		if ($a->modified == $b->modified) {
			return 0;
		}
		return ($a->modified > $b->modified) ? -1 : 1;
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="X-UA-Compatible" content="IE=Edge">
		<meta charset="utf-8">
		<title>Sermon Downloads</title>
		<link href="css/ui-lightness/jquery-ui-1.10.3.custom.css" rel="stylesheet">
		<script src="js/jquery-1.9.1.js"></script>
		<script src="js/jquery-ui-1.10.3.custom.js"></script>
        <style rel="stylesheet">
			html {
				height: 100%;
			}
			body {
				font-family: calibri;
				height: 100%;
				margin: 0px;
				background-color: #e6f2ff;
				word-wrap:break-word;
				color: #00274d;
			}
			#pageWrapper {
				height: 100%;
				margin: 0px auto;
			}
			#standardFiles, #otherFiles {
				background-color: #80c0ff;
				padding: 1% 0%;
				margin: 1.5%;
			}
			#standardFiles h2,
			#otherFiles h2 {
				padding-left: 4%;
			}
			@media all and (min-width: 420px) and (min-device-width: 420px)  {
				#pageWrapper {
					max-width: 1000px;
				}
				#standardFiles, #otherFiles {
					width: 47%;
				}
				#standardFiles {
					float: left;
				}
				#otherFiles {
					float: right;
				}
			}
			h1 {
				text-align: center;
			}
			.filesTable {
				border-collapse: collapse;
				width: 100%;
				table-layout: fixed;
			}
			.headerRow {
				font-weight: bold;
			}
			.modifiedTimeColumn,
			.filenameColumn {
				padding: 1% 4%;
			}
			.modifiedTimeColumn {
				width: 30%;
			}
			.filenameColumn {
				width: 60%;
			}
			
			.showMoreRow {
				font-style: italic;
			}
			
			#notes {
				width: 93%;
				clear: both;
				background-color: #80c0ff;
				margin: 0% 1.5%;
				padding: 0.01% 2%;
			}
			#feedback {
				text-align: right;
			}
		</style>
	</head>
	<body>
		<script>
			var uploadsWebDirectory = '<?= $uploadsWebDirectory; ?>';
			var standardFiles = <?= json_encode($standardFiles); ?>;
			var otherFiles = <?= json_encode($otherFiles); ?>;
			var starterFilename = '<?= $starterFilename; ?>';
			var starterFileDependencies = <?= json_encode($starterFileDependencies); ?>;
			var currentDate = new Date();
			var monthAbbreviation = Array(
				'Jan',
				'Feb',
				'Mar',
				'Apr',
				'May',
				'Jun',
				'July',
				'Aug',
				'Sep',
				'Oct',
				'Nov',
				'Dec'
			);
			
			var dayOfWeekAbbreviation = Array(
				'Sun',
				'Mon',
				'Tues',
				'Wed',
				'Thurs',
				'Fri',
				'Sat'
			);
			
			$(function() {
				var standardFilesTable = new fileTimeTable($('#standardFiles'));
				standardFilesTable.addFileTimes(standardFiles);
				var hideLastWeekOtherFiles = true;
				var otherFilesTable = new fileTimeTable($('#otherFiles'));
				otherFilesTable.addFileTimes(otherFiles, hideLastWeekOtherFiles);
				otherFilesTable.displayMoreLink();
			});
			
			function fileTimeTable(parentElement) {
				var self = this;
				this.table = new $('<table>').appendTo(parentElement).addClass('filesTable');
				
				this.tableHeader = new $('<thead>').appendTo(this.table);
				
				this.headerRow = new $('<tr>').appendTo(this.tableHeader).addClass('headerRow');
				this.fileColumnHeader = new $('<td>').appendTo(this.headerRow)
					.addClass('filenameColumn').text('File');
				this.modifiedColumnHeader = new $('<td>').appendTo(this.headerRow)
					.addClass('modifiedTimeColumn').text('Updated');
				
				this.addFileTime = function(thefile, hideLastWeek) {
					var fileTimeRow = new FileTime(self, thefile, hideLastWeek);
					this.table.append(fileTimeRow);
				}
				
				this.addFileTimes = function(files, hideLastWeek) {
					for(i in files) {
						var file = files[i];
						self.addFileTime(file, hideLastWeek);
					}
				}
				
				this.tableFooter = new $('<tfoot>').appendTo(this.table);
				
				this.displayMoreLink = function() {
					var showAllRow = new $('<tr>').addClass('showMoreRow').appendTo(self.tableFooter);
					var moreTd = new $('<td>').addClass('filenameColumn').appendTo(showAllRow);
					var unhiddenFilesCount = otherFiles.count - self.countHiddenRows();
					var hiddenFilesDescriptor;
					if(unhiddenFilesCount) {
						hiddenFilesDescriptor = ' more files';
					} else {
						hiddenFilesDescriptor = ' files';
					}
					new $('<a>').attr('href', '#').text(self.countHiddenRows() + hiddenFilesDescriptor)
						.click(clickMoreLink).appendTo(moreTd);
					var lastSundayDate = new Date(getLastSundayTimestamp(currentDate.getTime()));
					var lastSundayString = monthAbbreviation[lastSundayDate.getMonth()]
						+ ' ' + lastSundayDate.getDate();
					new $('<td>').text('Before ' + lastSundayString)
						.addClass('modifiedTimeColumn')
						.appendTo(showAllRow);
				}
				
				this.countHiddenRows = function() {
					return $('#otherFiles').find('.filesTable').find('tbody').find('tr').filter(function() { return $(this).css("display") == "none"
					}).length;
				}
			}
			
			function clickMoreLink(e) {
				var parentTable = $(e.target).parents('.filesTable');
				parentTable.find('tr').show();
				parentTable.find('.showMoreRow').hide();
				e.preventDefault();
			}
			
			function FileTime(parentElement, file, hideLastWeek) {
				var self = this;
				
				this.fileExtension = function(filename) {
					var splits = filename.split('.');
					var extension = splits[splits.length-1];
					return extension;
				}
				
				this.getModifiedTimeString = function(file) {
					var modDate = new Date(file.modified*1000);
					var hr = modDate.getHours();
					var min = modDate.getMinutes();
					if(hr < 12) {
						var ampm = 'am';
					} else {
						var ampm = 'pm';
					}
					
					if(hr > 12) {
						var regularHr = hr - 12;
					} else if (hr == 0) {
						var regularHr = 12;
					} else {
						var regularHr = hr;
					}
					
					if(String(min).length == 1) {
						var minutesString = String(0) + String(min);
					} else {
						var minutesString = String(min);
					}
					
					return String(regularHr) + ':' + minutesString + ' ' + ampm;
				}
				
				this.getModifiedDateVerbose = function(file) {
					var modDate = new Date(file.modified*1000);
					var yr = modDate.getFullYear();
					var mo = modDate.getMonth() + 1;
					var day = modDate.getDate();
					var monthString = monthAbbreviation[mo-1];
					var dayOfWeekString = dayOfWeekAbbreviation[modDate.getDay()];
					var timeString = self.getModifiedTimeString(file);
					
					return dayOfWeekString + ', ' + monthString + ' ' + day + ', ' + yr + ' at ' + timeString;
				}
				
				function handleFileTimeMouseIn() {
					self.row.addClass('ui-state-highlight').css({
						'background': 'none',
						'background-color': 'yellow',
						'cursor': 'pointer'
					});
				}
				
				function handleFileTimeMouseOut() {
					self.row.removeClass('ui-state-highlight').css({
						'background': 'none',
						'cursor': 'normal'
					});
				}
				
				this.row = new $('<tr>').attr('title', self.getModifiedDateVerbose(file))
					.css({
						'border': 'none'
					})
					.click(function(e) {
						var ext = self.fileExtension(file.name);
						if(ext == 'mp4') {
							e.preventDefault();
							showRightClickToDownloadDialog();
							return;
						} else {
							if(file.name == starterFilename) {
								var starterFile = file;
								var starterFileNotCurrent = false;
								for(var dependencyIndex in starterFileDependencies) {
									dependency = starterFileDependencies[dependencyIndex];
									if(dependency.modified > starterFile.modified) {
										starterFileNotCurrent = true;
										new $('<li>').text(dependency.name)
											.appendTo($('#unincorporatedStarterFileDependenciesList'));
									}
								}
								
								if(starterFileNotCurrent) {
									$('#dialog_starterFileNotCurrent').dialog({
										modal: true
									});
								}
							}
							$('#downloadFrame').attr('src', '//' + uploadsWebDirectory + '/' + file.name);
						}
					}).hover(handleFileTimeMouseIn, handleFileTimeMouseOut);
				
				this.fileLink = function(filename) {
					if(this.fileExtension(filename) == 'mp4') {
						return new $('<a>').attr('href', '//' + uploadsWebDirectory + '/' + filename).text(filename);
					} else {
						return filename;
					}
				}
				
				
				this.downloadIcon = function() {
					return new $('<span>').addClass('ui-icon ui-icon-circle-arrow-s')
					.css({
						'display': 'inline-block',
						'vertical-align': 'middle',
						'margin-right': '2%'
					});
				}
				
				this.filenameColumn = new $('<td>').appendTo(this.row)
					.addClass('filenameColumn')
					.append(self.downloadIcon())
					.append(this.fileLink(file.name));
				
				this.getModifiedTimeElement = function(timestamp) {
					if(timestamp) {
						var now = currentDate;
						var modDate = new Date(timestamp);
						var yr = modDate.getFullYear();
						var mo = modDate.getMonth() + 1;
						var day = modDate.getDate();
						var monthString = monthAbbreviation[mo-1];

						if(yr == now.getFullYear() && mo == now.getMonth() + 1 && day == now.getDate()) {
							var displayDate = 'Today';
						} else if (yr != now.getFullYear()) {
							var displayDate = String(mo) + '/' + String(day) + '/' + String(yr);
						} else if (timestamp > now.getTime() - 7*24*60*60*1000) {
							var displayDate = monthString + ' ' + day;
						} else {
							var displayDate = monthString + ' ' + day;
						}

						// If timestamp is after last Sunday
						if(timestamp < getLastSundayTimestamp(now.getTime())
							&& hideLastWeek ) {
							self.row.hide();
						}
						return new $('<span>').text(displayDate);
					}
					return new $('<span>').text('Not found');
				}
				
				this.modifiedTimeColumn = new $('<td>').appendTo(this.row)
					.addClass('modifiedTimeColumn')
					.append(self.getModifiedTimeElement(file.modified * 1000));
				
				return this.row;
			}
			
			function getLastSundayTimestamp(timestamp) {
				// If input is a PHP timestamp, multiply by 1000 to use as this function's input
				var thisDate = new Date(timestamp);
				var dayOfWeek = thisDate.getDay();
				
				thisDate.setHours(0);
				thisDate.setMinutes(0);
				thisDate.setSeconds(0);
				thisDate.setMilliseconds(0);
				
				if(dayOfWeek == 0) {
					thisDate.setTime(thisDate.getTime() - 7*24*60*60*1000);
				} else {
					thisDate.setTime(thisDate.getTime() - dayOfWeek*24*60*60*1000);
				}
				return thisDate.getTime();
			}
			
			function showRightClickToDownloadDialog() {
				$('#dialog_rightClickToDownload').dialog({
					modal: true,
					  buttons: {
						Ok: function() {
						  $( this ).dialog( "close" );
						}
					  }
				});
			}
			
		</script>
		
		<div id="pageWrapper">
			
			<h1>Weekly Downloads</h1>
			
			<div id="standardFiles">
				<h2>Standard Sermon Files</h2>
			</div>
			<div id="otherFiles">
				<h2>Other Files</h2>
			</div>
			
			<div id="notes">
				<h2>Notes</h2>
				<ul>
					<li>
						Contains all the files in the 
						<a href="http://go.alpinechurch.org/wp-content/uploads/">uploads directory</a>
						 from go.alpinechurch.org</li>
					<li>"Standard Sermon Files" is a static list of commonly used media team files</li>
					<li>"Other Files" displays files modified since and including the past Sunday</li>
					<li>The starter file contains the countdown, songs, announcements, and slides</li>
					<li>MP4 files can be downloaded by right clicking and choosing "save link as"</li>
				</ul>
				<p id="feedback">To report a bug or request a feature, email Ben (bbankes@gmail.com)</p>
			</div>
			
		</div>
		
		<iframe id="downloadFrame" frameborder="0" height="1"></iframe>
		
		<div id="dialog_rightClickToDownload" title="Alert" style="display:none">
			<p>To download MP4s<p>
			<ol>
				<li>Right click the link</li>
				<li>Choose "Save Link As"</li>
			</ol>
		</div>
		
		<div id="dialog_starterFileNotCurrent" title="Alert" style="display:none">
			<p>The starter files has not been updated since the following files were modified:</p>
			<ul id="unincorporatedStarterFileDependenciesList"></ul>
			<p>You can still download the file, but it may be incorrect.<p>
		</div>
		
	<body>
</html>