<?php
global $module;
/**
 * @var $module \uzgent\PermissionImport\PermissionImport
 */

?>
<H1>
    Import user permissions
</H1>
<p>
To import user permissions download the following file.
<BR/>
<a href="<?php echo $module->getPermissionCsvURL(); ?>">Import template example</a>
</p>



<BR/>
<div class="card">
    <div class="card-header">
        Upload user permissions
    </div>
    <div class="card-body">
<form enctype="multipart/form-data" action="<?php echo $module->getProcessCsvURL(); ?>importFile.php" method="post">
    <!-- input type="checkbox" checked id="sendemail" name="sendemail"><label for="sendemail">&nbsp;Send an email</label><br -->
    <input type="checkbox" checked id="checkusers" name="checkusers"><label for="checkusers">&nbsp;Check that the usernames exist.</label><br/>
    <input name="pid" type="hidden" value="<?php echo $_GET['pid'];?>"/><BR/><BR/>
    <input name="importcsv" type="file"/><BR/><BR/>
    <input class="btn btn-primary" type="submit">
</form>
</div>
<BR/>


<?php
