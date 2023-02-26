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

  public function validateImage($file)
  {
    $result = true;
    $part = explode(".", $file);
    $extension = end($part);

    switch (strtolower($extension)) {
      case 'jpg':
      case 'gif':
      case 'png':
      case 'jpeg':

      return $result;
    }
    $result = false;

    return $result;
  }

  public function uploadImage($tmp_name)
  {
    if ($tmp_name) {
      $file = $tmp_name;
      $image_name = time().uniqid(). ".jpg";
      $path = "uploads/".$image_name;
  
      if (move_uploaded_file($file, $path)) {
        return $path;
      }
    }
  }

  public function finalUpload($file, $name, $design_id, $top, $left, $width, $border, $border_raduis_top_right, $border_raduis_top_left, $border_raduis_bottom_right, $border_raduis_bottom_left, $height, $border_color, $name_top, $name_left, $font_size, $font_weight, $font_color)
  {
    $sql = "INSERT INTO uploads (file_name, name, design_id, top, left_side, width, border, border_raduis_top_right, border_raduis_top_left, border_raduis_bottom_right, border_raduis_bottom_left, height, border_color, name_top, name_left, font_size, font_weight, font_color, date)" . "VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

    $stmt = $this->db->prepare($sql);

    $stmt->execute([$file, $name, $design_id, $top, $left, $width, $border, $border_raduis_top_right, $border_raduis_top_left, $border_raduis_bottom_right, $border_raduis_bottom_left, $height, $border_color, $name_top, $name_left, $font_size, $font_weight, $font_color, date('Y-m-d H:i:s')]);

    return true;
  }

  public function fetchAllImages()
  {
    $sql = "SELECT * FROM uploads";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();

    $count_row = $stmt->rowCount();

    if ($count_row > 0) {
      return $stmt;
    }
  }

  public function fetchOneImage($id)
  {
    $sql = "SELECT * FROM uploads WHERE design_id = ?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$id]);

    $count_row = $stmt->rowCount();

    if ($count_row > 0) {
      return $stmt;
    }
  }

}