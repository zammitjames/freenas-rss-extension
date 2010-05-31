#!/usr/local/bin/php -f
<?php
require_once('auth.inc');
require_once('guiconfig.inc');

$pgtitle = array(gettext('Extensions'), gettext('RSS'), gettext('About'));
include('fbegin.inc');
?>

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
            <tr><td class="listhdrlr">FreeNAS RSS Extension</td></tr>
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
            <tr><td class="listhdrlr">Credits:</td></tr>
            <tr><td class="listlr">
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr><td width="15%"><a href="http://code.google.com/u/omnix32/">omnix32</a></td><td>Initial patch and testing for <a href="http://code.google.com/p/freenas-rss-extension/issues/detail?id=21">issue 21</a></td>
                    <tr><td><a href="http://code.google.com/u/dar.quonb/">dar.quonb</a></td><td>Patch and testing for issues <a href="http://code.google.com/p/freenas-rss-extension/issues/detail?id=22">22</a>, <a href="http://code.google.com/p/freenas-rss-extension/issues/detail?id=23">23</a> &amp; <a href="http://code.google.com/p/freenas-rss-extension/issues/detail?id=24">24</a></td>
                </table>
            </td></tr>
            </table>
            <br />
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr><td class="listhdrlr">torrent-rw</td></tr>
                <tr><td class="listlr"><a href="http://github.com/adriengibrat/torrent-rw">http://github.com/adriengibrat/torrent-rw</a></td></tr>
                <tr><td class="listlr">
                    <p>Torrent.php - Create and parse Torrent files</p>
                    <p>Copyright &copy; 2010, Adrien Gibrat</p>

                    <p>This program is free software: you can redistribute it and/or modify
                    it under the terms of the GNU General Public License as published by
                    the Free Software Foundation, either version 3 of the License, or
                    (at your option) any later version.</p>

                    <p>This program is distributed in the hope that it will be useful,
                    but WITHOUT ANY WARRANTY; without even the implied warranty of
                    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
                    GNU General Public License for more details.</p>

                    <p>You should have received a copy of the GNU General Public License
                    along with this program.  If not, see <a href="http://www.gnu.org/licenses/">http://www.gnu.org/licenses/</a>.</p>
                </td></tr>
            </table>
            <br />
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr><td class="listhdrlr">Silk Icon Pack</td></tr>
                <tr><td class="listlr"><a href="http://www.famfamfam.com/lab/icons/silk/">http://www.famfamfam.com/lab/icons/silk/</a></td></tr>
                <tr><td class="listlr">
                    <p>Copyright &copy; 2006, Mark James</p>
                    <p>This work is licensed under the Creative Commons Attribution 3.0 Unported License. To view a copy of this license, visit <a href="http://creativecommons.org/licenses/by/3.0/">http://creativecommons.org/licenses/by/3.0/</a> or send a letter to Creative Commons, 171 Second Street, Suite 300, San Francisco, California, 94105, USA.</p>
                </td></tr>
            </table>
        </td>
    </tr>
</table>
<?php
include('fend.inc');