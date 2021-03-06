<?php
// ini_set('error_reporting', E_ALL);
// ini_set('display_errors', true);

require_once('api_functions.php');

header("Content-type: application/json");

$json_string = file_get_contents('php://input');
$json_hash = md5($json_string);
$compiledata = json_decode($json_string);

$lang_result = contest_query("select_submission_language_id",
                             $compiledata->language);
if ($lang_result and mysqli_num_rows($lang_result) > 0) {
    $row = mysqli_fetch_assoc($lang_result);
    $lang_id = $row["language_id"];
} else {
    api_log("Creating new language: " . $compiledata->language);
    contest_query("insert_new_language", $compiledata->language);
    $lang_id = mysqli_insert_id($db_link);
    if ($memcache) {
        $memcache->delete("lookup:language_id");
        $memcache->delete("lookup:language_name");
    }
}
api_log("Language ID: " . strval($lang_id));

if ($compiledata->status_id == 40) {
    if (contest_query("update_submission_success",
                      $lang_id,
                      $worker["worker_id"],
                      $compiledata->submission_id)) {
        echo json_encode(array( "hash" => $json_hash ));
        api_log('worker '.$worker['worker_id'].' ('.$worker['ip_address'].') posted compile '.$compiledata->submission_id);
        if (!contest_query("update_submission_latest",
                          $compiledata->submission_id,
                          $compiledata->submission_id)) {
            api_log(sprintf("Error updating latest flag: %s", mysqli_error($db_link)));
        }
        if (!contest_query("update_user_shutdown_date", $compiledata->submission_id)) {
            api_log(sprintf("Error updating shutdown date: %s", mysqli_error($db_link)));
        }
    } else {
        api_log(sprintf("Error updating successful compile: %s", mysqli_error($db_link)));
    }
} else {
    if (contest_query("update_submission_failure",
                      $compiledata->status_id,
                      $lang_id,
                      $compiledata->errors,
                      $compiledata->submission_id)) {
        echo json_encode(array( "hash" => $json_hash ));
        api_log('worker '.$worker['worker_id'].' ('.$worker['ip_address'].') posted failed compile '.$compiledata->submission_id.":\n".$compiledata->errors);
    } else {
        api_log(sprintf("Error updating errored compile: %s", mysqli_error($db_link)));
    }
}

?>
