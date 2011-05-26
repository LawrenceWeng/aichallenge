<?php

include 'header.php'; 
require_once('mysql_login.php');

$query = "select count(*) from user where activated=1 and created > (now() - interval 24 hour)";
$result = mysql_query($query);
$row = mysql_fetch_row($result);
$new_users = $row[0];

$query = "select count(*) from submission where timestamp > (now() - interval 24 hour)";
$result = mysql_query($query);
$row = mysql_fetch_row($result);
$submissions = $row[0];

$query = "select count(*) from submission where timestamp > (now() - interval 24 hour) and status = 40";
$result = mysql_query($query);
$row = mysql_fetch_row($result);
$submissions_successful = $row[0];
if ($submissions) {
    $submissions_percentage = ($submissions_successful / $submissions) * 100.0;
} else {
    $submissions_percentage = 0;
}

$query = "select count(*) from game where timestamp > (now() - interval 24 hour)";
$result = mysql_query($query);
$row = mysql_fetch_row($result);
$games_played = $row[0];

$games_per_minute = array();
foreach(array(5,60,1444) as $minutes){
  $sql = "select count(*)/$minutes from game where timestamp > timestampadd(minute, -$minutes, current_timestamp);";
  $r = mysql_fetch_row(mysql_query($sql));
  $games_per_minute[$minutes] = $r[0];
}

$PAIRCUT_FILE = "/home/contest/pairing_cutoff";
if (is_readable($PAIRCUT_FILE)) {
  $pfc = file($PAIRCUT_FILE);
  $pair_cutoff = $pfc[0];
} else {
  $pair_cutoff = "None";
}

?>

<h1>Server Statistics</h1>

<h2>GIT information</h2>
<p><strong>Source: </strong><code><?=exec("git remote --v|grep origin|grep fetch")?></code></p>
<p><strong>Branch/Version Information: </strong><code><?=substr(exec("git branch -vv|grep -e ^\\*"),2);?></code></p>

<h2>Last 24 hours</h2>

<table class="bigstats">
  <tr>
    <td><?php echo $new_users?></td>
    <td><?php echo $submissions?></td>
    <td><?php echo $submissions_successful?>
      <span style="font-size: smaller">
        (<?php echo number_format($submissions_percentage,0)?>%)
      </span>
    </td>
    <td><?php echo $games_played?></td>
    <td><?php echo $pair_cutoff?></td>
  </tr>
  <tr>
    <th>New users</th>
    <th>New submissions</th>
    <th>Successful submissions</th>
    <th>Games played</th>
    <th>Pairing cutoff</th>
  </tr>
</table>

<h2 style="margin-top: 1em">Games per minute</h2>

<table class="bigstats">
  <tr>
    <td><?php echo number_format($games_per_minute[5],1)?></td>
    <td><?php echo number_format($games_per_minute[60],1)?></td>
    <td><?php echo number_format($games_per_minute[1444],1)?></td>
  </tr>
  <tr>
    <th>Last 5 minutes</th>
    <th>Last hour</th>
    <th>Last 24 hours</th>
  </tr>
</table>

<?php include 'footer.php'; ?>
