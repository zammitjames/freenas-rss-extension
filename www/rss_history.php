#!/usr/local/bin/php -f
<?php
require_once('guiconfig.inc');
require_once('ext/RSS/rss_functions.inc');

$pgtitle = array(gettext('Extensions'), gettext('RSS'), gettext('History'));
if (!is_array($config['rss'])) $config['rss'] = array();
if (!is_array($config['rss']['feeds'])) $config['rss']['feeds'] = array('rule'=>array());

array_sort_key($config['rss']['feeds']['rule'], "name");
$a_feeds = &$config['rss']['feeds']['rule'];

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    usort($a_feeds[$id]['history']['rule'], "usort_by_pubdate");
}

/*
if ($_GET['act'] === "del") {
    if ($a_feeds[$_GET['id']]) {
        unset($config['rss']['filters']['rule'][$_GET['id']]);
        write_config();
    }
}
*/

if ($_POST['act'] === "down") {
    if (isset($id) && isset($_POST['did'])) {
        $did = $_POST['did'];
        if(add_torrent($a_feeds[$id]['history']['rule'][$did]['link'], $a_feeds[$id]['directory']) == 0)
        {
            $data = $a_feeds[$id]['history']['rule'][$did];
            $data['downloaded'] = true;
            $a_feeds[$id]['history']['rule'][$did] = $data;
            $savemsg = "Successfully downloaded ";
            write_config();
        }
        else
            $savemsg = "Error downloading ";
        $savemsg .= "\"{$a_feeds[$id]['history']['rule'][$did]['title']}\"";
    }
}

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
        <form action="rss_history.php" method="post">
            Select Feed: 
            <select name='id' onchange="submit()">
                <?php if (!isset($id)): ?><option></option><?php endif; ?>
                <?php $i = 0; foreach ($a_feeds as $feed): ?>
                <option value='<?=$i;?>' <?php if (isset($id) && $id == $i):?>selected='selected'<?php endif; ?>><?=$feed['name'];?></option>
                <? $i++; endforeach; ?>
            </select>
        </form>
        </td>
    </tr>
<?php if (isset($id)): ?>
  <tr>
    <td class="tabcont">
            <form action="rss_history.php" method="post">
                <?php if ($savemsg) print_info_box($savemsg); ?>
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td width="75%" class="listhdrr"><?=gettext("Title"); ?></td>
                        <td width="20%" class="listhdrr"><?=gettext("Date"); ?></td>
                        <td width="5%" class="listhdrr"><?=gettext("Downloaded"); ?></td>
                        <!-- td width="10%" class="list"></td -->
                    </tr>
                    <?php
                        $i = 0; foreach ($a_feeds[$id]['history']['rule'] as $entry):
                    ?>
                    <tr>
                        <td class="listlr"><?=htmlspecialchars($entry['title']);?></td>
                        <td class="listrc"><?=htmlspecialchars($entry['pubdate']);?></td>
                        <td class="listrc">
                            <?php if ($entry['downloaded'] === true):?>
                            <img src="status_enabled.png" border="0">
                            <?php else:?>
                            <form action="rss_history.php" method="post">
                            <input type="hidden" name="act" value="down" />
                            <input type="hidden" name="id" value="<?=$id;?>" />
                            <input type="hidden" name="did" value="<?=$i; ?>" />
                            <input type="image" src="status_disabled.png" onclick="submit();">
                            </form>
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
<?php endif; ?>
</table>
<?php include("fend.inc");?>
