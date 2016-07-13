<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

@session_start();

//Module includes
include "./modules/" . $_SESSION[$guid]["module"] . "/moduleFunctions.php";

if (!isActionAccessible($guid, $connection2, '/modules/Trip Planner/trips_manageSettings.php')) {
    //Acess denied
    print "<div class='error'>";
        print "You do not have access to this action.";
    print "</div>";
} else {
    print "<div class='trail'>";
        print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>" . _("Home") . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . _(getModuleName($_GET["q"])) . "</a> > </div><div class='trailEnd'>" . _('Manage Settings') . "</div>";
    print "</div>";

    print "<h3>";
        print "Settings";
    print "</h3>";

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    ?>

    <form method="post" action="<?php print $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/trips_manageSettingsProcess.php" ?>">
        <table class='smallIntBorder' cellspacing='0' style="width: 100%">  
            <tr>
                <?php
                try {
                    $sql = "SELECT * FROM gibbonSetting WHERE scope='Trip Planner' AND name='requestApprovalType'";
                    $result = $connection2->prepare($sql);
                    $result->execute();
                } catch (PDOException $e) { 
                    print "<div class='error'>" . $e->getMessage() . "</div>"; 
                }
                
                $row = $result->fetch();
                ?>
                <td> 
                    <b><?php print _($row["nameDisplay"]) ?> *</b><br/>
                    <span style="font-size: 90%"><i>
                        <?php
                        if ($row["description"] != "") {
                            print _($row["description"]);
                        }
                        ?>
                    </i></span>
                </td>
                <td class="right">
                    <select name="<?php print $row["name"] ?>" id="<?php print $row["name"] ?>" style="width: 302px">
                        <?php
                        $selected = "";
                        if ($row["value"] == "One Of") {
                            $selected = "selected";
                        }
                        print "<option $selected value='One Of'>One Of</option>";
                        $selected = "";
                        if ($row["value"] == "Two Of") {
                            $selected = "selected";
                        }
                        print "<option $selected value='Two Of'>Two Of</option>";
                        $selected = "";
                        if ($row["value"] == "Chain Of All") {
                            $selected = "selected";
                        }
                        print "<option $selected value='Chain Of All'>Chain Of All</option>";
                        ?>          
                    </select>
                </td>
            </tr>
            <tr>
                <?php
                    try {
                        $sql = "SELECT * FROM gibbonSetting WHERE scope='Trip Planner' AND name='riskAssessmentTemplate'";
                        $result = $connection2->prepare($sql);
                        $result->execute();
                    } catch (PDOException $e) { 
                        print "<div class='error'>" . $e->getMessage() . "</div>"; 
                    }

                    $row = $result->fetch();
                ?>
                <td colspan=2>
                    <b><?php print _($row["nameDisplay"]) ?> *</b><br/>
                    <span style="font-size: 90%"><i>
                        <?php
                        if ($row["description"] != "") {
                            print _($row["description"]);
                        } 
                        ?>
                    </i></span>
                    <?php print getEditor($guid, TRUE, $row["name"], $row["value"], 5, true, false, false); ?>  
                </td>
            </tr>
            <tr>
                <td>
                    <span style="font-size: 90%"><i>* <?php print _("denotes a required field"); ?></i></span>
                </td>
                <td class="right">
                    <input type="hidden" name="address" value="<?php print $_SESSION[$guid]["address"] ?>">
                    <input type="submit" value="<?php print _("Submit"); ?>">
                </td>
            </tr>
        </table>
    </form>
    <?php
}   
?>
