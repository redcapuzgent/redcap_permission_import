<?php
$filename = $_FILES["importcsv"]["tmp_name"];
$sendEmail = @$_POST["sendemail"] == 'on';
$checkUsernames = @$_POST["checkusers"] == 'on';
$pid = $_POST["pid"];
global $module;

/**
 * @var $module \uzgent\PermissionImport\PermissionImport
 */
$Proj = new Project($pid);
$rows = [];
if (($handle = fopen($filename, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $rows[] = $data;
    }
    fclose($handle);
}

array_shift($rows); //slice off header.
$roles = UserRights::getRoles();
$groups = $Proj->getGroups();
$HtmlPage = new HtmlPage();
$HtmlPage->PrintHeaderExt();
?><H1>
    Import user permissions
</H1>
<table class="table">
    <thead>
    <th>username</th>
    <th>Remark</th>
    </thead>
    <tbody>
    <?php

    foreach ($rows as $row) {
        $username = $row[0];
        $selectedRole = $row[1];
        $selectedGroup = $row[2];
        echo "<tr><td>";
        echo $username . "</td>";
        if ($checkUsernames && User::getUIIDByUsername($username) === null) {
            echo "<td class='alert alert-warning'>User with username $username wasn't found. </td></tr>";
            continue;
        }
        list($role_id, $key) = $module->getRoleByName($roles, $selectedRole);
        if ($role_id === null) {
            echo "<td class='alert alert-warning'>Couldn't find the role for $username: $selectedRole. Skipping.</td></tr>";
            continue;
        }
        $group_id = $module->getDAG($groups, $selectedGroup);
        if ($selectedGroup !== null && strlen($selectedGroup) > 0 && $group_id == null) {
            echo "<td class='alert alert-warning'>A group was filled in for $username but $selectedGroup couldn't be found. Skipping.</td></tr>";
            continue;
        }
        try {
            if ($group_id !== null) {
                $module->setUserRoleWithDag($roles, $pid, $username, $role_id, $group_id);
                echo "<td class='alert alert-success'>User rights added with DAG info.</td>";
            } else {
                $module->setUserRole($roles, $pid, $username, $role_id);
                echo "<td class='alert alert-success'>User rights added without DAG info.</td>";
            }
            if ($sendEmail) {
                //$module->sendEmail($username);
            }
        } catch (Exception $e) {
            echo "<td class='alert alert-warning'>Something went very wrong " . $e->getMessage() . "</td>";
        }
    }

    ?>
    </tbody>
</table>
