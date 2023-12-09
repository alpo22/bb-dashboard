<?php
require_once __DIR__ . '/secrets.php';

$liveDbLink = mysqli_connect(SECRET_DB_SERVER, SECRET_DB_USER, SECRET_DB_PASS, SECRET_DB_NAME) or die('Database error: ' . mysqli_error($liveDbLink));

function getAllUsers()
{
  global $liveDbLink;

  $sql = "SELECT u.*
          FROM users u
          WHERE u.id NOT IN (1,2)
          AND u.isActive = 1
          ORDER BY u.id ASC";

  return $liveDbLink->query($sql);
}

function getAllOrders($userId)
{
  global $liveDbLink;

  $today         = date('Y-m-d');
  $thirtyDaysAgo = date('Y-m-d', strtotime($today . ' -30 days'));

  $sql = "SELECT u.id, u.domain, count(*) as 'count', cast(o.created as DATE) as 'date'
          FROM users u
          LEFT JOIN orders o ON o.userId = u.id
          WHERE o.deleted is null
          AND u.id = " . $userId . "
          AND o.created BETWEEN '" . $thirtyDaysAgo . " 00:00:00' AND '" . $today . " 23:59:59'
          GROUP BY o.userId, cast(o.created as DATE)
          ORDER BY id ASC, date ASC";

  return $liveDbLink->query($sql);
}

function getNumServices($userId)
{
  global $liveDbLink;

  $sql = "SELECT id
          FROM services
          WHERE userId = '" . $userId . "'
          AND deleted is NULL";

  $result = $liveDbLink->query($sql);
  return $result->num_rows;
}

function getNumTrucks($userId)
{
  global $liveDbLink;

  $sql = "SELECT id
          FROM trucks
          WHERE userId = '" . $userId . "'";

  $result = $liveDbLink->query($sql);
  return $result->num_rows;
}

function getNumBins($userId)
{
  global $liveDbLink;

  $sql = "SELECT id
          FROM bins
          WHERE userId = '" . $userId . "'
          AND deleted IS NULL";

  $result = $liveDbLink->query($sql);
  return $result->num_rows;
}

function getNumStaff($userId)
{
  global $liveDbLink;

  $sql = "SELECT id
          FROM staff
          WHERE userId = '" . $userId . "'
          AND deleted IS NULL";

  $result = $liveDbLink->query($sql);
  return $result->num_rows;
}


$users = getAllUsers();
$response = [];

foreach ($users as $user) {
  $orders      = [];
  $ordersQuery = getAllOrders($user['id']);

  while ($row = $ordersQuery->fetch_assoc()) {
    array_push($orders, $row);
  }

  $numServices = getNumServices($user['id']);
  $numTrucks = getNumTrucks($user['id']);
  $numBins = getNumBins($user['id']);
  $numStaff = getNumStaff($user['id']);

  $json = array(
    'id'                   => $user['id'],
    'domain'               => $user['domain'],
    'website'              => $user['url'],
    'name'                 => $user['firstName'] . ' ' . $user['lastName'],
    'city'                 => $user['city'],
    'province'             => $user['province'],
    'phone'                => $user['phone'],
    'email'                => $user['email'],
    'feePerOrderFE'        => $user['feePerOrderFE'],
    'maxFeePerMonth'       => $user['maxFeePerMonth'],
    'orders'               => $orders,
    'linkPresentOnWebsite' => '?',
    'stripeLinked'         => strlen($user['payMeStripeId']) > 0 ? 1 : 0,
    'numServices'          => $numServices,
    'numTrucks'            => $numTrucks,
    'numBins'              => $numBins,
    'numStaff'             => $numStaff
  );

  array_push($response, $json);
}



header('Content-type: application/json');
header('HTTP/1.1 200 Success', true, 200);
echo json_encode($response);
