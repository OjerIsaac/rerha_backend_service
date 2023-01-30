<?php

require_once "db.config.php";

//reset the timezone default
date_default_timezone_set('Africa/Lagos');

session_start();

class User
{

  protected $db;

  public function __construct()
  {
    $this->db = new Database();
    $this->db = $this->db->connect();
  }

  public function adminExist($email)
  {
    $query = "SELECT id from users WHERE email = ?";

    $stmt = $this->db->prepare($query);
    $stmt->execute([$email]);
    $count_row = $stmt->rowCount();

    if ($count_row > 0) {
      return true;
    } else {
      return false;
    }
  }

  public function loginUser($username, $password)
  {
    $rows = $this->getUserDetails($username)->fetch(PDO::FETCH_ASSOC);
    // print_r($rows);
    // die();

    if (password_verify($password, $rows['password'])) {
      return true;
    }else {
      return false;
    }
  }

  public function getUserDetails($username)
  {
    $query = "SELECT * from users WHERE email= ?";

    $stmt = $this->db->prepare($query);
    $stmt->execute([$username]);

    $count_row = $stmt->rowCount();

    if ($count_row == 1) {
      return $stmt;
    }
  }

}