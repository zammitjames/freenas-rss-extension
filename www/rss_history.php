#!/usr/local/bin/php -f
<?php
require_once("auth.inc");
require_once('guiconfig.inc');
require_once('ext/RSS/rss_functions.inc');
require_once('ext/RSS/rss_class_history.php');

$pgtitle = array(gettext('Extensions'), gettext('RSS'), gettext('History'));
if (!is_array($config['rss'])) $config['rss'] = array();
if (!is_array($config['rss']['feeds'])) $config['rss']['feeds'] = array('rule'=>array());
if (!is_array($config['rss']['filters'])) $config['rss']['filters'] = array('rule'=>array());

array_sort_key($config['rss']['feeds']['rule'], 'name');
array_sort_key($config['rss']['filters']['rule'], 'name');

$a_feeds = array_values($config['rss']['feeds']['rule']);
$a_filters = array_values($config['rss']['filters']['rule']);

$History = new History($config['rss']);
$History->read();
$list = array();

if (isset($_POST['act']) && $_POST['act'] === "down") {
    if (isset($_POST['id']) && isset($_POST['did'])) {
        // Returned by reference so we can work on item directly
        $item = &$History->find($a_feeds[$_POST['id']]['uuid'], $_POST['did']);
        $directory = '';
        
        if (isset($item['filter'])) {
            $directory = get_by_uuid($a_filters, $item['filter'], 'directory');
        }
        
        if(empty($directory))
            $directory = $a_feeds[$_POST['id']]['directory'];
        
        if(add_torrent($item['link'], $directory, $a_feeds[$_POST['id']]['cookie']) == 0) {
            $savemsg = "Successfully downloaded ";
            $item['downloaded'] = true;
            $History->write();
        }
        else
            $savemsg = "Error downloading ";
        $savemsg .= "\"{$item['title']}\"";
    }
}

function show_matched($elem) {
	if ($elem['filter'] && get_by_uuid($a_filters, $elem['filter']) != null) return TRUE;
	return FALSE;
}

if (isset($_REQUEST['id'])) {
    $id = $_REQUEST['id'];
    $list = $History->full($a_feeds[$id]['uuid']);
    if (is_array($list)) {
        usort($list, 'usort_by_pubdate'); // Do this in rss_cron.php
		if (isset($_REQUEST['filter_only'])) {
			$list = array_filter($list, show_matched)
		}
		
		$page = (isset($_REQUEST['page']) ? $_REQUEST['page'] - 1 : 0);
		$per_page = (isset($_REQUEST['per_page']) ? $_REQUEST['per_page'] : 25);
		$list = array_slice($list, ($per_page * $page), $per_page, true);
	}
}

include("fbegin.inc");
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td class="tabnavtbl">
        <ul id="tabnav">
                <li class="tabinact"><a href="extension_rss_feed_manage.php"><span><?=gettext("Feeds");?></span></a></li>
                <li class="tabinact"><a href="extension_rss_filter_manage.php"><span><?=gettext("Filters");?></span></a></li>
                <li class="tabact"><a href="extension_rss_history.php" title="<?=gettext("Reload page");?>"><span><?=gettext("History");?></span></a></li>
                <li class="tabinact"><a href="extension_rss_about.php"><span><?=gettext('About');?></span></a></li>
        </ul>
        </td>
    </tr>
    <tr>
        <td class="tabcont">
        <form action="extension_rss_history.php" method="post">
            Select Feed: 
            <select name='id' onchange="submit()">
                <?php if (!isset($id)): ?><option></option><?php endif; ?>
                <?php $i = 0; foreach ($a_feeds as $feed): ?>
                <option value='<?=$i;?>' <?php if (isset($id) && $id == $i):?>selected='selected'<?php endif; ?>><?=$feed['name'];?></option>
                <? $i++; endforeach; ?>
            </select>
            <?php include("formend.inc");?>
        </form>
        </td>
    </tr>
<?php if (isset($id)): ?>
  <tr>
    <td class="tabcont">
            <?php if (is_array($list)): ?>
            <form action="extension_rss_history.php" method="post">
                <?php if (isset($savemsg)) print_info_box($savemsg); ?>
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td width="75%" class="listhdrlr"><?=gettext("Title"); ?></td>
                        <td width="20%" class="listhdrr"><?=gettext("Date"); ?></td>
                        <td width="5%" class="listhdrr"><?=gettext("Downloaded"); ?></td>
                        <!-- td width="10%" class="list"></td -->
                    </tr>
                    <?php $i = 0; foreach ($list as $entry): ?>
                    <tr>
                        <td class="listlr">
                            <?php if (isset($entry['description']) && !empty($entry['description'])): ?>
                            <img src="/ext/RSS/bullet_toggle_plus.png" alt="[more]" style='vertical-align: bottom; cursor: pointer' onclick="showdesc('desc<?=$i?>', this);" />
                            <?php endif; ?>
                            <?=htmlspecialchars($entry['title']);?>
                            <?php if ($entry['filter'] && get_by_uuid($a_filters, $entry['filter']) != null): ?> <img src="/ext/RSS/lightning.png" alt="filtered" title="Matched filter: <?=get_by_uuid($a_filters, $entry['filter'], 'name'); ?>" /><?php endif; ?></td>
                        <td class="listrc"><?=htmlspecialchars(date(DATE_RSS, strtotime($entry['pubDate'])));?></td>
                        <td class="listrc">
                            <?php if ($entry['downloaded']):?>
                            <img src="status_enabled.png" border="0">
                            <?php else:?>
                            <form action="extension_rss_history.php" method="post">
                            <input type="hidden" name="act" value="down" />
                            <input type="hidden" name="id" value="<?=$id;?>" />
                            <input type="hidden" name="did" value="<?=$entry['guid']; ?>" />
                            <input type="image" src="status_disabled.png" onclick="submit();">
                            <?php include("formend.inc");?>
                            </form>
                            <?php endif;?>
                        </td>
                        <!-- td valign="middle" nowrap class="list">
                            <a href="extension_rss_filter_manage.php?act=del&id=<?=$i;?>" onclick="return confirm('<?=gettext("Do you really want to delete this entry?"); ?>')"><img src="x.gif" title="<?=gettext("Delete filter"); ?>" border="0"></a>
                        </td -->
                    </tr>
                    <?php if (isset($entry['description']) && !empty($entry['description'])): ?>
                    <tr>
                        <?php // This could be dangerous as we displaying all the description including any HTML ?>
                        <td class="listlr" id="desc<?=$i?>" style="display:none" colspan="3"><?=$entry['description']; ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php $i++; endforeach;?>
                </table>
                <?php include("formend.inc");?>
            </form>
            <?php
            else:
                print_error_box(gettext("There is no history, yet!"));
            endif;
            ?>
        </td>
    </tr>
<?php endif; ?>
</table>
<script type="text/javascript">
function showdesc(id, elem) {
    var el = document.getElementById(id);
    if (el.style.display != 'none') {
        elem.src = '/ext/RSS/bullet_toggle_plus.png';
        elem.alt = ' [more] ';
        el.style.display = 'none';
    }
    else {
        elem.src = '/ext/RSS/bullet_toggle_minus.png';
        elem.alt = ' [less] ';
        el.style.display = '';
    }
}
</script>
<?php include("fend.inc");?>
