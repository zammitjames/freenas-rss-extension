#!/usr/local/bin/php -f
<?php
require('guiconfig.inc');

$pgtitle = array(gettext('Extensions'), gettext('RSS'), gettext('Filters'));
if (!is_array($config['rss'])) $config['rss'] = array();
if (!is_array($config['rss']['filters'])) $config['rss']['filters'] = array('rule'=>array());

array_sort_key($config['rss']['filters']['rule'], "name");

$a_filters = &$config['rss']['filters']['rule'];
$a_feeds = &$config['rss']['feeds']['rule'];

if ($_GET['act'] === "del") {
	if ($a_feeds[$_GET['id']]) {
        unset($config['rss']['filters']['rule'][$_GET['id']]);
        write_config();
	}
}

function feed_by_uuid($uuid) {
  global $a_feeds;
  foreach ($a_feeds as $feed) {
    if ($feed['uuid'] == $uuid) return $feed['name'];
  }

  return 'Invalid Feed';
}

include("fbegin.inc");
?>
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
			<form action="rss_feed_manage.php" method="post">
				<?php if ($savemsg) print_info_box($savemsg); ?>
				<table width="100%" border="0" cellpadding="0" cellspacing="0">
					<tr>
						<td width="10%" class="listhdrr"><?=gettext("Name"); ?></td>
						<td width="65%" class="listhdrr"><?=gettext("Filter"); ?></td>
                        <td width="10%" class="listhdrr"><?=gettext("Feed"); ?></td>
						<td width="5%" class="listhdrr"><?=gettext("Enabled"); ?></td>
						<td width="10%" class="list"></td>
					</tr>
					<?php $i = 0; foreach ($a_filters as $filter):
                        $enable = isset($filter['enabled']);
                    ?>
					<tr>
						<td class="<?=$enable?"listlr":"listlrd";?>"><?=htmlspecialchars($filter['name']);?></td>
						<td class="<?=$enable?"listrc":"listrcd";?>"><?=htmlspecialchars($filter['filter']);?></td>
                        <td class="<?=$enable?"listrc":"listrcd";?>"><?php
                            if($filter['feed'] != -1) echo htmlspecialchars(feed_by_uuid($filter['feed']));
                        ?>&nbsp;</td>
						<td class="<?=$enable?"listrc":"listrcd";?>">
                            <?php if ($enable):?>
                            <img src="status_enabled.png" border="0">
                            <?php else:?>
                            <img src="status_disabled.png" border="0">
                            <?php endif;?>
                        </td>
						<td valign="middle" nowrap class="list">
							<a href="rss_filter_edit.php?id=<?=$i;?>"><img src="e.gif" title="<?=gettext("Edit filter");?>" border="0"></a>&nbsp;
							<a href="rss_filter_manage.php?act=del&id=<?=$i;?>" onclick="return confirm('<?=gettext("Do you really want to delete this filter?"); ?>')"><img src="x.gif" title="<?=gettext("Delete filter"); ?>" border="0"></a>
						</td>
					</tr>
					<?php $i++; endforeach;?>
					<tr>
						<td class="list" colspan="6"></td>
						<td class="list"> <a href="rss_filter_edit.php"><img src="plus.gif" title="<?=gettext("Add feed"); ?>" border="0"></a></td>
					</tr>
				</table>
			</form>
        </td>
	</tr>
</table>
<?php include("fend.inc");?>