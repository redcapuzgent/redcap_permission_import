<?php

namespace uzgent\PermissionImport;

// Declare your module class, which must extend AbstractExternalModule
class PermissionImport extends \ExternalModules\AbstractExternalModule {

    public function getPermissionCsvURL() {
        return $this->getUrl("importtemplate.csv", true, true);
    }

    public function getProcessCsvURL() {
        return $this->getUrl("importFile.php", false, false);
    }

    /**
     * @param $roles
     * @param $project_id
     * @param $username
     * @param $role_id
     */
    public function setUserRole($roles, $project_id, $username, $role_id)
    {
        $sql = "insert into redcap_user_rights (project_id, username, role_id) values ($project_id, '".db_escape($username)."', ".checkNull($role_id).")
			on duplicate key update role_id = $role_id";
        if (db_query($sql)) {
            // Get role name of this role
            $role_name = $roles[$role_id]['role_name'];
            // Logging (if user was created)
            if (db_affected_rows() === 1) {
                Logging::logEvent($sql, "redcap_user_rights", "insert", $username, "user = '$username'", "Add user");
            }
        }
    }

    /**
     * @param $roles
     * @param $project_id
     * @param $username
     * @param $role_id
     */
    public function setUserRoleWithDag($roles, $project_id, $username, $role_id, $group_id)
    {
        $sql = "insert into redcap_user_rights (project_id, username, role_id, group_id) values ($project_id, '".db_escape($username)."', ".checkNull($role_id) .", ".checkNull($group_id).")
			on duplicate key update role_id = $role_id, group_id = $group_id";
        if (db_query($sql)) {
            // Get role name of this role
            $role_name = $roles[$role_id]['role_name'];
            // Logging (if user was created)
            if (db_affected_rows() === 1) {
                Logging::logEvent($sql, "redcap_user_rights", "insert", $username, "user = '$username'", "Add user");
            }
        }
    }

    public function getRoleByName(array $roles, string $selectedRole): array
    {
        $role_id = null;
        foreach ($roles as $key => $role) {
            if ($role["role_name"] == $selectedRole) {
                $role_id = $key;
            }
        }
        return array($role_id, $key);
    }

    public function getDAG($groups, string $selectedGroup)
    {
        $group_id = null;
        foreach ($groups as $key => $group) {
            if ($group == $selectedGroup) {
                $group_id = $key;
            }
        }
        return $group_id;
    }

    public function sendEmail($username)
    {
        global $lang;
        $sql = "select user_firstname, user_lastname, user_email from redcap_user_information
						where username = '".db_escape($username)."' and user_email is not null";
        $q = db_query($sql);
        if (db_num_rows($q)) {
            $row = db_fetch_array($q);
            $user_email = $row["user_email"];
            $email = new Message ();
            $emailContents = "
						<html><body style='font-family:arial,helvetica;font-size:10pt;'>
						{$lang['global_21']}<br /><br />
						{$lang['rights_88']} \"<a href=\"".APP_PATH_WEBROOT_FULL."redcap_v".REDCAP_VERSION."/index.php?pid=".PROJECT_ID."\">".strip_tags(str_replace("<br>", " ", label_decode($app_title)))."</a>\"{$lang['period']}
						{$lang['rights_89']} \"$username\", {$lang['rights_90']}<br /><br />
						".APP_PATH_WEBROOT_FULL."
						</body>
						</html>";
            $email->setTo($row['user_email']);
            $email->setFrom($user_email);
            $email->setFromName($GLOBALS['user_firstname']." ".$GLOBALS['user_lastname']);
            $email->setSubject($lang['rights_122']);
            $email->setBody($emailContents);
            $email->send();
        }
    }


}
