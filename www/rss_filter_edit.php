#!/usr/local/bin/php
<?php
require_once("auth.inc");
require_once("guiconfig.inc");

$id = $_GET['id'];
if (isset($_POST['id']))
    $id = $_POST['id'];

$pgtitle = array(gettext('Extensions'), gettext("RSS"),gettext("Filter"),isset($id)?gettext("Edit"):gettext("Add"));

if (!is_array($config['rss']['filters'])) $config['rss']['filters'] = array('rule'=>array());
array_sort_key($config['rss']['filters']['rule'], "name");

$a_feed = array_values($config['rss']['feeds']['rule']);
$a_filter = array_values($config['rss']['filters']['rule']);

if (isset($id) && $a_filter[$id]) {
    $pconfig['uuid'] = $a_filter[$id]['uuid'];
    $pconfig['name'] = $a_filter[$id]['name'];
    $pconfig['filter'] = $a_filter[$id]['filter'];
    $pconfig['directory'] = $a_filter[$id]['directory'];
    $pconfig['enabled'] = $a_filter[$id]['enabled'];
    $pconfig['feed'] = $a_filter[$id]['feed'];
    $pconfig['smart'] = $a_filter[$id]['smart'];
    $pconfig['episodes'] = $a_filter[$id]['episodes'];
} else {
    $pconfig['uuid'] = uuid();
    $pconfig['name'] = '';
    $pconfig['filter'] = '';
    $pconfig['directory'] = '';
    $pconfig['enabled'] = false;
    $pconfig['feed'] = -1;
    $pconfig['episodes'] = array();
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
        $filter['smart'] = $_POST['smart'] ? true : false;
        $filter['episodes'] = unserialize($_POST['episodes']);

        if (isset($id) && $a_filter[$id]) {
            $a_filter[$id] = $filter;
        } else {
            $a_filter[] = $filter;
        }

        $config['rss']['filters']['rule'] = $a_filter;
        write_config();

        header("Location: extension_rss_filter_manage.php");
        exit;
    }
}


?>
<?php include("fbegin.inc");?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td class="tabnavtbl">
            <ul id="tabnav">
                <li class="tabinact"><a href="extension_rss_feed_manage.php"><span><?=gettext("Feeds");?></span></a></li>
                <li class="tabact"><a href="extension_rss_filter_manage.php" title="<?=gettext("Reload page");?>"><span><?=gettext("Filters");?></span></a></li>
                <li class="tabinact"><a href="extension_rss_history.php"><span><?=gettext("History");?></span></a></li>
            </ul>
        </td>
    </tr>
    <tr>
        <td class="tabcont">
            <form action="extension_rss_filter_edit.php" method="post" name="iform" id="iform">
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
                        <td width="22%" valign="top" class="vncell"><?=gettext("Smart Filter");?></td>
                        <td width="78%" class="vtable">
                            <input name="smart" type="checkbox" id="smart" value="yes" <?php if (isset($pconfig['smart'])) echo "checked";?>>
                            <span class="vexpl"><?=gettext("Attempt to filter series episodes.");?></span>
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
                    <input name="episodes" type="hidden" value="<?=htmlspecialchars(serialize($pconfig['episodes']));?>">
                    <?php if (isset($id) && $a_filter[$id]): ?>
                    <input name="id" type="hidden" value="<?=$id;?>">
                    <?php endif; ?>
                </div>
                <?php include("formend.inc");?>
            </form>
        </td>
    </tr>
</table>
<?php include("fend.inc");?>
