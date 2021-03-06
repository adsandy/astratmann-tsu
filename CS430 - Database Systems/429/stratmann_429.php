Welcome to the Island Jumpers reservtion system!

<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$username = 'UNAME';
$password = 'PASS';

$current_state = 0;


$dbh = new PDO( 'mysql:host=borax.truman.edu;dbname=stratmann;charset=utf8',
                $username, $password,
                array(PDO::ATTR_EMULATE_PREPARES => false,
                      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
                      
$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

do {
  if ($current_state == 0)
  {
    echo "MENU: \r\n";
    echo "Enter 1 to create a reservation.\r\n";
    echo "Enter 2 to cancel a reservation.\r\n";
    echo "Enter 3 to view all pending reservations.\r\n";
    echo "Enter 4 to view the number of reservations by";
    echo " destination and mode of transport.\r\n";
    echo "Enter 5 to quit.\r\n";
    $current_state = trim(fgets(STDIN));
  }
  elseif ($current_state == 1)
  {
    echo "\r\n\r\nLet's make a reservation.\r\n \r\n";
    echo "Here is a list of available origin locations: \r\n \r\n";
    $sth = $dbh->prepare( "CALL get_origins();" );
    $sth->execute();
    $result = $sth->fetchAll(); 
    
    foreach($result as $row)
    {
      echo "{$row['name']}\r\n";
    }
    $sth->closeCursor();
    echo "\r\nPlease enter an origin: ";
    $user_origin = trim(fgets(STDIN));
    echo "\r\nHere is a list of available destinations: \r\n\r\n";
    $sth = $dbh->prepare("CALL get_destinations(:user_origin);");
    $sth->bindParam(':user_origin', $user_origin);
    
    $sth->execute();
    
    $result = $sth->fetchAll();
    foreach($result as $row)
    {
      echo "{$row['name']}\r\n";
    }
    $sth->closeCursor();
    
    echo "\r\nPlease enter a destination: ";
    $user_dest = trim(fgets(STDIN));

    echo "Here are the available trips: \r\n";
    $sth = $dbh->prepare("CALL get_trips(:user_origin, :user_dest);");
    $sth->bindParam(':user_origin', $user_origin);
    $sth->bindParam(':user_dest', $user_dest);
    
    $sth->execute();
    
    $result = $sth->fetchAll();
    $mask = "|%2.2s|%9.9s|%9.9s|%5.5s| \n";
    echo "+--+---------+---------+-----+\r\n";
    printf($mask, 'ID', 'DEPARTURE', 'ARRIVAL', 'COST');
    echo "+--+---------+---------+-----+\r\n";
    foreach($result as $row)
    {
      printf($mask, $row['id'], $row['dept_time'], $row['arrival_time'], $row['cost']);
    }
    echo "+--+---------+---------+-----+\r\n";
    $sth->closeCursor();
    echo "Please enter the ID number of your desired trip: ";
    $user_trip_no = trim(fgets(STDIN));
    echo "Please enter a travel date in the form YYYY-MM-DD: ";
    $user_date = trim(fgets(STDIN));
    
    $sth = $dbh->prepare("CALL get_trip_capacity(:user_trip_no);");
    $sth->bindParam(':user_trip_no', $user_trip_no);
    $sth->execute();
    $result = $sth->fetchAll();
    $trip_cap = $result[0]['capacity'];
    $sth->closeCursor();
    
    $sth = $dbh->prepare("CALL get_current_bookings(:user_trip_no, :user_date);");
    $sth->bindParam(':user_trip_no', $user_trip_no);
    $sth->bindParam(':user_date', $user_date);
    $sth->execute();
    $result = $sth->fetchAll();
    $trip_bookings = $result[0]['count(*)'];
    $sth->closeCursor();
    
    
    $remaining_seats = $trip_cap - $trip_bookings;
    if($remaining_seats > 0)
    {
      echo "There are $remaining_seats remaining seats.\r\n";
      
      echo "Please enter your passenger ID number: ";
      $user_pass_id = trim(fgets(STDIN));
      
      $sth = $dbh->prepare("CALL make_reservation(:user_pass_id, :user_trip_no, :user_date);");
      $sth->bindParam(':user_pass_id', $user_pass_id);
      $sth->bindParam(':user_trip_no', $user_trip_no);
      $sth->bindParam(':user_date', $user_date);
      $sth->execute();
      $result = $sth->fetch();
      $ticket_id = $result[0];
      $sth->closeCursor();
      echo "\r\nYour reservation number is $ticket_id. You will need this number to cancel ";
      echo "your reservation. \r\n";
      echo "\r\n";
      $current_state = 0;
    }
    else
    {
      echo "There are no remaining seats.\r\n";
      echo "\r\n";
      $current_state = 0;
    }
      
  }
  elseif ($current_state == 2)
  {
    echo "\r\n\r\nPlease enter the reservation number for the trip you'd like to cancel: ";
    $user_trip_no = trim(fgets(STDIN));
    $sth = $dbh->prepare("CALL cancel_reservation(:user_trip_no);");
    $sth->bindParam(':user_trip_no', $user_trip_no);
    $sth->execute();
    $sth->closeCursor();
    echo "Your reservation has been canceled.\r\n";
    
    $current_state = 0;
  
  }
  elseif ($current_state == 3)
  {
    echo "\r\n\r\nHere are details on all future reservations: \r\n\r\n";
    
    $sth = $dbh->prepare("CALL get_future_info();");
    $sth->execute();
    $result = $sth->fetchAll();
    
    $mask = "|%3.3s|%20.20s|%11.11s|%11.11s|%10.10s|%9.9s|%9.9s|%11.11s|%5.5s| \n";
    echo "+---+--------------------+-----------+-----------+----------+---------+---------+";
    echo "-----------+-----+\r\n";
    printf($mask, 'ID', 'NAME', 'ORIGIN', 'DESTINATION', 'DATE', 'DEPARTURE', 'ARRIVAL',
    'TYPE', 'COST');
    echo "+---+--------------------+-----------+-----------+----------+---------+---------+";
    echo "-----------+-----+\r\n";
    foreach($result as $row)
    {
      printf($mask, $row['id'], $row['passname'], $row['orgname'], $row['destname'], 
      $row['ticket_date'], $row['dept_time'], $row['arrival_time'], $row['type'], $row['cost']);
    }
    echo "+---+--------------------+-----------+-----------+----------+---------+---------+";
    echo "-----------+-----+\r\n";
    $sth->closeCursor();
    
    $current_state = 0;
  
  }
  elseif ($current_state == 4)
  {
    echo "\r\n\r\nHere are the counts of of reservations made to each island by each ";
    echo "transportation method: \r\n\r\n";
    
    $sth = $dbh->prepare( "CALL show_island_count();" );
    $sth->execute();
    $result = $sth->fetchAll(); 
    
    $mask = "|%11.11s|%5.5s|%11.11s| \n";
    echo "+-----------+-----+-----------+\r\n";
    printf($mask, 'Destination', 'Count', 'Mode');
    echo "+-----------+-----+-----------+\r\n";
    foreach($result as $row)
    {
      printf($mask, $row['name'], $row['count(*)'], $row['type']);
    }
    echo "+-----------+-----+-----------+\r\n";
    $sth->closeCursor();
    
    $current_state = 0;
  }
  else
  {
    $current_state = 5;
  }

} while ($current_state != 5);
?>    
Thank you for using the Island Jumpers reservation system!
