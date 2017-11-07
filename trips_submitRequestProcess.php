<?php

@session_start();

//Module includes
include "../../functions.php";
include "../../config.php";

include "./moduleFunctions.php";

date_default_timezone_set($_SESSION[$guid]["timezone"]);

$URL = $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/Trip Planner/";

$pdo = new Gibbon\sqlConnection();
$connection2 = $pdo->getConnection();

if (!isActionAccessible($guid, $connection2, '/modules/Trip Planner/trips_submitRequest.php')) {
    //Acess denied
    $URL .= "trips_manage.php&return=error0";
    header("Location: {$URL}");
    exit();
} else {    
    print_r($_POST["days"]);
    $URL .= "trips_submitRequest.php";
    $date = new DateTime();
    $riskAssessmentApproval = getSettingByScope($connection2, "Trip Planner", "riskAssessmentApproval");
    $items = array("title" => true, "description" => true, "location" => true, "days" => true, "riskAssessment" => !$riskAssessmentApproval, "letterToParents" => false, "teachersSelected" => true, "studentsSelected" => false, "order" => false);
    $data = array("creatorPersonID" => $_SESSION[$guid]["gibbonPersonID"], "timestampCreation" => $date->format('Y-m-d H:i:s'), "gibbonSchoolYearID" => $_SESSION[$guid]["gibbonSchoolYearID"]);
    $sql = "INSERT INTO tripPlannerRequests SET creatorPersonID=:creatorPersonID, timestampCreation=:timestampCreation, gibbonSchoolYearID=:gibbonSchoolYearID, ";
    $people = array();
    $days = array();
    $costs = array();

    foreach ($items as $item => $required) {
        if (isset($_POST[$item])) {
            if ($_POST[$item] != null && $_POST[$item] != "") {
                $key = $item;
                if ($item == "days") {
                    $key = null;
                    foreach ($_POST[$item] as $day) {
                        $date["startDate"] = DateTime::createFromFormat("d/m/Y", $date["startDate"])->format("Y-m-d");
                        $date["endDate"] = DateTime::createFromFormat("d/m/Y", $date["endDate"])->format("Y-m-d");
                        $days[] = $day;
                    }
                } elseif ($item == "teachersSelected" || $item == "studentsSelected") {
                    $key = null;
                    $role = "Teacher";
                    if ($item == "studentsSelected") {
                        $role = "Student";
                    } 

                    foreach ($_POST[$item] as $person) {
                        $people[] = array("role" => $role, "gibbonPersonID" => $person);
                    }
                } elseif ($item == "order") {
                    $key = null;
                    $order = $_POST['order'];
                    foreach ($order as $cost) {
                        $costs[$cost]['name'] = $_POST['name'.$cost];
                        $costs[$cost]['cost'] = $_POST['cost'.$cost];
                        $costs[$cost]['description'] = $_POST['description'.$cost];

                        if ($costs[$cost]['name'] == '' || $costs[$cost]['cost'] == '' || is_numeric($costs[$cost]['cost']) == false) {
                            $URL .= "&return=error1";
                            header("Location: {$URL}");
                            exit();
                        }
                    }
                } else {
                    $data[$item] = $_POST[$item];
                }

                if ($key != null) {
                    $sql .= $key . "=:" . $key . ", ";
                }
            }
        } elseif ($required) {
            $URL .= "&return=error1";
            header("Location: {$URL}");
            exit();
        }
    }

    $sql = substr($sql, 0, -2);

    try {
        $result = $connection2->prepare($sql);
        $result->execute($data);
        $tripPlannerRequestID = $connection2->lastInsertId();
        logEvent($connection2, $tripPlannerRequestID, $_SESSION[$guid]["gibbonPersonID"], "Request");
        $sql1 = "INSERT INTO tripPlannerCostBreakdown SET tripPlannerRequestID=:tripPlannerRequestID, title=:name, cost=:cost, description=:description";
        foreach ($costs as $cost) {
            $cost['tripPlannerRequestID'] = $tripPlannerRequestID;
            $result1 = $connection2->prepare($sql1);
            $result1->execute($cost);
        }
        $sql2 = "INSERT INTO tripPlannerRequestPerson SET tripPlannerRequestID=:tripPlannerRequestID, gibbonPersonID=:gibbonPersonID, role=:role";
        foreach ($people as $person) {
            $person['tripPlannerRequestID'] = $tripPlannerRequestID;
            $result2 = $connection2->prepare($sql2);
            $result2->execute($person);
        }
        $sql3 = "INSERT INTO tripPlannerRequestDays SET tripPlannerRequestID=:tripPlannerRequestID, startDate=:startDate, endDate=:endDate; allDay=:allDay, startTime=:startTime, endTime=:endTime";
        foreach ($days as $day) {
            $day['tripPlannerRequestID'] = $tripPlannerRequestID;
            $result3 = $connection2->perpare($sql3);
            $result3->execute($day);
        }
        notifyApprovers($guid, $connection2, $tripPlannerRequestID, $_SESSION[$guid]["gibbonPersonID"], $data["title"]);
    } catch (PDOException $e) {
        $URL .= "&return=error2";
        header("Location: {$URL}");
        exit();
    }

    $URL .= "&return=success0&tripPlannerRequestID=" . $tripPlannerRequestID;
    header("Location: {$URL}");
    exit();
}   
?>
