#!/usr/local/bin/php -f
<?php
require_once("auth.inc");
require_once('guiconfig.inc');
require_once('ext/RSS/rss_functions.inc');

$pgtitle = array(gettext('Extensions'), gettext('RSS'), gettext('Filters'));

if (!is_array($config['rss']['filters'])) $config['rss']['filters'] = array('rule'=>array());
array_sort_key($config['rss']['filters']['rule'], "name");

$a_feeds = array_values($config['rss']['feeds']['rule']);
$a_filters = array_values($config['rss']['filters']['rule']);

if ($_GET['act'] === "del") {
    if ($a_filters[$_GET['id']]) {
        unset($a_filters[$_GET['id']]);
        $config['rss']['filters']['rule'] = $a_filters;
        write_config();
    }
}

include("fbegin.inc");
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td class="tabnavtbl">
            <ul id="tabnav">
                <li class="tabinact"><a href="extension_rss_feed_manage.php"><span><?=gettext("Feeds");?></span></a></li>
                <li class="tabact"><a href="extension_rss_filter_manage.php" title="<?=gettext("Reload page");?>"><span><?=gettext("Filters");?></span></a></li>
                <li class="tabinact"><a href="extension_rss_history.php"><span><?=gettext("History");?></span></a></li>
                <li class="tabinact"><a href="extension_rss_about.php"><span><?=gettext('About');?></span></a></li>
            </ul>
        </td>
    </tr>
    <tr>
        <td class="tabcont">
            <form action="extension_rss_feed_manage.php" method="post">
                <?php if ($savemsg) print_info_box($savemsg); ?>
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td width="10%" class="listhdrlr"><?=gettext("Name"); ?></td>
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
                            if($filter['feed'] != -1) echo htmlspecialchars(get_by_uuid($a_feeds, $filter['feed'], "name"));
                        ?>&nbsp;</td>
                        <td class="<?=$enable?"listrc":"listrcd";?>">
                            <?php if ($enable):?>
                            <img src="status_enabled.png" border="0">
                            <?php else:?>
                            <img src="status_disabled.png" border="0">
                            <?php endif;?>
                        </td>
                        <td valign="middle" nowrap class="list">
                            <a href="extension_rss_filter_edit.php?id=<?=$i;?>"><img src="e.gif" title="<?=gettext("Edit filter");?>" border="0"></a>&nbsp;
                            <a href="extension_rss_filter_manage.php?act=del&id=<?=$i;?>" onclick="return confirm('<?=gettext("Do you really want to delete this filter?"); ?>')"><img src="x.gif" title="<?=gettext("Delete filter"); ?>" border="0"></a>
                        </td>
                    </tr>
                    <?php $i++; endforeach;?>
                    <tr>
                        <td class="list" colspan="4"></td>
                        <td class="list"> <a href="extension_rss_filter_edit.php"><img src="plus.gif" title="<?=gettext("Add feed"); ?>" border="0"></a></td>
                    </tr>
                </table>
                <?php include("formend.inc");?>
            </form>
        </td>
    </tr>
</table>
<?php include("fend.inc");?>