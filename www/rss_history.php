#!/usr/local/bin/php -f
<?php
require_once('guiconfig.inc');
require_once('ext/RSS/rss_functions.inc');

$pgtitle = array(gettext('Extensions'), gettext('RSS'), gettext('History'));
if (!is_array($config['rss'])) $config['rss'] = array();
if (!is_array($config['rss']['feeds'])) $config['rss']['feeds'] = array('rule'=>array());
if (!is_array($config['rss']['filters'])) $config['rss']['filters'] = array('rule'=>array());

array_sort_key($config['rss']['feeds']['rule'], 'name');
array_sort_key($config['rss']['filters']['rule'], 'name');

$a_feeds = &$config['rss']['feeds']['rule'];
$a_filters = &$config['rss']['filters']['rule'];

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    if (!is_array($a_feeds[$id]['history']) || !is_array($a_feeds[$id]['history']['rule']))
        $a_feeds[$id]['history'] = array('rule' => array());
    
    usort($a_feeds[$id]['history']['rule'], "usort_by_pubdate");
}

if ($_POST['act'] === "down") {
    if (isset($id) && isset($_POST['did'])) {
        $item = &$a_feeds[$id]['history']['rule'][$_POST['did']];
        
        $directory = '';
        
        if (isset($item['filter'])) {
            $directory = get_by_uuid($a_filters, $item['filter'], 'directory');
        }
        
        if(empty($directory))
            $directory = $a_feeds[$id]['directory'];
        
        if(add_torrent($item['link'], $directory) == 0) {
            $item['downloaded'] = true;
            $savemsg = "Successfully downloaded ";
            write_config();
        }
        else
            $savemsg = "Error downloading ";
        $savemsg .= "\"{$item['title']}\"";
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
        </form>
        </td>
    </tr>
<?php if (isset($id)): ?>
  <tr>
    <td class="tabcont">
            <form action="extension_rss_history.php" method="post">
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
                        <td class="listlr">
                            <?php if (isset($entry['description']) && !empty($entry['description'])): ?>
                            <img src="/ext/RSS/bullet_toggle_plus.png" alt="[more]" style='vertical-align: bottom; cursor: pointer' onclick="showdesc('desc<?=$i?>', this);" />
                            <?php endif; ?>
                            <?=htmlspecialchars($entry['title']);?>
                            <?php if (isset($entry['filter'])): ?> <img src="/ext/RSS/lightning.png" alt="filtered" title="Matched filter: <?=get_by_uuid($a_filters, $entry['filter'], 'name'); ?>" /><?php endif; ?></td>
                        <td class="listrc"><?=htmlspecialchars($entry['pubdate']);?></td>
                        <td class="listrc">
                            <?php if (isset($entry['downloaded'])):?>
                            <img src="status_enabled.png" border="0">
                            <?php else:?>
                            <form action="extension_rss_history.php" method="post">
                            <input type="hidden" name="act" value="down" />
                            <input type="hidden" name="id" value="<?=$id;?>" />
                            <input type="hidden" name="did" value="<?=$i; ?>" />
                            <input type="image" src="status_disabled.png" onclick="submit();">
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
            </form>
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
