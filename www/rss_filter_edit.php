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

$pgtitle = array(gettext('Extensions'), gettext("RSS"),gettext("Filter"),isset($id)?gettext("Edit"):gettext("Add"));

if (!is_array($config['rss'])) $config['rss'] = array();
if (!is_array($config['rss']['filters'])) $config['rss']['filters'] = array('rule'=>array());
if (!is_array($config['rss']['filters']['rule'])) $config['rss']['filters']['rule'] = array();

array_sort_key($config['rss']['filters']['rule'], "name");

$a_filter = &$config['rss']['filters']['rule'];
$a_feed = &$config['rss']['feeds']['rule'];

if (isset($id) && $a_filter[$id]) {
	$pconfig['uuid'] = $a_filter[$id]['uuid'];
	$pconfig['name'] = $a_filter[$id]['name'];
	$pconfig['filter'] = $a_filter[$id]['filter'];
	$pconfig['directory'] = $a_filter[$id]['directory'];
	$pconfig['enabled'] = $a_filter[$id]['enabled'];
	$pconfig['feed'] = $a_filter[$id]['feed'];
} else {
	$pconfig['uuid'] = uuid();
	$pconfig['name'] = '';
	$pconfig['filter'] = '';
	$pconfig['directory'] = '';
	$pconfig['enabled'] = false;
	$pconfig['feed'] = -1;
}

if ($_POST) {
	unset($input_errors);
	$pconfig = $_POST;

	/* check for name conflicts */
	foreach ($a_filter as $filter) {
		if (isset($id) && ($a_filter[$id]) && ($a_filter[$id] === $filter))
			continue;

		if ($filter['name'] == $_POST['name']) {
			$input_errors[] = gettext("This filter already exists in the filter list.");
			break;
		}
	}
	
	$reqdfields = explode(" ", "name filter");
	$reqdfieldsn = array(gettext("Name"), gettext("Filter"));
	do_input_validation($_POST, $reqdfields, $reqdfieldsn, &$input_errors);
	
	if (!empty($_POST['directory']) && !is_dir($_POST['directory'])) $input_errors[] = gettext("Download directory does not exist.");

	if (!$input_errors) {
		$filter = array();
		$filter['uuid'] = $_POST['uuid'];
		$filter['name'] = $_POST['name'];
		$filter['filter'] = $_POST['filter'];
		$filter['directory'] = $_POST['directory'];
		$filter['enabled'] = $_POST['enabled'] ? true : false;
		$filter['feed'] = $_POST['feed'];

		if (isset($id) && $a_filter[$id]) {
			$a_filter[$id] = $filter;
		} else {
			$a_filter[$filter['uuid']] = $filter;
		}

		write_config();

		header("Location: rss_filter_manage.php");
		exit;
	}
}


?>
<?php include("fbegin.inc");?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabinact"><a href="rss_feed_manage.php"><span><?=gettext("Feeds");?></span></a></li>
				<li class="tabact"><a href="rss_filter_manage.php" title="<?=gettext("Reload page");?>"><span><?=gettext("Filters");?></span></a></li>
				<li class="tabinact"><a href="rss_history.php"><span><?=gettext("History");?></span></a></li>
			</ul>
		</td>
	</tr>
	<tr>
		<td class="tabcont">
			<form action="rss_filter_edit.php" method="post" name="iform" id="iform">
				<?php if ($input_errors) print_input_errors($input_errors); ?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
					<?php html_inputbox("name", gettext("Name"), $pconfig['name'], gettext("You may enter a name here for your reference."), true, 40);?>
					<?php html_inputbox("filter", gettext("Filter"), $pconfig['filter'], gettext("Enter a Perl regular expression."), true); ?>
					<?php html_filechooser("directory", gettext("Download directory"), $pconfig['directory'], gettext("Where to save downloaded data."), $g['media_path'], true, 60); ?>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gettext("Enabled");?></td>
						<td width="78%" class="vtable">
							<input name="enabled" type="checkbox" id="enabled" value="yes" <?php if (isset($pconfig['enabled'])) echo "checked";?>>
							<span class="vexpl"><?=gettext("Enable this filter.");?></span>
						</td>
					</tr>
					<tr>
						<td width="22%" valign="top" class="vncell"><?=gettext("Feed");?></td>
						<td width="78%" class="vtable">
							<select name="feed" class="formfld" id="feed">
								<option value="-1" <?if (-1 == $pconfig['feed']) echo 'selected';?>></option>
								<?php foreach ($a_feed as $feedk => $feedv): ?>
								<option value="<?=$feedv['uuid'];?>" <?php if ($feedv['uuid'] == $pconfig['feed']) echo "selected";?>><?php echo htmlspecialchars($feedv['name']);?></option>
								<?php endforeach; ?>
							</select>
					  </td>
					</tr>
				</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=((isset($id) && $a_filter[$id]))?gettext("Save"):gettext("Add")?>">
					<input name="uuid" type="hidden" value="<?=$pconfig['uuid'];?>">
					<?php if (isset($id) && $a_filter[$id]): ?>
					<input name="id" type="hidden" value="<?=$id;?>">
					<?php endif; ?>
				</div>
			</form>
		</td>
	</tr>
</table>
<?php include("fend.inc");?>
