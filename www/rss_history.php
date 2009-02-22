#!/usr/local/bin/php -f
<?php
require('guiconfig.inc');

function pubdate_sort($a, $b) {
  return strtotime($a['pubdate']) < strtotime($b['pubdate']);
}

$pgtitle = array(gettext('Extensions'), gettext('RSS'), gettext('History'));
if (!is_array($config['rss'])) $config['rss'] = array();
if (!is_array($config['rss']['feeds'])) $config['rss']['feeds'] = array('rule'=>array());

//$a_filters = &$config['rss']['filters']['rule'];
$a_feeds = &$config['rss']['feeds']['rule'];

/*
if ($_GET['act'] === "del") {
	if ($a_feeds[$_GET['id']]) {
        unset($config['rss']['filters']['rule'][$_GET['id']]);
        write_config();
	}
}
*/
$history = array();
$feeds = array();
foreach ($a_feeds as $feed)
{
    $feeds[$feed['uuid']] = $feed['name'];
    if (!isset($feed['history'])) continue;
    $history = array_merge($history, $feed['history']['rule']);
}

usort($history, "pubdate_sort");
include("fbegin.inc");
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
		<td class="tabnavtbl">
  		<ul id="tabnav">
				<li class="tabinact"><a href="rss_feed_manage.php"><span><?=gettext("Feeds");?></span></a></li>
				<li class="tabinact"><a href="rss_filter_manage.php"><span><?=gettext("Filters");?></span></a></li>
                <li class="tabact"><a href="rss_history.php" title="<?=gettext("Reload page");?>"><span><?=gettext("History");?></span></a></li>
  		</ul>
  	</td>
  </tr>
  <tr>
    <td class="tabcont">
			<form action="rss_feed_manage.php" method="post">
				<?php if ($savemsg) print_info_box($savemsg); ?>
				<table width="100%" border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td width="40%" class="listhdrr"><?=gettext("Title"); ?></td>
                        <td width="15%" class="listhdrr"><?=gettext("Date"); ?></td>
                        <td width="40%" class="listhdrr"><?=gettext("Feed"); ?></td>
						<td width="5%" class="listhdrr"><?=gettext("Downloaded"); ?></td>
						<!-- td width="10%" class="list"></td -->
					</tr>
					<?php $i = 0; foreach ($history as $entry): ?>
					<tr>
						<td class="listlr"><?=htmlspecialchars($entry['title']);?></td>
                        <td class="listrc"><?=htmlspecialchars($entry['pubdate']);?></td>
                        <td class="listrc"><?=htmlspecialchars($feeds[$entry['feed']]);?></td>
						<td class="listrc">
                            <?php if (isset($entry['downloaded'])):?>
                            <img src="status_enabled.png" border="0">
                            <?php else:?>
                            <img src="status_disabled.png" border="0">
                            <?php endif;?>
                        </td>
						<!-- td valign="middle" nowrap class="list">
							<a href="rss_filter_manage.php?act=del&id=<?=$i;?>" onclick="return confirm('<?=gettext("Do you really want to delete this entry?"); ?>')"><img src="x.gif" title="<?=gettext("Delete filter"); ?>" border="0"></a>
						</td -->
					</tr>
					<?php $i++; endforeach;?>
				</table>
			</form>
        </td>
	</tr>
</table>
<?php include("fend.inc");?>
