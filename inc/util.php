<?php
  /*
  * File: util.php
  * Authors: Joshua Anderson
  * Created: 5/23/13
  * Updated: 5/23
  * Description: Utility functions used throughout the site
  */

  /*
  * Opens the database connection.
  * @return object $conn that has connection data
  */

  function connect()
  {
      $conn = mysqli_connect("localhost", "root", "", "internshipmanager");
      
      if(mysqli_connect_errno())
      {
          exit("Failed to connect" . mysqli_connect_error());
      }
      
      return $conn;
  }


?>