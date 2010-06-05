#!/usr/local/bin/php -f
<?php
require_once("auth.inc");
require_once('guiconfig.inc');
require_once('ext/RSS/rss_functions.inc');
require_once('ext/RSS/history.class.php');

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

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $list = $History->full($a_feeds[$id]['uuid']);
    if (is_array($list))
        usort($list, 'usort_by_pubdate'); // Do this in rss_cron.php
}

include("fbegin.inc");
?>
<script src="/ext/RSS/js/jquery.min.js"></script>
<script src="/ext/RSS/js/jquery.boxy.js"></script>
<link rel="stylesheet" href="/ext/RSS/css/boxy.css" type="text/css" />
<script>
$(function() {
    $('.boxy-image').boxy({draggable: false, modal: true, closeable: false});
});
</script>
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
<?php if (isset($id)): ?>
        <br />
    <?php if (is_array($list)): ?>
            <?php if (isset($savemsg)) print_info_box($savemsg); ?>
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="75%" class="listhdrlr"><?=gettext("Title"); ?></td>
                    <td width="20%" class="listhdrr"><?=gettext("Date"); ?></td>
                    <td width="5%" class="listhdrr"><?=gettext("Status"); ?></td>
                    <!-- td width="10%" class="list"></td -->
                </tr>
                <?php $i = 0; foreach ($list as $entry): ?>
                <tr>
                    <td class="listlr">
                        <?php if (isset($entry['description']) && !empty($entry['description'])): ?>
                            <img src="/ext/RSS/img/bullet_toggle_plus.png" alt="[more]" style='vertical-align: bottom; cursor: pointer' onclick="showdesc('desc<?=$i?>', this);" />
                        <?php else: ?>
                            <img src="/ext/RSS/img/blank.gif" style="width: 16px; height: 16px; vertical-align: bottom" />
                        <?php endif; ?>
                        <?=htmlspecialchars($entry['title']);?>
                    </td>
                    <td class="listrc"><?=htmlspecialchars(date(DATE_RSS, strtotime($entry['pubDate'])));?></td>
                    <td class="listr" style="text-align:right">
                        <?php if ($entry['filter'] && get_by_uuid($a_filters, $entry['filter']) != null): ?><img src="/ext/RSS/img/wand.png" alt="filtered" title="Matched filter: <?=get_by_uuid($a_filters, $entry['filter'], 'name'); ?>" /> <?php endif; ?>
                        <div id="download_<?=$i; ?>" style="display:none">
                            <form action="extension_rss_history.php" class="boxy-form" method="post">
                                <div style="font-weight: bold">Choose download directory:</div>
                                <input type="hidden" name="act" value="down" />
                                <input type="hidden" name="id" value="<?=$id;?>" />
                                <input type="hidden" name="did" value="<?=$entry['guid']; ?>" />
                                <input name="directory" type="text" class="formfld" id="directory" size="60" value="<?=get_by_uuid($a_filters, $entry['directory'], 'name'); ?>">
                                <input name="directorybrowsebtn" type="button" class="formbtn" id="directorybrowsebtn" onclick="ifield = form.directory; filechooser = window.open(&quot;filechooser.php?p=&quot;+escape(ifield.value)+&quot;&amp;sd=/mnt&quot;, &quot;filechooser&quot;, &quot;scrollbars=yes,toolbar=no,menubar=no,statusbar=no,width=550,height=300&quot;); filechooser.ifield = ifield; window.ifield = ifield;" value="...">
                                <div>Leave this blank to download to your default directory</div>
                                <?php include("formend.inc");?>
                                <br />
                                <input type="submit" value="Download" onclick="$(this).parent().hide().next('div').show(); return true;" />
                                <input type="submit" value="Close" onclick="return false;" class="close" />
                            </form>
                            <div style="display:none">
                                <img src="/ext/RSS/img/load.gif" /> Sending command to transmission
                            </div>
                        </div>
                        <a class="boxy-image" href="#download_<?=$i; ?>" title="Downloading <?=htmlspecialchars($entry['title']); ?>"><img src="<?=($entry['downloaded']?'status_enabled':'/ext/RSS/img/add')?>.png" border="0" /></a>
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
<?php
    else:
        print_error_box(gettext("There is no history, yet!"));
    endif;
endif;
?>
        </td>
    </tr>
</table>
<script type="text/javascript">
function showdesc(id, elem) {
    var el = document.getElementById(id);
    if (el.style.display != 'none') {
        elem.src = '/ext/RSS/img/bullet_toggle_plus.png';
        elem.alt = ' [more] ';
        el.style.display = 'none';
    }
    else {
        elem.src = '/ext/RSS/img/bullet_toggle_minus.png';
        elem.alt = ' [less] ';
        el.style.display = '';
    }
}
</script>
<?php include("fend.inc");?>
