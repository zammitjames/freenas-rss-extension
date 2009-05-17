#!/usr/local/bin/php -f
<?php
require('guiconfig.inc');
require_once('rss_class_history.php');

$pgtitle = array(gettext('Extensions'), gettext('RSS'), gettext('Feeds'));

if (!is_array($config['rss']['feeds'])) $config['rss']['feeds'] = array('rule'=>array());
array_sort_key($config['rss']['feeds']['rule'], "name");

$a_feeds = &$config['rss']['feeds']['rule'];

if ($_GET['act'] === "del") {
    if ($a_feeds[$_GET['id']]) {
        $History = new History($config['rss']);
        $History->read();
        $History->delete($_GET['id']);
        $History->write();
        
        unset($config['rss']['feeds']['rule'][$_GET['id']]);
        write_config();
    }
}

include("fbegin.inc");
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td class="tabnavtbl">
            <ul id="tabnav">
                <li class="tabact"><a href="extension_rss_feed_manage.php" title="<?=gettext("Reload page");?>"><span><?=gettext("Feeds");?></span></a></li>
                <li class="tabinact"><a href="extension_rss_filter_manage.php"><span><?=gettext("Filters");?></span></a></li>
                <li class="tabinact"><a href="extension_rss_history.php"><span><?=gettext("History");?></span></a></li>
            </ul>
        </td>
    </tr>
    <tr>
    <td class="tabcont">
            <form action="extension_rss_feed_manage.php" method="post">
                <?php if ($savemsg) print_info_box($savemsg); ?>
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td width="10%" class="listhdrr"><?=gettext("Name"); ?></td>
                        <td width="65%" class="listhdrr"><?=gettext("URL"); ?></td>
                        <td width="10%" class="listhdrr"><?=gettext("Last Checked"); ?></td>
                        <td width="5%" class="listhdrr"><?=gettext("Enabled"); ?></td>
                        <td width="10%" class="list"></td>
                    </tr>
                    <?php $i=0; foreach ($a_feeds as $feed):
                        $enable = isset($feed['enabled']);
                    ?>
                    <tr>
                        <td class="<?=$enable?"listlr":"listlrd";?>"><?=htmlspecialchars($feed['name']);?></td>
                        <td class="<?=$enable?"listrc":"listrcd";?>"><?=htmlspecialchars($feed['_url']);?></td>
                        <td class="<?=$enable?"listrc":"listrcd";?>"><?=htmlspecialchars($feed['updated']);?>&nbsp;</td>
                        <td class="<?=$enable?"listrc":"listrcd";?>">
                            <?php if ($enable):?>
                            <img src="status_enabled.png" border="0">
                            <?php else:?>
                            <img src="status_disabled.png" border="0">
                            <?php endif;?>
                        </td>
                        <td valign="middle" nowrap class="list">
                            <a href="extension_rss_feed_edit.php?id=<?=$i;?>"><img src="e.gif" title="<?=gettext("Edit feed");?>" border="0"></a>&nbsp;
                            <a href="extension_rss_feed_manage.php?act=del&id=<?=$i;?>" onclick="return confirm('<?=gettext("Do you really want to delete this feed?"); ?>')"><img src="x.gif" title="<?=gettext("Delete feed"); ?>" border="0"></a>
                        </td>
                    </tr>
                    <?php $i++; endforeach;?>
                    <tr>
                        <td class="list" colspan="6"></td>
                        <td class="list"> <a href="extension_rss_feed_edit.php"><img src="plus.gif" title="<?=gettext("Add feed"); ?>" border="0"></a></td>
                    </tr>
                </table>
            </form>
        </td>
    </tr>
</table>
<?php include("fend.inc");?>