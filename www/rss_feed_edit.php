#!/usr/local/bin/php
<?php
/*
	disks_manage_edit.php
	part of FreeNAS (http://www.freenas.org)
	Copyright (C) 2005-2008 Olivier Cochard-Labbe <olivier@freenas.org>.
	All rights reserved.

	Based on m0n0wall (http://m0n0.ch/wall)
	Copyright (C) 2003-2006 Manuel Kasper <mk@neon1.net>.
	All rights reserved.

	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:

	1. Redistributions of source code must retain the above copyright notice,
	   this list of conditions and the following disclaimer.

	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.

	THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
	INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
	AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
	AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
	OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
	SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
	INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
	CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
	ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
	POSSIBILITY OF SUCH DAMAGE.
*/
require("guiconfig.inc");

$id = $_GET['id'];
if (isset($_POST['id']))
	$id = $_POST['id'];

$pgtitle = array(gettext('Extensions'), gettext("RSS"),gettext("Feed"),isset($id)?gettext("Edit"):gettext("Add"));

if (!is_array($config['rss'])) $config['rss'] = array();
if (!is_array($config['rss']['feeds'])) $config['rss']['feeds'] = array('rule'=>array());
if (!is_array($config['rss']['feeds']['rule'])) $config['rss']['feeds']['rule'] = array();

array_sort_key($config['rss']['feeds']['rule'], "name");

$a_feed = &$config['rss']['feeds']['rule'];

if (isset($id) && $a_feed[$id]) {
	$pconfig['uuid'] = $a_feed[$id]['uuid'];
	$pconfig['name'] = $a_feed[$id]['name'];
	$pconfig['_url'] = $a_feed[$id]['_url'];
    $pconfig['subscribe'] = $a_feed[$id]['subscribe'];
	$pconfig['enabled'] = $a_feed[$id]['enabled'];
    $pconfig['directory'] = $a_feed[$id]['directory'];
} else {
	$pconfig['uuid'] = uuid();
	$pconfig['name'] = '';
	$pconfig['_url'] = '';
    $pconfig['subscribe'] = false;
	$pconfig['enabled'] = true;
    $pconfig['updated'] = 'Never';
    $pconfig['directory'] = '';
}

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	/* check for name conflicts */
	foreach ($a_feed as $feed) {
		if (isset($id) && ($a_feed[$id]) && ($a_feed[$id] === $feed))
			continue;

		if ($feed['name'] == $_POST['name']) {
			$input_errors[] = gettext("This feed already exists in the feed list.");
			break;
		}
	}
    
    $reqdfields = explode(" ", "name _url");
    $reqdfieldsn = array(gettext("Name"), gettext("URL"));
    
    do_input_validation($_POST, $reqdfields, $reqdfieldsn, &$input_errors);
    
    if (!empty($_POST['directory']) && !is_dir($_POST['directory'])) $input_errors[] = gettext("Download directory does not exist.");

	if (!$input_errors) {
        $feed = array();
        $feed['uuid'] = $_POST['uuid'];
        $feed['name'] = $_POST['name'];
        $feed['_url'] = $_POST['_url'];
        $feed['enabled'] = $_POST['enabled'] ? true : false;
        $feed['subscribe'] = $_POST['auto'] ? true : false;

		if (isset($id) && $a_feed[$id]) {
			$a_feed[$id] = $feed;
		} else {
			$a_feed[] = $feed;
		}

		write_config();

		header("Location: rss_feed_manage.php");
		exit;
	}
}


?>
<?php include("fbegin.inc");?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabact"><a href="rss_feed_manage.php" title="<?=gettext("Reload page");?>"><span><?=gettext("Feeds");?></span></a></li>
				<li class="tabinact"><a href="rss_filter_manage.php"><span><?=gettext("Filters");?></span></a></li>
                <li class="tabinact"><a href="rss_history.php"><span><?=gettext("History");?></span></a></li>
			</ul>
		</td>
	</tr>
	<tr>
		<td class="tabcont">
			<form action="rss_feed_edit.php" method="post" name="iform" id="iform">
				<?php if ($input_errors) print_input_errors($input_errors); ?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
					<?php html_inputbox("name", gettext("Name"), $pconfig['name'], gettext("You may enter a name here for your reference."), true, 40);?>
                    <?php html_inputbox("_url", gettext("URL"), $pconfig['_url'], gettext("Enter the URL of the feed."), true, 40); ?>
                    <?php html_inputbox("directory", gettext("Save in"), $pconfig['directory'], gettext("Download feed items to this folder.  This is overriden by filters"), false, 40); ?>
                    <tr>
						<td width="22%" valign="top" class="vncell"><?=gettext("Subscribe");?></td>
                        <td width="78%" class="vtable">
							<input name="subscribe" type="checkbox" id="subscribe" value="yes" <?php if ($pconfig['subscribe']) echo "checked";?>>
							<span class="vexpl"><?=gettext("Enable to download all new feed items (ie. no filtering).");?></span>
                        </td>
                    </tr>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gettext("Enabled");?></td>
                        <td width="78%" class="vtable">
							<input name="enabled" type="checkbox" id="enabled" value="yes" <?php if (isset($pconfig['enabled'])) echo "checked";?>>
							<span class="vexpl"><?=gettext("Enable downloading and filtering of this feed.");?></span>
                        </td>
                    </tr>
				</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=((isset($id) && $a_feed[$id]))?gettext("Save"):gettext("Add")?>">
					<input name="uuid" type="hidden" value="<?=$pconfig['uuid'];?>">
					<?php if (isset($id) && $a_feed[$id]): ?>
					<input name="id" type="hidden" value="<?=$id;?>">
					<?php endif; ?>
				</div>
			</form>
		</td>
	</tr>
</table>
<?php include("fend.inc");?>
