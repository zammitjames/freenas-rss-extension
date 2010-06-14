#!/usr/local/bin/php -f
<?php
require_once('auth.inc');
require_once('guiconfig.inc');

$pgtitle = array(gettext('Extensions'), gettext('RSS'), gettext('About'));
include('fbegin.inc');
?>
<style>
div.license { margin-bottom: 1em; }
div.license p { margin: 0; }
</style>
<script src="/ext/RSS/js/jquery.min.js"></script>
<script>
$(function(){
    $(".license .listhdrlr").append("&nbsp;[<a href='#' class='lic-link' style='font-weight: normal'>license info</a>]");
    $(".lic-link").click(function(event){ event.preventDefault(); $(this).closest('table').find("td.listlr").toggle() });
});
</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td class="tabnavtbl">
        <ul id="tabnav">
                <li class="tabinact"><a href="extension_rss_feed_manage.php"><span><?=gettext('Feeds');?></span></a></li>
                <li class="tabinact"><a href="extension_rss_filter_manage.php"><span><?=gettext('Filters');?></span></a></li>
                <li class="tabinact"><a href="extension_rss_history.php" title="<?=gettext('Reload page');?>"><span><?=gettext("History");?></span></a></li>
                <li class="tabact"><a href="extension_rss_about.php"><span><?=gettext('About');?></span></a></li>
        </ul>
        </td>
    </tr>
    <tr>
        <td class="tabcont">
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
            <tr><td class="listtopic">FreeNAS RSS Extension</td></tr>
            <tr><td class="listlr"><a href="http://code.google.com/p/freenas-rss-extension">http://code.google.com/p/freenas-rss-extension</a><td></tr>
            <tr><td class="listlr">
                    <p>Copyright &copy; 2010, Brian Hartvigsen</p>
                    <p>All rights reserved.</p>

                    <p>Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:</p>

                    <p>Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.</p>
                    <ul>
                    <li>Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.</li>
                    <li>Neither the name of the FreeNAS RSS Extension nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.</li>
                    </ul>
                
                    <p>THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.</p>
            </td></tr>
            </table>
            <br />
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
            <tr><td class="listtopic">Contributions:</td></tr>
            <tr><td class="listlr">
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr><td width="15%"><a href="http://code.google.com/u/omnix32/">omnix32</a></td><td>Initial patch and testing for <a href="http://code.google.com/p/freenas-rss-extension/issues/detail?id=21">issue 21</a></td>
                    <tr><td><a href="http://code.google.com/u/dar.quonb/">dar.quonb</a></td><td>Patch and testing for issues <a href="http://code.google.com/p/freenas-rss-extension/issues/detail?id=22">22</a>, <a href="http://code.google.com/p/freenas-rss-extension/issues/detail?id=23">23</a> &amp; <a href="http://code.google.com/p/freenas-rss-extension/issues/detail?id=24">24</a></td>
                </table>
            </td></tr>
            </table>
            <br />
            <table width="100%" border="0" cellpadding="0" cellspacing="0" class="license">
                <tr><td class="listtopic">Credits:</td></tr>
                <tr><td class="listlr">
                    <div class="license">
                        <p><b>Silk Icon Pack</b> - <a href="http://www.famfamfam.com/lab/icons/silk/">http://www.famfamfam.com/lab/icons/silk/</a></p>
                        <p>Copyright &copy; 2006, Mark James</p>
                        <p>Used under the terms of the <a href="http://creativecommons.org/licenses/by/3.0/">Creative Commons Attribution 3.0 Unported License</a>.</p>
                    </div>
                    <div class="license">
                        <p><b>torrent-rw</b> - <a href="http://github.com/adriengibrat/torrent-rw">http://github.com/adriengibrat/torrent-rw</a></p>
                        <p>Copyright &copy; 2010, Adrien Gibrat</p>
                        <p>Used under the terms of the <a href="http://www.gnu.org/licenses/">GNU General Public License Version 3</a>.</p>
                    </div>
                    <div class="license">
                        <p><b>jQuery</b> - <a href="http://jquery.com/">http://jquery.com/</a></p>
                        <p>Copyright &copy; 2010 John Resig, http://jquery.com/</p>
                        <p>Used under the terms of the <a href="http://opensource.org/licenses/mit-license.php">MIT License</a>.</p>
                    </div>
                    <div class="license">
                        <p><b>boxy</b> - <a href="http://onehackoranother.com/projects/jquery/boxy/">http://onehackoranother.com/projects/jquery/boxy/</a></p>
                        <p>Copyright &copy; 2008 Jason Frame (jason@onehackoranother.com)</p>
                        <p>Used under the terms of the <a href="http://opensource.org/licenses/mit-license.php">MIT License</a>.</p>
                    </div>
                    <div class="license">
                        <p><b>jQuery Form Plugin</b> - <a href="http://jquery.malsup.com/form/">http://jquery.malsup.com/form/</a></p>
                        <p>Copyright &copy; 2010, Mike Alsup</p>
                        <p>Used under the terms of the <a href="http://opensource.org/licenses/mit-license.php">MIT License</a>.</p>
                    </div>
                </td></tr>
            </table>
        </td>
    </tr>
</table>
<?php
include('fend.inc');