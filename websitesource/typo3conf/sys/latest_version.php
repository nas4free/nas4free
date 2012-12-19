<?php
	//Feed URLS
	$rss_stable = "http://sourceforge.net/api/file/index/project-id/722987/path/NAS4Free-9.0-Stable/mtime/desc/limit/1/rss";
	$rss_nightly = "http://sourceforge.net/api/file/index/project-id/722987/path/NAS4Free-9.0-Nightly/mtime/desc/limit/1/rss";
		
	//Get latest STABLE information from RSS feed
	function getFeed_stable($rss_url) {
		$content = file_get_contents($rss_url);
		$stable_dl = "https://sourceforge.net/projects/nas4free/files/NAS4Free-9.0-Stable";
		if (empty($content)) {
			echo "N/A";
		} else {
			$x = new SimpleXmlElement($content);
			foreach($x->channel->item as $entry) {
				$results = $entry->title;
				$stable = substr($results, 22, -23);
				echo "NAS4Free-".$stable."-Stable<br />";
				echo "(<a href=\"".$stable_dl."/".$stable."\" target=\"_blank\"><img src=\"/typo3conf/sys/templates/gfx/icon_dl.png\">Download Now</a>)";
			}
		}
	}
	//Get latest NIGHTLY information from RSS feed
	function getFeed_nightly($rss_url) {
		$content = file_get_contents($rss_url);
		$nightly_dl = "https://sourceforge.net/projects/nas4free/files/NAS4Free-9.0-Nightly";
		if (empty($content)) {
			echo "N/A";
		} else {
			$x = new SimpleXmlElement($content);
			foreach($x->channel->item as $entry) {
				$results = $entry->title;
				$nightly = substr($results, 22, -23);
				echo "NAS4Free-".$nightly."-Nightly<br />";
				echo "(<a href=\"".$nightly_dl."/".$nightly."\" target=\"_blank\"><img src=\"/typo3conf/sys/templates/gfx/icon_dl.png\">Download Now</a>)";				
			}
		}
	}

	//Display actual data from functions
?>
<center>
	<strong>
		<u>
			Latest Stable
		</u>
	</strong>
	<br/>
	N/A
	<?php //getFeed_stable($rss_stable); ?>
	<br />
	<strong>
		<u>
			Latest Nightly
		</u>
	</strong>
	<br />
	<?php getFeed_nightly($rss_nightly); ?>
</center>